<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }

function parse_chatgpt_records(string $input): array {
    if(strlen($input)>100000) throw new InvalidArgumentException('Maximum input size: 100 KB.');
    $input=trim($input);
    $input=preg_replace('/\A```(?:json)?\s*|\s*```\z/i','',$input);
    $data=json_decode($input,true,32,JSON_THROW_ON_ERROR);
    if(!is_array($data) || !isset($data['records']) || !is_array($data['records']) || !array_is_list($data['records']) || count($data['records'])<1 || count($data['records'])>20) throw new InvalidArgumentException('Use {"records":[...]} with 1–20 records.');
    $records=[];
    foreach($data['records'] as $i=>$row) {
        if(!is_array($row)) throw new InvalidArgumentException('Invalid record.');
        $r=[];
        foreach(['date'=>10,'company'=>191,'subject'=>255,'summary'=>12000,'type'=>10,'language'=>2,'next_action'=>2000,'due_date'=>10] as $field=>$max) {
            $v=$row[$field]??'';
            if(!is_string($v)) throw new InvalidArgumentException('Text required: '.$field);
            $v=trim($v);
            if(preg_match('//u',$v)!==1 || preg_match_all('/./us',$v)>$max) throw new InvalidArgumentException('Invalid or too long: '.$field);
            $r[$field]=$v;
        }
        foreach(['date','company','subject','summary'] as $f) if($r[$f]==='') throw new InvalidArgumentException('Required field: '.$f.' (record '.($i+1).')');
        foreach(['date','due_date'] as $f) if($r[$f]!=='') {
            $d=DateTimeImmutable::createFromFormat('!Y-m-d',$r[$f]);
            if(!$d || $d->format('Y-m-d')!==$r[$f]) throw new InvalidArgumentException('Use a valid YYYY-MM-DD date: '.$f);
        }
        if(!in_array($r['language'],['ja','en','pl'],true)) throw new InvalidArgumentException('language must be ja, en or pl.');
        if($r['due_date']!=='' && $r['next_action']==='') throw new InvalidArgumentException('A due date requires a next action.');
        $codes=$row['product_codes']??[];
        if(!is_array($codes) || !array_is_list($codes) || count($codes)>30) throw new InvalidArgumentException('product_codes must be a list.');
        foreach($codes as $code) if(!is_string($code) || strlen($code)>191) throw new InvalidArgumentException('Invalid product code.');
        if($r['type']==='') $r['type']='note';
        if(!in_array($r['type'],['meeting','email','phone','sample','test','note'],true)) throw new InvalidArgumentException('Invalid activity type.');
        $r['product_codes']=array_values(array_unique($codes)); sort($r['product_codes']);
        $records[]=$r;
    }
    return $records;
}

function handle_chatgpt_import_legacy(string $action): void {
    $u=require_login();
    if($u['must_change_password']) redirect('?page=change-password');
    try {
        if($action==='chatgpt_preview') {
            unset($_SESSION['chatgpt_draft']);
            $payload=is_string($_POST['payload']??null)?$_POST['payload']:'';
            $_SESSION['chatgpt_payload']=strlen($payload)<=100000?$payload:'';
            $records=parse_chatgpt_records($payload);
            $find=db()->prepare('SELECT id FROM products WHERE code=?');
            foreach($records as $r) foreach($r['product_codes'] as $code) { $find->execute([$code]); if(!$find->fetchColumn()) throw new InvalidArgumentException('Unknown product code: '.$code); }
            $_SESSION['chatgpt_draft']=['uid'=>(int)$u['id'],'created'=>time(),'token'=>bin2hex(random_bytes(24)),'records'=>$records];
        } elseif($action==='chatgpt_cancel') {
            unset($_SESSION['chatgpt_draft']);
        } elseif($action==='chatgpt_commit') {
            $draft=$_SESSION['chatgpt_draft']??null;
            $token=$_POST['draft_token']??null;
            if(!$draft || !is_string($token) || !hash_equals($draft['token'],$token) || $draft['uid']!==(int)$u['id'] || time()-$draft['created']>1800) throw new InvalidArgumentException('Preview expired. Please preview again.');
            $db=db();
            // Unique per-user record hash makes retries and double clicks safe.
            $db->exec("CREATE TABLE IF NOT EXISTS crm_chatgpt_imports (user_id BIGINT UNSIGNED NOT NULL, record_hash CHAR(64) NOT NULL, activity_id BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(user_id,record_hash)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $saved=0; $skipped=0;
            $db->beginTransaction();
            try {
                $claim=$db->prepare('INSERT IGNORE INTO crm_chatgpt_imports(user_id,record_hash) VALUES(?,?)');
                foreach($draft['records'] as $r) {
                    $hash=hash('sha256',json_encode($r,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
                    $claim->execute([$u['id'],$hash]); if(!$claim->rowCount()) { $skipped++; continue; }
                    $db->prepare('INSERT IGNORE INTO companies(name) VALUES(?)')->execute([$r['company']]);
                    $find=$db->prepare('SELECT id FROM companies WHERE name=?'); $find->execute([$r['company']]); $cid=$find->fetchColumn();
                    $text=['ja'=>'','en'=>'','pl'=>'']; $text[$r['language']]=$r['summary'];
                    // Japanese fallback remains readable when only EN/PL text was supplied.
                    if($text['ja']==='') $text['ja']=$r['summary'];
                    $db->prepare("INSERT INTO activities(activity_date,company_id,activity_type,subject,summary_ja,summary_en,summary_pl,next_action,next_action_en,next_action_pl,next_action_date,status,created_by,owner_name) VALUES(?,?,?,?,?,?,?,?,?,?,?, 'open',?,?)")
                        ->execute([$r['date'],$cid,$r['type'],$r['subject'],$text['ja'],$text['en'],$text['pl'],$r['next_action'],$r['language']==='en'?$r['next_action']:'',$r['language']==='pl'?$r['next_action']:'',$r['due_date']?:null,$u['id'],$u['display_name']]);
                    $aid=(int)$db->lastInsertId();
                    $link=$db->prepare('INSERT INTO activity_products(activity_id,product_id) SELECT ?,id FROM products WHERE code=?');
                    foreach($r['product_codes'] as $code) { $link->execute([$aid,$code]); if($link->rowCount()!==1) throw new RuntimeException('Product changed during import.'); }
                    if($r['next_action']!=='') {
                        $title=preg_replace('/\s+/u',' ',$r['next_action']); preg_match('/\A.{0,240}/us',$title,$m); $title=$m[0];
                        $db->prepare("INSERT INTO tasks(company_id,title,title_en,title_pl,detail,status,due_date,assigned_to,owner_name) VALUES(?,?,?,?,?,'open',?,?,?)")
                            ->execute([$cid,$title,$r['language']==='en'?$title:'',$r['language']==='pl'?$title:'',$r['next_action'].' / '.implode(', ',$r['product_codes']),$r['due_date']?:null,$u['id'],$u['display_name']]);
                    }
                    $db->prepare('UPDATE crm_chatgpt_imports SET activity_id=? WHERE user_id=? AND record_hash=?')->execute([$aid,$u['id'],$hash]);
                    $saved++;
                }
                $db->commit();
            } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
            unset($_SESSION['chatgpt_draft'],$_SESSION['chatgpt_payload']);
            $_SESSION['chatgpt_import_result']=[$saved,$skipped];
        }
    } catch(JsonException|InvalidArgumentException $e) { $_SESSION['chatgpt_import_error']=$e->getMessage(); }
    catch(Throwable $e) { error_log('CRM ChatGPT import failed: '.$e->getMessage()); $_SESSION['chatgpt_import_error']='Registration failed. No records from this batch were saved. Please retry.'; }
    redirect('?page=chatgpt-import');
}

function render_chatgpt_import_legacy(array $u): void {
    $products=db()->query('SELECT code,name FROM products ORDER BY name')->fetchAll();
    $example=['records'=>[['date'=>'YYYY-MM-DD','company'=>'','subject'=>'','summary'=>'','type'=>'note','language'=>current_lang(),'product_codes'=>[],'next_action'=>'','due_date'=>'']]];
    $prompt=ux('会話の営業内容を次のJSON形式だけで出力してください。日付・会社・件名・内容は必須。不明な必須項目は私に質問してください。期限や製品は推測しないでください。担当者名はsummaryに含めてください。next_actionは合意済みの未完了事項だけにし、なければ空文字。1〜20件。','Convert our sales discussion into only the following JSON format. Date, company, subject and summary are required; ask me if missing. Do not guess dates or products. Put the contact name in summary. next_action must contain only agreed outstanding actions; otherwise use an empty string. 1–20 records.','Zwróć treść rozmowy sprzedażowej wyłącznie w poniższym formacie JSON. Data, firma, temat i opis są wymagane; zapytaj o brakujące dane. Nie zgaduj terminów ani produktów. Nazwisko kontaktu umieść w summary. next_action zawiera tylko uzgodnione niezakończone działania; w innym przypadku pusty tekst. 1–20 wpisów.');
    $prompt.="\n".json_encode($example,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\nproduct_codes: ".implode(', ',array_column($products,'code'));
    $prompt=gpt_import_instructions();
    ?><section class="card"><p><?=h(ux('スキャン画像・PDFをChatGPTに渡し、下の依頼文で登録・修正データを作成します。出力を貼り付け、変更前後を確認して反映してください。会話の自動取得ではありません。','Give ChatGPT your scan or PDF and the instructions below. Paste its output, review changes and apply. Conversations are not fetched automatically.','Przekaż ChatGPT skan lub PDF oraz instrukcję poniżej. Wklej wynik, sprawdź zmiany i zapisz. Rozmowy nie są pobierane automatycznie.'))?></p>
<?php if(isset($_SESSION['chatgpt_import_result'])): [$saved,$skipped]=$_SESSION['chatgpt_import_result']; unset($_SESSION['chatgpt_import_result']); ?><p class="ok"><?=h(ux('登録済み','Saved','Zapisano'))?>: <?=$saved?> / <?=h(ux('登録済みのためスキップ','Already saved; skipped','Już zapisane; pominięto'))?>: <?=$skipped?></p><a href="?page=activities"><?=h(tr('history'))?> ↗</a> · <a href="?page=companies"><?=h(tr('companies'))?> ↗</a><?php endif; ?>
<?php if(isset($_SESSION['chatgpt_import_error'])): ?><p class="error"><?=h($_SESSION['chatgpt_import_error'])?></p><?php unset($_SESSION['chatgpt_import_error']); endif; ?>
<details><summary><?=h(ux('ChatGPTに渡す依頼文','Instructions to copy into ChatGPT','Instrukcja do skopiowania do ChatGPT'))?></summary><textarea id="gpt-general-context" rows="13" readonly onclick="this.select()"><?=h($prompt)?></textarea><button type="button" onclick="const t=document.getElementById('gpt-general-context');t.select();t.setSelectionRange(0,t.value.length);try{document.execCommand('copy')}catch(e){} "><?=h(ux('依頼文をコピー','Copy instructions','Kopiuj instrukcję'))?></button></details>
<form method="post" action="?page=chatgpt-import" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_preview"><label>ChatGPT JSON<textarea name="payload" rows="10" required spellcheck="false" placeholder='{"operations":[...]}'><?=h($_SESSION['chatgpt_payload']??'')?></textarea></label><button><?=h(ux('取り込み内容を確認','Preview import','Sprawdź dane'))?></button></form></section>
<?php $draft=$_SESSION['chatgpt_draft']??null; if($draft && $draft['uid']===(int)$u['id'] && time()-$draft['created']<=1800): ?>
<section class="card"><h2><?=h(ux('登録前の確認','Review before saving','Sprawdź przed zapisem'))?></h2><p><?=h(tr('owner'))?>: <?=h($u['display_name'])?></p>
<?php $find=db()->prepare('SELECT id FROM companies WHERE name=?'); foreach($draft['records'] as $r): $find->execute([$r['company']]); $exists=$find->fetchColumn(); ?>
<article class="activity"><div><?=h($r['date'])?></div><div><h3><?=h($r['company'])?> / <?=h($r['subject'])?></h3><?php if(!$exists): ?><small><?=h(ux('会社を新規作成します','A new company will be created','Zostanie utworzona nowa firma'))?></small><?php endif; ?><p><?=nl2br(h($r['summary']))?></p><p><?=h(tr('product'))?>: <?=h(implode(', ',$r['product_codes']))?></p><p><?=h(tr('next_action'))?>: <?=h($r['next_action'])?> / <?=h($r['due_date'])?></p></div></article>
<?php endforeach; ?><p><?=h(ux('次のアクションがある記録は、ダッシュボードにも未完了タスクを作成します。','Records with a next action also create an open dashboard task.','Wpisy z następnym działaniem tworzą też otwarte zadanie w panelu.'))?></p>
<form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_commit"><input type="hidden" name="draft_token" value="<?=h($draft['token'])?>"><button><?=h(ux('この内容で登録する','Save these records','Zapisz te wpisy'))?></button></form>
<form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_cancel"><button class="linkbtn"><?=h(ux('取り消す','Cancel','Anuluj'))?></button></form></section>
<?php endif; }


// Version 2: explicit, previewed contact/activity/task mutations.
function gpt_fields(string $entity): array {
    $common=['company'=>191];
    if($entity==='contact') return $common+['name'=>255,'title'=>255,'email'=>255,'phone'=>100,'mobile'=>100,'fax'=>100,'address'=>2000,'notes'=>12000,'card_source'=>2000,'card_received_on'=>10,'card_scanned_on'=>10];
    if($entity==='activity') return $common+['activity_date'=>10,'activity_type'=>30,'subject'=>255,'summary_ja'=>12000,'summary_en'=>12000,'summary_pl'=>12000,'next_action'=>2000,'next_action_en'=>2000,'next_action_pl'=>2000,'next_action_date'=>10,'status'=>30,'product_codes'=>0];
    if($entity==='task') return $common+['title'=>255,'title_en'=>255,'title_pl'=>255,'detail'=>12000,'detail_en'=>12000,'detail_pl'=>12000,'due_date'=>10,'status'=>30];
    throw new InvalidArgumentException('Unknown entity.');
}
function gpt_table(string $entity): string { return ['contact'=>'contacts','activity'=>'activities','task'=>'tasks'][$entity]; }
function gpt_error(string $ja,string $en,string $pl): void { throw new InvalidArgumentException(ux($ja,$en,$pl)); }
function gpt_json(array $value): string { return json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR); }
function gpt_parse_operations(string $payload): array {
    if(strlen($payload)>100000) throw new InvalidArgumentException('Maximum input size: 100 KB.');
    $raw=json_decode(preg_replace('/\A```(?:json)?\s*|\s*```\z/i','',trim($payload)),true,32,JSON_THROW_ON_ERROR);
    if(!is_array($raw) || array_keys($raw)!==['operations'] || !is_array($raw['operations']) || !array_is_list($raw['operations']) || count($raw['operations'])<1 || count($raw['operations'])>20) throw new InvalidArgumentException('Use {"operations":[...]} with 1–20 operations.');
    $ops=[];
    foreach($raw['operations'] as $o) {
        if(!is_array($o) || array_diff(array_keys($o),['entity','action','match','set','clear','expected'])) throw new InvalidArgumentException('Unknown operation field.');
        $entity=$o['entity']??''; if(!is_string($entity)) throw new InvalidArgumentException('Invalid entity.');
        $fields=gpt_fields($entity); $action=$o['action']??'';
        if(!in_array($action,['create','update','upsert'],true) || ($action==='upsert' && $entity!=='contact') || ($entity==='task' && $action!=='update')) throw new InvalidArgumentException('Use contact:create/update/upsert, activity:create/update, task:update.');
        $set=$o['set']??[]; $clear=$o['clear']??[]; $match=$o['match']??[];
        if(!is_array($set) || !is_array($match) || !is_array($clear) || !array_is_list($clear)) throw new InvalidArgumentException('Invalid set/match/clear.');
        $clean=[];
        foreach($set as $key=>$value) {
            if(!array_key_exists($key,$fields)) throw new InvalidArgumentException('Unknown field: '.$key);
            if($key==='product_codes') {
                if(!is_array($value) || !array_is_list($value) || count($value)>30) throw new InvalidArgumentException('Invalid product_codes.');
                foreach($value as $v) if(!is_string($v) || trim($v)==='' || strlen($v)>191) throw new InvalidArgumentException('Invalid product code.');
                $value=array_values(array_unique($value)); sort($value); $clean[$key]=$value; continue;
            }
            // Unknown OCR values and blanks never erase existing data.
            if($value===null || $value==='') continue;
            if(!is_string($value) || preg_match('//u',$value)!==1 || preg_match_all('/./us',$value)>$fields[$key]) throw new InvalidArgumentException('Invalid field: '.$key);
            $value=trim($value); if($value==='') continue;
            if(in_array($key,['activity_date','next_action_date','due_date','card_received_on','card_scanned_on'],true)) {
                $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
                if(!$date || $date->format('Y-m-d')!==$value) throw new InvalidArgumentException('Invalid date: '.$key);
            }
            if($key==='email' && !filter_var($value,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Invalid email.');
            if($key==='activity_type' && !in_array($value,['meeting','email','phone','sample','test','note'],true)) throw new InvalidArgumentException('Invalid activity_type.');
            if($key==='status' && !in_array($value,$entity==='task'?['open','waiting','conditional','done']:['open','done'],true)) throw new InvalidArgumentException('Invalid status.');
            $clean[$key]=$value;
        }
        foreach($clear as $key) {
            if(!is_string($key) || !isset($fields[$key]) || in_array($key,array_merge(['company','name','subject','activity_date','activity_type','status'],$entity==='task'?['title']:[]),true) || array_key_exists($key,$clean)) throw new InvalidArgumentException('Invalid clear field.');
            $clean[$key]=$key==='product_codes'?[]:null;
        }
        $allowed=$entity==='contact'?['id','email','company','name']:($entity==='activity'?['id','company','activity_date','subject']:['id','company','title']);
        foreach($match as $key=>$value) {
            if(!in_array($key,$allowed,true)) throw new InvalidArgumentException('Unknown match field.');
            if($key==='id') { if(!is_int($value) || $value<1) throw new InvalidArgumentException('match.id must be a positive integer.'); }
            elseif(!is_string($value) || trim($value)==='' || strlen($value)>1000) throw new InvalidArgumentException('Invalid match value.');
        }
        $expected=$o['expected']??null;
        if($expected!==null && (!is_string($expected) || !preg_match('/\A[a-f0-9]{64}\z/',$expected))) throw new InvalidArgumentException('Invalid expected version.');
        if(!$clean) throw new InvalidArgumentException('No fields to apply.');
        ksort($clean); ksort($match);
        $ops[]=['entity'=>$entity,'action'=>$action,'match'=>$match,'set'=>$clean,'expected'=>$expected];
    }
    return $ops;
}
function gpt_snapshot(string $entity,int $id,bool $lock=false): ?array {
    $table=gpt_table($entity); $q=db()->prepare("SELECT * FROM `$table` WHERE id=?".($lock?' FOR UPDATE':'')); $q->execute([$id]); $row=$q->fetch();
    if(!$row) return null;
    $q=db()->prepare('SELECT name FROM companies WHERE id=?'); $q->execute([$row['company_id']]); $row['company']=$q->fetchColumn()?:'';
    if($entity==='activity') {
        $q=db()->prepare('SELECT p.code FROM activity_products ap JOIN products p ON p.id=ap.product_id WHERE ap.activity_id=? ORDER BY p.code'); $q->execute([$id]); $row['product_codes']=$q->fetchAll(PDO::FETCH_COLUMN);
    }
    return $row;
}
function gpt_fingerprint(?array $row): string { if($row!==null) ksort($row); return hash('sha256',json_encode($row,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)); }
function gpt_authorize(string $entity,array $row,array $user): void {
    $owner=$entity==='activity'?'created_by':'assigned_to';
    if($entity!=='contact' && $user['role']!=='admin' && (int)($row[$owner]??0)!==(int)$user['id']) gpt_error('この記録の修正は登録者または管理者が行ってください。','Only the owner or an administrator can update this record.','Ten wpis może zmienić właściciel lub administrator.');
}
function gpt_resolve(array $o): array {
    $entity=$o['entity']; $match=$o['match']; $set=$o['set']; $table=gpt_table($entity);
    if(!$match && $entity==='contact') {
        if(!empty($set['email'])) $match=['email'=>$set['email']];
        elseif(!empty($set['company']) && !empty($set['name'])) $match=['company'=>$set['company'],'name'=>$set['name']];
    }
    if(!$match && $entity==='activity' && $o['action']==='create') $match=array_intersect_key($set,array_flip(['company','activity_date','subject']));
    $hasIdentity=isset($match['id']) || ($entity==='contact' && (isset($match['email']) || isset($match['company'],$match['name']))) || ($entity==='activity' && isset($match['company'],$match['activity_date'],$match['subject'])) || ($entity==='task' && isset($match['company'],$match['title']));
    if(!$hasIdentity) gpt_error('対象のID、または照合に必要な会社名・氏名などを指定してください。','Provide a record ID or complete matching fields.','Podaj ID lub pełne dane identyfikujące wpis.');
    $where=[]; $args=[];
    foreach($match as $key=>$value) { $where[]=($key==='company'?'c.name':'r.`'.$key.'`').'=?'; $args[]=$value; }
    $q=db()->prepare("SELECT r.id FROM `$table` r LEFT JOIN companies c ON c.id=r.company_id WHERE ".implode(' AND ',$where).' ORDER BY r.id LIMIT 21'); $q->execute($args); $ids=$q->fetchAll(PDO::FETCH_COLUMN);
    // An email change must not silently create a second card for the same company/name.
    if(!$ids && $entity==='contact' && !isset($match['id']) && isset($match['email']) && !empty($set['company']) && !empty($set['name'])) {
        $q=db()->prepare('SELECT ct.id FROM contacts ct JOIN companies c ON c.id=ct.company_id WHERE c.name=? AND ct.name=? ORDER BY ct.id LIMIT 21'); $q->execute([$set['company'],$set['name']]); $ids=$q->fetchAll(PDO::FETCH_COLUMN);
    }
    if(count($ids)>1) gpt_error('候補が複数あります。対象の「ChatGPTで修正」からID付き依頼文を使ってください。候補ID: '.implode(', ',$ids),'Multiple matches. Use “Edit with ChatGPT” on the target record. IDs: '.implode(', ',$ids),'Kilka wyników. Użyj „Edytuj z ChatGPT” przy właściwym wpisie. ID: '.implode(', ',$ids));
    if(!$ids && ($o['action']==='update' || isset($match['id']))) gpt_error('更新対象が見つかりません。新規登録には切り替えません。','Update target not found; no new record will be created.','Nie znaleziono wpisu do aktualizacji.');
    if($ids && $o['action']==='create') gpt_error('既存の記録があります。修正する場合はupdateを指定してください。','A matching record exists. Use update to modify it.','Wpis już istnieje. Użyj update.');
    return $ids?gpt_snapshot($entity,(int)$ids[0]):[];
}
function gpt_prepare(array $ops,array $user): array {
    $prepared=[]; $targets=[];
    foreach($ops as $o) {
        $before=gpt_resolve($o); $entity=$o['entity'];
        if($before) gpt_authorize($entity,$before,$user);
        if($o['expected']!==null && $o['expected']!==gpt_fingerprint($before?:null)) gpt_error('コピー後にデータが変わっています。対象を開き直して依頼文をコピーしてください。','The record changed. Copy a fresh editing prompt.','Wpis się zmienił. Skopiuj aktualną instrukcję.');
        $set=$o['set'];
        if($entity==='contact' && !$before) {
            foreach(['company','name'] as $f) if(empty($set[$f])) throw new InvalidArgumentException('Required for a new card: '.$f);
        }
        if($entity==='activity' && !$before) {
            foreach(['company','activity_date','subject'] as $f) if(empty($set[$f])) throw new InvalidArgumentException('Required for a new activity: '.$f);
            if(empty($set['summary_ja']) && empty($set['summary_en']) && empty($set['summary_pl'])) throw new InvalidArgumentException('A summary is required.');
            $set['activity_type']=$set['activity_type']??'note';
        }
        if(isset($set['product_codes'])) {
            $q=db()->prepare('SELECT id FROM products WHERE code=?'); foreach($set['product_codes'] as $code) { $q->execute([$code]); if(!$q->fetchColumn()) throw new InvalidArgumentException('Unknown product code: '.$code); }
        }
        // A correction in one language must not leave a contradictory old translation visible.
        $groups=$entity==='activity'?[['summary_ja','summary_en','summary_pl'],['next_action','next_action_en','next_action_pl']]:($entity==='task'?[['title','title_en','title_pl'],['detail','detail_en','detail_pl']]:[]);
        foreach($groups as $group) {
            if(!array_filter(array_intersect_key($set,array_flip($group)),fn($value)=>$value!==null && $value!=='')) continue;
            foreach($group as $field) if(!array_key_exists($field,$set)) $set[$field]=null;
            if(empty($set[$group[0]])) foreach($group as $field) if(!empty($set[$field])) { $set[$group[0]]=$set[$field]; break; }
        }
        if($entity!=='contact' && isset($set['company']) && $before && $set['company']!==$before['company'] && !empty($before['contact_id'])) $set['contact_id']=null;
        $after=array_replace($before,$set);
        if($entity==='activity' && !empty($after['next_action_date']) && empty($after['next_action'])) throw new InvalidArgumentException('A due date requires a next action. Clear the due date if removing the action.');
        $key=$entity.':'.($before['id']??hash('sha256',gpt_json($o)));
        if(isset($targets[$key])) throw new InvalidArgumentException('One operation per record per batch.'); $targets[$key]=true;
        $prepared[]=['operation'=>$o,'entity'=>$entity,'id'=>(int)($before['id']??0),'before'=>$before,'set'=>$set,'version'=>gpt_fingerprint($before?:null)];
    }
    return $prepared;
}
function gpt_schema(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS crm_gpt_changes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,request_hash CHAR(64) NOT NULL,entity VARCHAR(20) NOT NULL,record_id BIGINT UNSIGNED NULL,before_json MEDIUMTEXT,after_json MEDIUMTEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY dedup(user_id,request_hash)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS crm_gpt_activity_tasks (activity_id BIGINT UNSIGNED PRIMARY KEY,task_id BIGINT UNSIGNED NOT NULL UNIQUE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function gpt_write(array $p,array $user): int {
    $db=db(); $entity=$p['entity']; $table=gpt_table($entity); $set=$p['set']; $id=$p['id'];
    $codes=$set['product_codes']??null; unset($set['product_codes']);
    if(isset($set['company'])) {
        $db->prepare('INSERT IGNORE INTO companies(name) VALUES(?)')->execute([$set['company']]);
        $q=$db->prepare('SELECT id FROM companies WHERE name=?'); $q->execute([$set['company']]); $set['company_id']=(int)$q->fetchColumn(); unset($set['company']);
    }
    if($id) {
        $cols=array_map(fn($key)=>'`'.$key.'`=?',array_keys($set));
        if($cols) $db->prepare("UPDATE `$table` SET ".implode(',',$cols).' WHERE id=?')->execute([...array_values($set),$id]);
    } else {
        if($entity==='activity') { $set['created_by']=$user['id']; $set['owner_name']=$user['display_name']; }
        $db->prepare("INSERT INTO `$table` (`".implode('`,`',array_keys($set)).'`) VALUES('.implode(',',array_fill(0,count($set),'?')).')')->execute(array_values($set)); $id=(int)$db->lastInsertId();
    }
    if($entity==='activity' && $codes!==null) {
        $db->prepare('DELETE FROM activity_products WHERE activity_id=?')->execute([$id]);
        $q=$db->prepare('INSERT INTO activity_products(activity_id,product_id) SELECT ?,id FROM products WHERE code=?');
        foreach($codes as $code) { $q->execute([$id,$code]); if($q->rowCount()!==1) throw new RuntimeException('Product changed; preview again.'); }
    }
    if($entity==='activity') {
        $a=gpt_snapshot('activity',$id); $q=$db->prepare('SELECT task_id FROM crm_gpt_activity_tasks WHERE activity_id=?'); $q->execute([$id]); $tid=$q->fetchColumn();
        $actionTouched=!$p['id'] || array_intersect(array_keys($p['set']),['next_action','next_action_en','next_action_pl','next_action_date','company']);
        if($actionTouched && $tid) {
            $q=$db->prepare('SELECT * FROM tasks WHERE id=? FOR UPDATE'); $q->execute([$tid]); $task=$q->fetch();
            if(!$task || gpt_fingerprint($task)!==($p['task_version']??'')) throw new RuntimeException('Related task changed. Preview again.');
            // Preserve completed/waiting task state unless the task is explicitly updated separately.
            $db->prepare('UPDATE tasks SET company_id=?,title=?,title_en=?,title_pl=?,detail=?,detail_en=?,detail_pl=?,due_date=? WHERE id=?')->execute([$a['company_id'],gpt_title($a['next_action']?:$task['title']),gpt_title($a['next_action_en']??''),gpt_title($a['next_action_pl']??''),$a['next_action'],$a['next_action_en'],$a['next_action_pl'],$a['next_action_date'],$tid]);
        } elseif(!$p['id'] && !empty($a['next_action'])) {
            $db->prepare("INSERT INTO tasks(company_id,title,title_en,title_pl,detail,detail_en,detail_pl,due_date,status,assigned_to,owner_name) VALUES(?,?,?,?,?,?,?,?,'open',?,?)")->execute([$a['company_id'],gpt_title($a['next_action']),gpt_title($a['next_action_en']??''),gpt_title($a['next_action_pl']??''),$a['next_action'],$a['next_action_en'],$a['next_action_pl'],$a['next_action_date'],$user['id'],$user['display_name']]);
            $db->prepare('INSERT INTO crm_gpt_activity_tasks(activity_id,task_id) VALUES(?,?)')->execute([$id,(int)$db->lastInsertId()]);
        }
    }
    return $id;
}
function gpt_title(?string $text): string { preg_match('/\A.{0,240}/us',(string)$text,$m); return $m[0]; }
function handle_chatgpt_import(string $action): void {
    $user=require_login(); if($user['must_change_password']) redirect('?page=change-password');
    if($action==='chatgpt_cancel') { unset($_SESSION['gpt_v2_draft']); handle_chatgpt_import_legacy($action); return; }
    try {
        if($action==='chatgpt_preview') {
            unset($_SESSION['gpt_v2_draft'],$_SESSION['chatgpt_draft']);
            $payload=is_string($_POST['payload']??null)?$_POST['payload']:'';
            $_SESSION['chatgpt_payload']=strlen($payload)<=100000?$payload:'';
            if(strlen($payload)>100000) throw new InvalidArgumentException('Maximum input size: 100 KB.');
            $raw=json_decode(preg_replace('/\A```(?:json)?\s*|\s*```\z/i','',trim($payload)),true,32,JSON_THROW_ON_ERROR);
            if(is_array($raw) && isset($raw['records']) && !isset($raw['operations'])) { handle_chatgpt_import_legacy($action); return; }
            gpt_schema(db()); $prepared=gpt_prepare(gpt_parse_operations($payload),$user);
            foreach($prepared as &$p) if($p['entity']==='activity' && $p['id']) {
                $q=db()->prepare('SELECT t.* FROM tasks t JOIN crm_gpt_activity_tasks l ON l.task_id=t.id WHERE l.activity_id=?'); $q->execute([$p['id']]); $task=$q->fetch();
                if($task) { gpt_authorize('task',$task,$user); foreach($prepared as $other) if($other['entity']==='task' && $other['id']===(int)$task['id']) throw new InvalidArgumentException('Update the linked activity and its task in separate batches.'); }
                $p['task_version']=$task?gpt_fingerprint($task):null;
                $p['linked_task']=$task?:null;
                if(!array_intersect(array_keys($p['set']),['next_action','next_action_en','next_action_pl','next_action_date','company'])) $p['linked_task']=null;
                if($task && array_key_exists('next_action',$p['set']) && empty($p['set']['next_action'])) gpt_error('関連タスクがあります。次のアクションを消す場合は、先にタスクの扱いを確認してください。','A linked task exists. Resolve its status before removing the next action.','Istnieje powiązane zadanie. Najpierw ustal jego status.');
            } unset($p);
            $_SESSION['gpt_v2_draft']=['uid'=>(int)$user['id'],'created'=>time(),'token'=>bin2hex(random_bytes(24)),'items'=>$prepared];
        } elseif($action==='chatgpt_commit') {
            $draft=$_SESSION['gpt_v2_draft']??null;
            if(!$draft) { handle_chatgpt_import_legacy($action); return; }
            $token=$_POST['draft_token']??null;
            if(!is_string($token) || !hash_equals($draft['token'],$token) || $draft['uid']!==(int)$user['id'] || time()-$draft['created']>1800) throw new InvalidArgumentException('Preview expired. Preview again.');
            $db=db(); gpt_schema($db); $saved=0; $skipped=0;
            if((int)$db->query("SELECT GET_LOCK('daisho_crm_gpt_import_v2',5)")->fetchColumn()!==1) throw new RuntimeException('Another import is running. Retry.');
            try {
            $db->beginTransaction();
            try {
                foreach($draft['items'] as $p) {
                    $hash=hash('sha256',gpt_json([$p['operation'],$p['version']]));
                    $claim=$db->prepare('INSERT IGNORE INTO crm_gpt_changes(user_id,request_hash,entity,before_json) VALUES(?,?,?,?)'); $claim->execute([$user['id'],$hash,$p['entity'],gpt_json(['record'=>$p['before'],'related_task'=>$p['linked_task']??null])]);
                    if(!$claim->rowCount()) { $skipped++; continue; }
                    $auditId=(int)$db->lastInsertId();
                    $current=$p['id']?gpt_snapshot($p['entity'],$p['id'],true):null;
                    if($p['id']) {
                        if(!$current || gpt_fingerprint($current)!==$p['version']) gpt_error('確認後にデータが変わりました。もう一度確認してください。','Data changed after preview. Preview again.','Dane zmieniły się po podglądzie. Sprawdź ponownie.');
                        gpt_authorize($p['entity'],$current,$user);
                    } elseif(gpt_resolve($p['operation'])) throw new RuntimeException('A matching record was added. Preview again.');
                    $id=gpt_write($p,$user);
                    $db->prepare('UPDATE crm_gpt_changes SET record_id=?,after_json=? WHERE id=?')->execute([$id,gpt_json(['record'=>gpt_snapshot($p['entity'],$id),'related_task'=>!empty($p['linked_task'])?gpt_snapshot('task',(int)$p['linked_task']['id']):null]),$auditId]); $saved++;
                }
                $db->commit();
            } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
            } finally { try { $db->query("SELECT RELEASE_LOCK('daisho_crm_gpt_import_v2')"); } catch(Throwable $lockError) { error_log('CRM import lock release: '.$lockError->getMessage()); } }
            unset($_SESSION['gpt_v2_draft'],$_SESSION['chatgpt_payload']); $_SESSION['chatgpt_import_result']=[$saved,$skipped];
        }
    } catch(JsonException|InvalidArgumentException $e) { $_SESSION['chatgpt_import_error']=$e->getMessage(); }
    catch(Throwable $e) { error_log('CRM import v2: '.$e->getMessage()); $_SESSION['chatgpt_import_error']=ux('反映できませんでした。この一括処理の変更は保存していません。再度内容を確認してください。','Import failed. No changes from this batch were saved. Preview again.','Import nie powiódł się. Nie zapisano zmian z tej partii. Sprawdź ponownie.'); }
    redirect('?page=chatgpt-import');
}
function gpt_import_instructions(): string {
    $intro=ux('添付のスキャン画像・PDFや会話から、DAISHO CRMへの追加・修正JSONを作ってください。読めない情報を推測しないでください。不明・空欄は省略し、既存データを消さないでください。会社名・氏名は既存の表記に合わせ、同一人物か不明なら質問してください。修正は指定フィールドだけ。依頼されていない情報の削除は禁止。JSONだけ出力してください。','Convert the scan/PDF or discussion into DAISHO CRM changes. Do not guess unreadable information. Omit unknown/blank fields; preserve existing data. Use existing company/person spelling; ask if identity is uncertain. Change only requested fields. Return JSON only.','Zamień skan/PDF lub rozmowę na zmiany w DAISHO CRM. Nie zgaduj nieczytelnych danych. Pomijaj nieznane/puste pola. Zachowaj pisownię firmy i osoby. Zapytaj, jeśli tożsamość jest niepewna. Zmieniaj tylko wskazane pola. Zwróć tylko JSON.');
    $schema=<<<'SCHEMA'

Format: {"operations":[{"entity":"contact","action":"upsert","match":{"email":"person@example.com"},"set":{"company":"EXACT COMPANY NAME","name":"FULL NAME","email":"person@example.com"}}]}
1–20 operations. entity=contact|activity|task. contact actions=create|update|upsert (upsert updates one match or creates if none); activity=create|update; task=update only.
match: use the CRM-supplied id if available (never invent it). Otherwise contact: email OR company+name; activity: company+activity_date+subject; task: company+title. match values identify the OLD record, set contains NEW values. For a new contact, include company and name in set, even if email is present. For activity create include company, activity_date, subject and summary in at least one language.
If provided by CRM, copy expected unchanged. Dates YYYY-MM-DD. Do not invent a scan date. Empty/null set values are ignored. To erase a known field only when explicitly requested, use clear:["phone"] etc. product_codes:[] explicitly removes product links; omit it to preserve links.
SCHEMA;
    foreach(['contact','activity','task'] as $entity) $schema.="\n".$entity.' set fields: '.implode(', ',array_keys(gpt_fields($entity)));
    $schema.="\nactivity_type=meeting|email|phone|sample|test|note. activity status=open|done; task status=open|waiting|conditional|done. Put the contact name in the activity summary. When changing a translated field, provide all known translations; old omitted translations in that group are cleared to avoid stale text. Do not translate names or email addresses. Activity next_action is an agreed outstanding action only. A new activity with next_action creates a task. Updating an older activity does not automatically change an unlinked task: include an explicit task update using its CRM context when needed. Related linked task fields are shown in preview and updated together. Keep task status unchanged unless explicitly instructed.\nproduct_codes: ".implode(', ',db()->query('SELECT code FROM products ORDER BY code')->fetchAll(PDO::FETCH_COLUMN));
    return $intro."\n".$schema;
}
function gpt_edit_link(string $entity,int $id): void {
    ?><a class="linkbtn" href="?page=chatgpt-import&amp;entity=<?=h($entity)?>&amp;id=<?=$id?>"><?=h(ux('ChatGPTで修正','Edit with ChatGPT','Edytuj z ChatGPT'))?></a><?php
}
function render_chatgpt_import(array $user): void {
    $entity=$_GET['entity']??''; $id=(int)($_GET['id']??0);
    if(is_string($entity) && in_array($entity,['contact','activity','task'],true) && $id>0) {
        $row=gpt_snapshot($entity,$id);
        if($row) {
            try {
                gpt_authorize($entity,$row,$user);
                $context=['entity'=>$entity,'action'=>'update','match'=>['id'=>$id],'expected'=>gpt_fingerprint($row),'current'=>array_intersect_key($row,gpt_fields($entity))];
                $text=gpt_import_instructions()."\nCRM CURRENT RECORD (read-only context; exclude current from output):\n".gpt_json($context);
                ?><section class="card"><h2><?=h(ux('この記録を修正','Update this record','Zmień ten wpis'))?> #<?=$id?></h2><p><?=h(ux('これをChatGPTに渡し、スキャン画像や修正内容と一緒に「更新して」と伝えてください。','Give this to ChatGPT with your scan or corrections and ask it to update the record.','Przekaż to ChatGPT wraz ze skanem lub poprawkami i poproś o aktualizację.'))?></p><textarea id="gpt-edit-context" rows="10" readonly onclick="this.select()"><?=h($text)?></textarea><button type="button" onclick="const t=document.getElementById('gpt-edit-context');t.select();t.setSelectionRange(0,t.value.length);try{document.execCommand('copy')}catch(e){}"><?=h(ux('依頼文をコピー','Copy instructions','Kopiuj instrukcję'))?></button></section><?php
            } catch(InvalidArgumentException $e) { echo '<p class="error">'.h($e->getMessage()).'</p>'; }
        }
    }
    render_chatgpt_import_legacy($user);
    $draft=$_SESSION['gpt_v2_draft']??null;
    if(!$draft || $draft['uid']!==(int)$user['id'] || time()-$draft['created']>1800) return;
    ?><section class="card"><h2><?=h(ux('変更前後を確認','Review changes','Sprawdź zmiany'))?></h2><p><?=h(ux('空欄・不明な項目は維持します。表示された項目だけ変更します。修正する場合は上のJSONを貼り直し、もう一度確認してください。','Blank/unknown fields are preserved. Only displayed fields change. To revise, replace the JSON above and preview again.','Puste/nieznane pola pozostają bez zmian. Zmienią się tylko pokazane pola. Aby poprawić, wklej JSON ponownie i sprawdź.'))?></p>
    <?php foreach($draft['items'] as $p): ?><article class="card"><h3><?=h(['contact'=>ux('名刺','Business card','Wizytówka'),'activity'=>ux('日報','Sales record','Wpis sprzedażowy'),'task'=>ux('タスク','Task','Zadanie')][$p['entity']])?> — <?=h($p['id']?ux('更新','Update','Aktualizacja').' #'.$p['id']:ux('新規追加','Create','Nowy wpis'))?></h3><p><?=h(($p['before']['company']??$p['set']['company']??'').' / '.($p['before']['name']??$p['before']['subject']??$p['before']['title']??''))?></p><div class="table-scroll"><table><thead><tr><th><?=h(ux('項目','Field','Pole'))?></th><th><?=h(ux('変更前','Before','Przed'))?></th><th><?=h(ux('変更後','After','Po'))?></th></tr></thead><tbody>
    <?php foreach($p['set'] as $field=>$value): ?><tr><td><?=h(gpt_field_label($field))?></td><td><?=nl2br(h(gpt_display($p['before'][$field]??null)))?></td><td><?=nl2br(h(gpt_display($value)))?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php if(!empty($p['linked_task'])): $linked=$p['linked_task']; $after=array_replace($p['before'],$p['set']); ?><h4><?=h(ux('関連タスクも更新','Related task changes','Zmiany powiązanego zadania'))?> #<?=(int)$linked['id']?></h4><div class="table-scroll"><table><tbody><?php foreach(['next_action'=>'detail','next_action_en'=>'detail_en','next_action_pl'=>'detail_pl','next_action_date'=>'due_date'] as $activityField=>$taskField): ?><tr><td><?=h(gpt_field_label($activityField))?></td><td><?=nl2br(h(gpt_display($linked[$taskField]??null)))?></td><td><?=nl2br(h(gpt_display($after[$activityField]??null)))?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    <?php if($p['entity']==='activity'): ?><p><?=h(!empty($p['linked_task'])?ux('関連タスク #'.$p['linked_task']['id'].' の次アクション・期限・会社も同期します。タスクの状態は維持します。','Linked task #'.$p['linked_task']['id'].' action, due date and company will also be synchronized; status is preserved.','Powiązane zadanie #'.$p['linked_task']['id'].' zostanie zsynchronizowane; status pozostanie bez zmian.'):($p['id']?ux('既存タスクは変更しません。必要な場合はタスクの「ChatGPTで修正」を使ってください。','Existing tasks are not changed. Use Edit with ChatGPT on the task when needed.','Istniejące zadania nie zostaną zmienione. W razie potrzeby edytuj zadanie osobno.'):ux('次のアクションがあれば未完了タスクも作成します。','A next action creates an open task.','Następne działanie utworzy otwarte zadanie.')))?></p><?php endif; ?></article><?php endforeach; ?>
    <form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_commit"><input type="hidden" name="draft_token" value="<?=h($draft['token'])?>"><button><?=h(ux('この内容で反映する','Apply these changes','Zastosuj zmiany'))?></button></form><form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_cancel"><button class="linkbtn"><?=h(ux('取り消す','Cancel','Anuluj'))?></button></form></section><?php
}
function gpt_display($value): string { return is_array($value)?implode(', ',$value):($value===null?'—':(string)$value); }
function gpt_field_label(string $field): string {
    $labels=['company'=>['会社','Company','Firma'],'name'=>['氏名','Name','Imię i nazwisko'],'title'=>['役職 / 件名','Title','Stanowisko / Tytuł'],'email'=>['メール','Email','Email'],'phone'=>['電話','Phone','Telefon'],'mobile'=>['携帯','Mobile','Komórka'],'address'=>['住所','Address','Adres'],'notes'=>['メモ','Notes','Notatki'],'card_source'=>['名刺の出典','Card source','Źródło'],'card_received_on'=>['受領日','Received','Otrzymano'],'card_scanned_on'=>['スキャン日','Scanned','Skan'],'activity_date'=>['日付','Date','Data'],'subject'=>['件名','Subject','Temat'],'product_codes'=>['製品','Products','Produkty'],'next_action'=>['次のアクション','Next action','Następne działanie'],'next_action_date'=>['期限','Due date','Termin'],'due_date'=>['期限','Due date','Termin'],'summary_ja'=>['内容（日本語）','Summary (JA)','Opis (JA)'],'summary_en'=>['内容（英語）','Summary (EN)','Opis (EN)'],'summary_pl'=>['内容（ポーランド語）','Summary (PL)','Opis (PL)'],'status'=>['状態','Status','Status']];
    return isset($labels[$field])?ux(...$labels[$field]):$field;
}
