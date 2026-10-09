<?php
declare(strict_types=1);
// Never expose a SQL executor through HTTP.
if(PHP_SAPI!=='cli' && !defined('CRM_MIGRATION_TEST')) { http_response_code(404); exit; }

/** One statement per file; strings may contain semicolons and doubled quotes. */
function crm_migration_sql(string $sql,string $kind): string {
    if($sql==='' || strlen($sql)>1048576) throw new RuntimeException('SQL file is empty or larger than 1 MB.');
    $sql=preg_replace('/\A\xEF\xBB\xBF/','',$sql);
    $masked=''; $ended=false; $len=strlen($sql);
    for($i=0;$i<$len;$i++) {
        $ch=$sql[$i]; $next=$sql[$i+1]??'';
        if(ctype_space($ch)) { $masked.=' '; continue; }
        if($ch==='#' || ($ch==='-' && $next==='-' && (ctype_space($sql[$i+2]??' ') || !isset($sql[$i+2])))) {
            while($i<$len && $sql[$i]!=="\n") $i++; $masked.=' '; continue;
        }
        if($ch==='/' && $next==='*') {
            if(in_array($sql[$i+2]??'',['!','+'],true)) throw new RuntimeException('Executable SQL comments/hints are not supported.');
            $end=strpos($sql,'*/',$i+2); if($end===false) throw new RuntimeException('Unclosed SQL comment.'); $i=$end+1; $masked.=' '; continue;
        }
        if($ended) throw new RuntimeException('Use exactly one SQL statement per file.');
        if(in_array($ch,["'",'"','`'],true)) {
            $quote=$ch; $closed=false;
            for($i++;$i<$len;$i++) {
                if($sql[$i]==='\\') throw new RuntimeException('Use doubled quotes instead of backslash escapes in migration SQL.');
                if($sql[$i]!==$quote) continue;
                if(($sql[$i+1]??'')===$quote) { $i++; continue; }
                $closed=true; break;
            }
            if(!$closed) throw new RuntimeException('Unclosed SQL quote.'); $masked.=' Q '; continue;
        }
        if($ch===';') { $ended=true; continue; }
        $masked.=$ch;
    }
    $masked=trim($masked);
    $pattern=$kind==='ddl'?'/\A(?:CREATE\s+(?:TABLE|(?:UNIQUE\s+)?INDEX)|ALTER\s+TABLE|DROP\s+(?:TABLE|INDEX)|RENAME\s+TABLE)\b/i':'/\A(?:INSERT\s+INTO|UPDATE\s+|DELETE\s+FROM)\b/i';
    if(!in_array($kind,['ddl','dml'],true) || !preg_match($pattern,$masked)) throw new RuntimeException('Statement does not match its .ddl.sql or .dml.sql file type.');
    return $sql;
}

function crm_migration_files(string $directory): array {
    if(!is_dir($directory)) throw new RuntimeException('Migration directory is missing.');
    $files=[]; $versions=[];
    foreach(glob($directory.'/*.sql')?:[] as $path) {
        $name=basename($path);
        if(is_link($path) || !preg_match('/\A([0-9]{4})_[a-z0-9_]+\.(ddl|dml)\.sql\z/',$name,$m)) throw new RuntimeException('Invalid migration filename: '.$name);
        if(isset($versions[$m[1]])) throw new RuntimeException('Duplicate migration number: '.$m[1]); $versions[$m[1]]=true;
        $sql=file_get_contents($path); if($sql===false) throw new RuntimeException('Cannot read '.$name);
        $files[$name]=['name'=>$name,'kind'=>$m[2],'sql'=>crm_migration_sql($sql,$m[2]),'checksum'=>hash('sha256',$sql)];
    }
    ksort($files,SORT_STRING);
    if(!$files) throw new RuntimeException('No migration SQL files found.');
    return $files;
}

function crm_migration_plan(array $files,array $history,?string $retry=null): array {
    $last='';
    foreach($history as $name=>$record) {
        if(!isset($files[$name])) throw new RuntimeException('Recorded SQL file is missing: '.$name);
        if(!hash_equals($record['checksum'],$files[$name]['checksum'])) throw new RuntimeException('Recorded SQL was changed: '.$name.'. Restore it and add a new numbered file.');
        if(!in_array($record['status'],['applied','running','failed'],true)) throw new RuntimeException('Unknown migration status: '.$name);
        if($record['status']==='applied' && strcmp($name,$last)>0) $last=$name;
    }
    if($retry!==null && (!isset($history[$retry]) || !in_array($history[$retry]['status'],['running','failed'],true))) throw new RuntimeException('--retry must name a failed or interrupted migration.');
    $pending=[];
    foreach($files as $name=>$file) {
        if(isset($history[$name])) {
            if($history[$name]['status']==='applied') continue;
            if($retry!==$name) throw new RuntimeException('Migration '.$name.' is '.$history[$name]['status'].'. Inspect the database first; use --retry='.$name.' only after verifying re-execution is safe.');
        } elseif($last!=='' && strcmp($name,$last)<0) throw new RuntimeException('New migration is older than the applied sequence: '.$name);
        $pending[]=$file;
    }
    return $pending;
}

function crm_migration_connect(string $configPath='/etc/global-daisho/crm-db.php'): PDO {
    if(!extension_loaded('pdo_mysql')) throw new RuntimeException('PHP pdo_mysql is required.');
    if(!is_readable($configPath)) throw new RuntimeException('Cannot read /etc/global-daisho/crm-db.php.');
    $config=require $configPath;
    if(!is_array($config) || !is_string($config['dsn']??null) || !str_starts_with($config['dsn'],'mysql:') || !is_string($config['user']??null) || !is_string($config['password']??null)) throw new RuntimeException('Invalid CRM MySQL configuration.');
    $db=new PDO($config['dsn'],$config['user'],$config['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS=>false,
    ]);
    $count=(int)$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('users','contacts','activities','products','tasks')")->fetchColumn();
    if($count!==5) throw new RuntimeException('CRM base tables are missing. Check the selected database and complete the existing CRM initial setup first.');
    return $db;
}

function crm_migration_history(PDO $db): array {
    $exists=$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='crm_schema_migrations'")->fetchColumn();
    if(!(int)$exists) return [];
    $history=[]; foreach($db->query('SELECT name,checksum,status FROM crm_schema_migrations ORDER BY name') as $row) $history[$row['name']]=$row;
    return $history;
}

function crm_migration_apply(PDO $db,array $files,?string $retry,callable $log): void {
    $database=(string)$db->query('SELECT DATABASE()')->fetchColumn();
    if($database==='') throw new RuntimeException('The DSN must select a CRM database.');
    $lock='daisho_crm_schema_'.substr(hash('sha256',$database),0,40);
    $q=$db->prepare('SELECT GET_LOCK(?,5)'); $q->execute([$lock]);
    if((int)$q->fetchColumn()!==1) throw new RuntimeException('Another database migration is running.');
    try {
        // Check every checksum and file before executing any migration.
        $pending=crm_migration_plan($files,crm_migration_history($db),$retry);
        if(!$pending) { $log('UP TO DATE: no pending SQL files.'); return; }
        $db->exec("CREATE TABLE IF NOT EXISTS crm_schema_migrations (name VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,checksum CHAR(64) CHARACTER SET ascii NOT NULL,kind VARCHAR(3) NOT NULL,status VARCHAR(10) NOT NULL,attempts INT UNSIGNED NOT NULL DEFAULT 0,started_at DATETIME NULL,applied_at DATETIME NULL,last_error TEXT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach($pending as $file) {
            $name=$file['name']; $log('APPLY '.$name);
            // Persist the intent before DDL, whose implicit commits cannot be rolled back.
            $q=$db->prepare("INSERT INTO crm_schema_migrations(name,checksum,kind,status,attempts,started_at) VALUES(?,?,?,'running',1,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE status='running',attempts=attempts+1,started_at=UTC_TIMESTAMP(),last_error=NULL");
            $q->execute([$name,$file['checksum'],$file['kind']]);
            try {
                if($file['kind']==='dml') $db->beginTransaction();
                $db->exec($file['sql']);
                $db->prepare("UPDATE crm_schema_migrations SET status='applied',applied_at=UTC_TIMESTAMP(),last_error=NULL WHERE name=?")->execute([$name]);
                if($db->inTransaction()) $db->commit();
            } catch(Throwable $e) {
                if($db->inTransaction()) $db->rollBack();
                // Do not log SQL values or database credentials in console/history.
                $error=$e instanceof PDOException?'SQLSTATE '.($e->errorInfo[0]??$e->getCode()).' / driver '.($e->errorInfo[1]??'unknown'):'Execution failed';
                try { $db->prepare("UPDATE crm_schema_migrations SET status='failed',last_error=? WHERE name=?")->execute([$error,$name]); } catch(Throwable $historyError) { /* A running entry already records the uncertain outcome. */ }
                throw new RuntimeException($name.' failed ('.$error.'). Stop here and inspect the database. Earlier applied files remain applied; DDL is not rolled back.');
            }
            $log('OK '.$name);
        }
        $log('DONE: '.count($pending).' migration(s) applied.');
    } finally {
        try { $q=$db->prepare('SELECT RELEASE_LOCK(?)'); $q->execute([$lock]); } catch(Throwable $e) { /* Connection closure also releases the lock. */ }
    }
}

function crm_migration_main(array $args): int {
    $mode='--status'; $retry=null;
    foreach(array_slice($args,1) as $arg) {
        if(in_array($arg,['--status','--apply'],true)) $mode=$arg;
        elseif(str_starts_with($arg,'--retry=')) { $retry=substr($arg,8); $mode='--apply'; }
        else { fwrite(STDERR,"Usage: php .deploy/crm-migrate.php [--status|--apply|--retry=filename]\n"); return 2; }
    }
    try {
        $files=crm_migration_files(__DIR__.'/crm-sql'); $db=crm_migration_connect();
        if($mode==='--status') {
            $history=crm_migration_history($db);
            foreach($files as $name=>$file) echo strtoupper($history[$name]['status']??'pending').' '.$name."\n";
            crm_migration_plan($files,$history); // A drifted or interrupted installation is not healthy.
        } else crm_migration_apply($db,$files,$retry,fn(string $message)=>print($message."\n"));
        return 0;
    } catch(PDOException $e) {
        fwrite(STDERR,"Database connection/history check failed. Verify the external config and MySQL permissions.\n"); return 1;
    } catch(Throwable $e) { fwrite(STDERR,'ERROR: '.$e->getMessage()."\n"); return 1; }
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__) exit(crm_migration_main($argv));
