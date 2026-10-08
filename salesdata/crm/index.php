<?php
declare(strict_types=1);

session_start();
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

const APP_NAME = 'DAISHO Sales & Technical CRM';
const INITIAL_PASSWORD_HASH = '$2y$12$h72JAMnv.W/P42/O42Au5uAhEDCsbS/ZJVMxsA3reyEhZcP/nIaYK';

function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function db_path(): string {
    $env = getenv('DAISHO_CRM_DB');
    return $env ?: '/var/lib/global-daisho-crm/crm.sqlite';
}
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $path = db_path();
    $dir = dirname($path);
    if (!is_dir($dir)) @mkdir($dir, 0770, true);
    $pdo = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    migrate($pdo);
    return $pdo;
}
function migrate(PDO $db): void {
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, display_name TEXT NOT NULL, password_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'user', must_change_password INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, last_login_at TEXT);
CREATE TABLE IF NOT EXISTS companies (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, country TEXT, website TEXT, notes TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS contacts (id INTEGER PRIMARY KEY AUTOINCREMENT, company_id INTEGER, name TEXT NOT NULL, title TEXT, email TEXT, phone TEXT, notes TEXT, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT NOT NULL, category TEXT, summary TEXT, status TEXT DEFAULT 'active');
CREATE TABLE IF NOT EXISTS activities (id INTEGER PRIMARY KEY AUTOINCREMENT, activity_date TEXT NOT NULL, company_id INTEGER, contact_id INTEGER, activity_type TEXT NOT NULL DEFAULT 'meeting', subject TEXT NOT NULL, summary_ja TEXT, summary_en TEXT, summary_pl TEXT, next_action TEXT, next_action_date TEXT, status TEXT NOT NULL DEFAULT 'open', created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS activity_products (activity_id INTEGER NOT NULL, product_id INTEGER NOT NULL, PRIMARY KEY(activity_id, product_id), FOREIGN KEY(activity_id) REFERENCES activities(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS tests (id INTEGER PRIMARY KEY AUTOINCREMENT, test_date TEXT, company_id INTEGER, product_id INTEGER, site TEXT, title TEXT NOT NULL, purpose TEXT, result TEXT, next_step TEXT, status TEXT NOT NULL DEFAULT 'planned', FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, due_date TEXT, company_id INTEGER, contact_id INTEGER, title TEXT NOT NULL, detail TEXT, status TEXT NOT NULL DEFAULT 'open', priority TEXT NOT NULL DEFAULT 'normal', assigned_to INTEGER, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL, FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS company_product_status (company_id INTEGER NOT NULL, product_id INTEGER NOT NULL, stage TEXT NOT NULL DEFAULT 'interest', summary TEXT, next_action TEXT, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(company_id,product_id), FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE);
SQL);
    seed($db);
    sync_current_sales_data($db);
}
function seed(PDO $db): void {
    if ((int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
        $stmt = $db->prepare('INSERT INTO products(code,name,category,summary) VALUES(?,?,?,?)');
        foreach ([['S-CHEM','S-Chem','Soil additive','粘性土・土質改良向け混和剤'],['DEEPER','DEEPER','Excavation aid','硬質地盤・風化岩等の掘削支援'],['D-RETARDER','D Retarder','Retarder','グルコン酸系遅延剤'],['REAPER','REAPER','Shield/TBM','シールド・TBM向けビット交換回数低減を狙う製品'],['DT','DT剤','Excavation aid','掘削性改善・軟弱化用途']] as $r) $stmt->execute($r);
    }
    if ((int)$db->query('SELECT COUNT(*) FROM companies')->fetchColumn() === 0) {
        $companies = ['鹿島建設株式会社','東急建設株式会社','株式会社テノックス九州','日特建設株式会社','ライト工業株式会社'];
        $s=$db->prepare('INSERT OR IGNORE INTO companies(name,country) VALUES(?,?)'); foreach($companies as $c) $s->execute([$c,'Japan']);
        $ids=[]; foreach($db->query('SELECT id,name FROM companies') as $r) $ids[$r['name']]=$r['id'];
        $c=$db->prepare('INSERT INTO contacts(company_id,name,title,notes) VALUES(?,?,?,?)');
        $c->execute([$ids['鹿島建設株式会社'],'柳井','鹿島技術研究所','飛田給。技術研究所で面談。']);
        $c->execute([$ids['東急建設株式会社'],'高松 伸行',null,'TBM注入決壊工法・DEEPER関連']);
        $c->execute([$ids['株式会社テノックス九州'],'田邊 亜紀子','技術部','DT剤試験施工']);
        $c->execute([$ids['日特建設株式会社'],'佐藤','技術部長','梶田氏から紹介']);
        $c->execute([$ids['日特建設株式会社'],'梶田','副社長/取締役クラス（正式役職要確認）','佐藤技術部長へ紹介']);
    }
    if ((int)$db->query('SELECT COUNT(*) FROM activities')->fetchColumn() === 0) {
        $co=[]; foreach($db->query('SELECT id,name FROM companies') as $r) $co[$r['name']]=$r['id']; $ct=[]; foreach($db->query('SELECT id,name FROM contacts') as $r) $ct[$r['name']]=$r['id']; $p=[]; foreach($db->query('SELECT id,code FROM products') as $r) $p[$r['code']]=$r['id'];
        $a=$db->prepare('INSERT INTO activities(activity_date,company_id,contact_id,activity_type,subject,summary_ja,next_action,next_action_date,status) VALUES(?,?,?,?,?,?,?,?,?)');
        $a->execute(['2026-09-25',$co['鹿島建設株式会社'],$ct['柳井'],'meeting','鹿島技術研究所 会社・製品紹介','会社紹介。NUSとチュウ先生とのS-Chem開発経緯に強い関心。REAPERはシールドのビット交換回数を削減できれば非常に良いとの評価。土砂をゲル状にして吸引搬送する案も相談。無機系薬剤での検討ニーズあり。','S-Chem動画送付、吸引試験候補薬剤検討、柳井氏から土木担当者紹介待ち','2026-09-26','open']);
        $aid=(int)$db->lastInsertId(); foreach(['S-CHEM','REAPER'] as $code) $db->prepare('INSERT INTO activity_products VALUES(?,?)')->execute([$aid,$p[$code]]);
        $a->execute(['2026-09-25',$co['東急建設株式会社'],$ct['高松 伸行'],'sample','DEEPER実証用サンプル引渡し','DEEPERを使用するとコンクリートが壊れやすくなるかを実証するためのサンプルを引き渡し。','実証結果確認・Zoom打合せ','2026-10-30','open']); $aid=(int)$db->lastInsertId(); $db->prepare('INSERT INTO activity_products VALUES(?,?)')->execute([$aid,$p['DEEPER']]);
        $a->execute(['2026-09-24',$co['株式会社テノックス九州'],$ct['田邊 亜紀子'],'email','DT剤 試験施工状況報告','9/17〜19試験。DT+水側で風化頁岩が明らかに軟弱化。φ1200mmも問題なく掘削。今後はボーリングコアで連続性と圧縮強度を確認。','詳細結果・コア結果の確認',null,'open']); $aid=(int)$db->lastInsertId(); $db->prepare('INSERT INTO activity_products VALUES(?,?)')->execute([$aid,$p['DT']]);
        $a->execute(['2026-09-25',$co['日特建設株式会社'],$ct['佐藤'],'meeting','日特建設 技術打合せ','梶田氏の紹介で佐藤技術部長と面談。遅延剤、DEEPER、S-Chemに強い関心。埼玉・橋田で実験に向けた打合せを行うことになった。','埼玉・橋田で実験打合せ','2026-10-07','open']); $aid=(int)$db->lastInsertId(); foreach(['D-RETARDER','DEEPER','S-CHEM'] as $code) $db->prepare('INSERT INTO activity_products VALUES(?,?)')->execute([$aid,$p[$code]]);
        $t=$db->prepare('INSERT INTO tests(test_date,company_id,site,title,purpose,status) VALUES(?,?,?,?,?,?)'); $t->execute(['2026-09-30',$co['ライト工業株式会社'],'ライト現場','圧送テスト','圧送性・施工性の確認','planned']);
        $task=$db->prepare('INSERT INTO tasks(due_date,company_id,contact_id,title,detail,priority) VALUES(?,?,?,?,?,?)'); $task->execute(['2026-09-26',$co['鹿島建設株式会社'],$ct['柳井'],'S-Chem動画を送付','面談時に約束したS-Chem動画を送る','high']); $task->execute(['2026-10-07',$co['日特建設株式会社'],$ct['佐藤'],'橋田 実験打合せ','遅延剤・DEEPER・S-Chemの適用候補を準備','high']);
    }
}

function sync_current_sales_data(PDO $db): void {
    // Products used in current sales / development discussions.
    $products = [
        ['S-CHEM','S-Chem','Soil additive','粘性土・土質改良向け混和剤'],
        ['DEEPER','DEEPER','Excavation aid','岩盤・コンクリート・地山の掘削支援／浸透・軟化評価'],
        ['D-RETARDER','D Retarder','Retarder','セメントの凝結時間・施工可能時間を調整'],
        ['REAPER','REAPER','Shield/TBM','シールド・TBM向けビット交換回数低減を狙う製品'],
        ['DT','DT剤','Excavation aid','掘削性改善・軟弱化用途'],
        ['OP-FLOW','OP-flow','Jet grouting','OPTジェット工法向け流動・施工補助'],
        ['ACE-CHEM','エースケム','Soil additive','機械攪拌・ジェット工法への適用を評価中'],
        ['V10','V10','Pumping aid','圧送・パンピング用途の候補材'],
        ['NF-U','NF-U','Multi-purpose additive','圧送・吹付け・注入・裏込め等への展開候補'],
        ['BENTONITE-AID','ベントナイト膨潤補助剤','Bentonite','ベントナイトの膨潤性・配合最適化'],
        ['BENTONITE-EMULSION','ベントナイト代替エマルジョン','Bentonite replacement','少量添加で粘性を制御するベントナイト代替候補']
    ];
    $ps=$db->prepare('INSERT INTO products(code,name,category,summary) VALUES(?,?,?,?) ON CONFLICT(code) DO UPDATE SET name=excluded.name,category=excluded.category,summary=excluded.summary');
    foreach($products as $r) $ps->execute($r);

    foreach(['鹿島建設株式会社','東急建設株式会社','ジャパンパイル株式会社','日特建設株式会社','ライト工業株式会社','大林組'] as $name){
        $db->prepare("INSERT OR IGNORE INTO companies(name,country) VALUES(?, 'Japan')")->execute([$name]);
    }
    $co=[]; foreach($db->query('SELECT id,name FROM companies') as $r) $co[$r['name']]=(int)$r['id'];
    $pr=[]; foreach($db->query('SELECT id,code FROM products') as $r) $pr[$r['code']]=(int)$r['id'];

    $contact=$db->prepare('INSERT INTO contacts(company_id,name,title,notes) SELECT ?,?,?,? WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE company_id=? AND name=?)');
    foreach([
        ['鹿島建設株式会社','柳井 修司','技術研究所 主席研究員・博士（工学）','TBM・シールド・材料開発'],
        ['東急建設株式会社','高松 伸行',null,'DEEPER・Jet Clay関連'],
        ['東急建設株式会社','藤井 貴裕',null,'DEEPER噴射試験関連'],
        ['日特建設株式会社','阿部',null,'事業本部 技術開発部'],
        ['日特建設株式会社','竹谷',null,'事業本部 技術開発部'],
        ['ライト工業株式会社','吉田',null,'OP-flow／横浜現場'],
        ['ライト工業株式会社','長井（長さん）',null,'圧送材・NF-U評価'],
        ['ライト工業株式会社','黒柳','本部長','NF-U用途展開']
    ] as $r){
        [$cn,$nm,$ti,$no]=$r; $contact->execute([$co[$cn],$nm,$ti,$no,$co[$cn],$nm]);
    }

    $up=$db->prepare("INSERT INTO company_product_status(company_id,product_id,stage,summary,next_action,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)
        ON CONFLICT(company_id,product_id) DO UPDATE SET stage=excluded.stage,summary=excluded.summary,next_action=excluded.next_action,updated_at=CURRENT_TIMESTAMP");
    $rows = [
        ['鹿島建設株式会社','DEEPER','共同評価','TBM・シールド用途。地山軟化→掘削抵抗・トルク・発熱低減→カッター／マシン保護を評価。高強度コンクリート、岩盤、鏡切りも候補。','DEEPERサンプル送付。鹿島側でコンクリート・岩盤・TBM想定試験。詳細解析時はNDA検討。'],
        ['鹿島建設株式会社','BENTONITE-AID','試験準備','鹿島側がシールド用ベントナイトを複数種類送付予定。相性と適正配合を大翔側で評価。','ベントナイト受領後、各材料の膨潤性・適正配合を試験。'],
        ['鹿島建設株式会社','BENTONITE-EMULSION','強い関心','欧州でのベントナイト代替材料。少量添加・粘度制御・裏込め等への展開に関心。','サンプル・供給条件を整理し、共同評価テーマを設定。'],
        ['鹿島建設株式会社','REAPER','関心','シールド分野でビット交換頻度低減の可能性を協議。','対象地盤・施工条件に合う案件を探索。'],
        ['鹿島建設株式会社','S-CHEM','関心','NUS Chiu先生との共同開発経緯を紹介。','関連動画・技術資料を共有し次テーマへ接続。'],
        ['東急建設株式会社','DEEPER','試験予定','高松様・藤井様とDEEPER／Jet Clayを協議。噴射テストを実施する方針。','DEEPER噴射試験を行い結果を返答。'],
        ['ライト工業株式会社','OP-FLOW','受注見込','大林組横浜現場。11月開始予定、OP-flow約50tの見込み。','12月初旬に吉田様へ使用感ヒアリング。'],
        ['ライト工業株式会社','NF-U','強い関心','長井（長）様、黒柳本部長へ紹介。吹付け・注入・裏込め材代替・ベントナイト関連など複数用途案。','試験材を手配し、用途別の適用条件・試験配合を整理。'],
        ['ライト工業株式会社','V10','紹介済','圧送材・パンピング用途として紹介。','NF-U、S-Chemと並行して適用条件を整理。'],
        ['ライト工業株式会社','S-CHEM','紹介済','圧送材・パンピング用途として紹介。','圧送試験候補として評価条件を整理。'],
        ['日特建設株式会社','ACE-CHEM','評価予定','機械攪拌で使用できるか検討中。ジェット工法（既存材Nジェット）への適用も検討。','エースケムのサンプルを送付し、機械攪拌・ジェット工法で評価。'],
        ['ジャパンパイル株式会社','S-CHEM','試験中','北海道土を用いた試験を進行。','ジャパンパイル実験の返答時期を管理し、試験結果を返答。']
    ];
    foreach($rows as $r) if(isset($co[$r[0]],$pr[$r[1]])) $up->execute([$co[$r[0]],$pr[$r[1]],$r[2],$r[3],$r[4]]);

    // Dated activities from the latest reports.
    $ins=$db->prepare("INSERT INTO activities(activity_date,company_id,activity_type,subject,summary_ja,next_action,status)
        SELECT ?,?,'meeting',?,?,?,'open' WHERE NOT EXISTS (SELECT 1 FROM activities WHERE activity_date=? AND company_id=? AND subject=?)");
    $dated=[
        ['2026-10-07','日特建設株式会社','エースケム 機械攪拌・ジェット工法評価','阿部様、竹谷様と面談。エースケムに関心。機械攪拌で使用できるか検討中。日特建設のジェット工法材「Nジェット」があり、ジェット工法へのエースケム適用も検討。','エースケムのサンプル送付。機械攪拌およびジェット工法で評価。'],
        ['2026-10-08','ライト工業株式会社','圧送材・NF-U用途展開','長井（長）様と面談。V10、S-Chem、NF-Uを紹介。NF-Uへの反応が特に強く、黒柳本部長にも紹介。ベントナイトを扱う技術担当者も強い関心。吹付け、注入、裏込め材代替などの用途案が出た。','NF-U試験材を手配し、適用条件と用途別試験を整理。']
    ];
    foreach($dated as $r){ $cid=$co[$r[1]]; $ins->execute([$r[0],$cid,$r[2],$r[3],$r[4],$r[0],$cid,$r[2]]); }

    // Product links for the two dated activities.
    foreach([
        ['2026-10-07','日特建設株式会社','エースケム 機械攪拌・ジェット工法評価',['ACE-CHEM']],
        ['2026-10-08','ライト工業株式会社','圧送材・NF-U用途展開',['V10','S-CHEM','NF-U']]
    ] as $x){
        [$d,$cn,$sub,$codes]=$x;
        $q=$db->prepare('SELECT id FROM activities WHERE activity_date=? AND company_id=? AND subject=? ORDER BY id DESC LIMIT 1'); $q->execute([$d,$co[$cn],$sub]); $aid=(int)$q->fetchColumn();
        if($aid) foreach($codes as $code) $db->prepare('INSERT OR IGNORE INTO activity_products(activity_id,product_id) VALUES(?,?)')->execute([$aid,$pr[$code]]);
    }
}

function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function check_csrf(): void { if(!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')) { http_response_code(403); exit('CSRF validation failed'); } }
function user_count(): int { return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(); }
function current_user(): ?array { if(empty($_SESSION['uid'])) return null; $s=db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$_SESSION['uid']]); return $s->fetch() ?: null; }
function require_login(): array { $u=current_user(); if(!$u){ header('Location:?page=login'); exit; } return $u; }
function redirect(string $to): never { header('Location:'.$to); exit; }

$action=$_POST['action']??'';
if($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    if($action==='bootstrap' && user_count()===0) {
        $code=(string)($_POST['initial_password']??''); $pw=(string)($_POST['password']??'');
        if(!password_verify($code, INITIAL_PASSWORD_HASH)) $error='初期パスワードが違います。'; elseif(strlen($pw)<8) $error='新しいパスワードは8文字以上にしてください。'; else { $s=db()->prepare('INSERT INTO users(username,display_name,password_hash,role,must_change_password) VALUES(?,?,?,?,0)'); try{$s->execute([trim($_POST['username']??'admin'),trim($_POST['display_name']??'Administrator'),password_hash($pw,PASSWORD_DEFAULT),'admin']); $_SESSION['uid']=(int)db()->lastInsertId(); redirect('?');} catch(Throwable $e){$error='ユーザー名が使用済みです。';} }
    } elseif($action==='login') {
        $s=db()->prepare('SELECT * FROM users WHERE username=?'); $s->execute([trim($_POST['username']??'')]); $u=$s->fetch(); if(!$u || !password_verify((string)($_POST['password']??''),$u['password_hash'])) $error='ユーザー名またはパスワードが違います。'; else {$_SESSION['uid']=(int)$u['id']; db()->prepare('UPDATE users SET last_login_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$u['id']]); redirect($u['must_change_password']?'?page=change-password':'?');}
    } elseif($action==='logout') { session_destroy(); redirect('?page=login'); }
    elseif($action==='change_password') { $u=require_login(); $pw=(string)($_POST['password']??''); if(strlen($pw)<8) $error='新しいパスワードは8文字以上にしてください。'; else {db()->prepare('UPDATE users SET password_hash=?,must_change_password=0 WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$u['id']]); redirect('?');} }
    elseif($action==='add_user') { $u=require_login(); if($u['role']!=='admin') exit('Forbidden'); $s=db()->prepare('INSERT INTO users(username,display_name,password_hash,role,must_change_password) VALUES(?,?,?,?,1)'); try{$s->execute([trim($_POST['username']),trim($_POST['display_name']),INITIAL_PASSWORD_HASH,$_POST['role']==='admin'?'admin':'user']); $notice='ユーザーを追加しました。初回パスワードは0921、ログイン後に変更必須です。';}catch(Throwable $e){$error='ユーザーを追加できませんでした。';} }
    elseif($action==='add_company') { require_login(); db()->prepare('INSERT INTO companies(name,country,website,notes) VALUES(?,?,?,?)')->execute([trim($_POST['name']),trim($_POST['country']),trim($_POST['website']),trim($_POST['notes'])]); redirect('?page=companies'); }
    elseif($action==='add_activity') { $u=require_login(); $s=db()->prepare('INSERT INTO activities(activity_date,company_id,contact_id,activity_type,subject,summary_ja,summary_en,summary_pl,next_action,next_action_date,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)'); $s->execute([$_POST['activity_date'],$_POST['company_id']?:null,$_POST['contact_id']?:null,$_POST['activity_type'],trim($_POST['subject']),trim($_POST['summary_ja']),trim($_POST['summary_en']),trim($_POST['summary_pl']),trim($_POST['next_action']),$_POST['next_action_date']?:null,$_POST['status'],$u['id']]); redirect('?page=activities'); }
    elseif($action==='task_done') { require_login(); db()->prepare("UPDATE tasks SET status='done' WHERE id=?")->execute([(int)$_POST['id']]); redirect('?'); }
}
$page=$_GET['page']??'dashboard'; if(user_count()===0) $page='bootstrap'; $u=current_user(); if(!in_array($page,['login','bootstrap'],true) && !$u) redirect('?page=login'); if($u && $u['must_change_password'] && $page!=='change-password') $page='change-password';
function header_html(string $title, ?array $u): void { ?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> | <?=APP_NAME?></title><link rel="stylesheet" href="style.css"></head><body>
<?php if($u): ?><header class="top"><div><strong>DAISHO</strong><span>Sales & Technical CRM</span></div><nav><a href="?">Dashboard</a><a href="?page=activities">営業履歴</a><a href="?page=companies">会社</a><a href="?page=products">製品/SDS</a><a href="?page=matrix">横串ビュー</a><a href="?page=tests">試験</a><?php if($u['role']==='admin'):?><a href="?page=users">Users</a><?php endif;?></nav><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="logout"><button class="linkbtn">Logout</button></form></header><?php endif; ?><main class="wrap"><h1><?=h($title)?></h1><?php }
function footer_html(): void { echo '</main></body></html>'; }
if($page==='bootstrap'){ header_html('初期設定',null); ?><div class="auth card"><p>初回のみ、初期パスワード <b>0921</b> で管理者を登録します。登録後は0921ではログインできません。</p><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="bootstrap"><label>初期パスワード<input type="password" name="initial_password" required></label><label>表示名<input name="display_name" required></label><label>ユーザー名<input name="username" value="taiju" required></label><label>新しいパスワード<input type="password" name="password" minlength="8" required></label><button>管理者を登録</button></form></div><?php footer_html(); exit; }
if($page==='login'){ header_html('Login',null); ?><div class="auth card"><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="login"><label>ユーザー名<input name="username" required autofocus></label><label>パスワード<input type="password" name="password" required></label><button>ログイン</button></form></div><?php footer_html(); exit; }
if($page==='change-password'){ $u=require_login(); header_html('パスワード変更',$u); ?><div class="auth card"><p>初回ログインです。新しいパスワードを登録してください。</p><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="change_password"><label>新しいパスワード<input type="password" name="password" minlength="8" required></label><button>変更して開始</button></form></div><?php footer_html(); exit; }
$u=require_login();
if($page==='dashboard'){ header_html('営業ダッシュボード',$u); $stats=['companies'=>(int)db()->query('SELECT COUNT(*) FROM companies')->fetchColumn(),'activities'=>(int)db()->query('SELECT COUNT(*) FROM activities')->fetchColumn(),'open_tasks'=>(int)db()->query("SELECT COUNT(*) FROM tasks WHERE status='open'")->fetchColumn(),'planned_tests'=>(int)db()->query("SELECT COUNT(*) FROM tests WHERE status='planned'")->fetchColumn()]; ?><div class="stats"><?php foreach($stats as $k=>$v):?><div class="stat"><b><?=$v?></b><span><?=h(str_replace('_',' ',$k))?></span></div><?php endforeach;?></div><div class="grid2"><section class="card"><h2>次のアクション</h2><?php $q=db()->query("SELECT tasks.*,companies.name company FROM tasks LEFT JOIN companies ON companies.id=tasks.company_id WHERE tasks.status='open' ORDER BY COALESCE(due_date,'9999-12-31') LIMIT 12"); foreach($q as $r):?><div class="row"><div><b><?=h($r['due_date'])?> <?=h($r['title'])?></b><small><?=h($r['company'])?> / <?=h($r['detail'])?></small></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="task_done"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="small">完了</button></form></div><?php endforeach;?></section><section class="card"><h2>予定試験</h2><?php foreach(db()->query("SELECT tests.*,companies.name company FROM tests LEFT JOIN companies ON companies.id=tests.company_id WHERE tests.status='planned' ORDER BY test_date LIMIT 12") as $r):?><div class="row"><div><b><?=h($r['test_date'])?> <?=h($r['title'])?></b><small><?=h($r['company'])?> / <?=h($r['site'])?></small></div></div><?php endforeach;?></section></div><section class="card"><h2>最近の営業履歴</h2><?php foreach(db()->query("SELECT a.*,c.name company,ct.name contact FROM activities a LEFT JOIN companies c ON c.id=a.company_id LEFT JOIN contacts ct ON ct.id=a.contact_id ORDER BY activity_date DESC,id DESC LIMIT 8") as $r):?><article class="activity"><div class="date"><?=h($r['activity_date'])?></div><div><b><?=h($r['company'])?> / <?=h($r['contact'])?> — <?=h($r['subject'])?></b><p><?=nl2br(h($r['summary_ja']))?></p><?php if($r['next_action']):?><small>Next: <?=h($r['next_action_date'])?> <?=h($r['next_action'])?></small><?php endif;?></div></article><?php endforeach;?></section><?php }
elseif($page==='activities'){ header_html('営業履歴',$u); $companies=db()->query('SELECT * FROM companies ORDER BY name')->fetchAll(); $contacts=db()->query('SELECT * FROM contacts ORDER BY name')->fetchAll(); ?><div class="grid2"><section class="card"><h2>新規営業記録</h2><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_activity"><label>日付<input type="date" name="activity_date" value="<?=date('Y-m-d')?>" required></label><label>会社<select name="company_id"><option value="">--</option><?php foreach($companies as $c):?><option value="<?=$c['id']?>"><?=h($c['name'])?></option><?php endforeach;?></select></label><label>担当者<select name="contact_id"><option value="">--</option><?php foreach($contacts as $c):?><option value="<?=$c['id']?>"><?=h($c['name'])?></option><?php endforeach;?></select></label><label>種別<select name="activity_type"><option>meeting</option><option>email</option><option>phone</option><option>sample</option><option>test</option></select></label><label>件名<input name="subject" required></label><label>日本語<textarea name="summary_ja" rows="6"></textarea></label><label>English<textarea name="summary_en" rows="4"></textarea></label><label>Polski<textarea name="summary_pl" rows="4"></textarea></label><label>次アクション<input name="next_action"></label><label>期限<input type="date" name="next_action_date"></label><input type="hidden" name="status" value="open"><button>保存</button></form></section><section class="card"><h2>履歴</h2><?php foreach(db()->query("SELECT a.*,c.name company,ct.name contact FROM activities a LEFT JOIN companies c ON c.id=a.company_id LEFT JOIN contacts ct ON ct.id=a.contact_id ORDER BY activity_date DESC,id DESC") as $r):?><article class="activity"><div class="date"><?=h($r['activity_date'])?></div><div><b><?=h($r['company'])?> / <?=h($r['contact'])?></b><h3><?=h($r['subject'])?></h3><p><?=nl2br(h($r['summary_ja']))?></p><?php if($r['summary_en']):?><details><summary>English</summary><p><?=nl2br(h($r['summary_en']))?></p></details><?php endif;?><?php if($r['summary_pl']):?><details><summary>Polski</summary><p><?=nl2br(h($r['summary_pl']))?></p></details><?php endif;?><small>Next: <?=h($r['next_action_date'])?> <?=h($r['next_action'])?></small></div></article><?php endforeach;?></section></div><?php }
elseif($page==='companies'){ header_html('会社・担当者',$u); ?><div class="grid2"><section class="card"><h2>会社一覧</h2><?php foreach(db()->query('SELECT c.*,COUNT(ct.id) contacts FROM companies c LEFT JOIN contacts ct ON ct.company_id=c.id GROUP BY c.id ORDER BY c.name') as $r):?><div class="row"><div><b><?=h($r['name'])?></b><small><?=h($r['country'])?> / contacts: <?=$r['contacts']?></small></div></div><?php endforeach;?></section><section class="card"><h2>会社追加</h2><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_company"><label>会社名<input name="name" required></label><label>国<input name="country"></label><label>URL<input name="website"></label><label>メモ<textarea name="notes"></textarea></label><button>追加</button></form></section></div><?php }
elseif($page==='products'){ header_html('製品 / SDS',$u); ?><section class="card"><p>SDSは既存の <code>/salesdata/sds/</code> を自動参照します。製品DBと営業履歴を同じ画面から辿れるようにします。</p><div class="products"><?php foreach(db()->query('SELECT * FROM products ORDER BY name') as $r):?><div class="product"><b><?=h($r['name'])?></b><small><?=h($r['code'])?> / <?=h($r['category'])?></small><p><?=h($r['summary'])?></p></div><?php endforeach;?></div><h2>SDSファイル</h2><div class="sds-list"><?php $files=glob(__DIR__.'/../sds/*.{html,pdf}',GLOB_BRACE)?:[]; sort($files); foreach($files as $f): $n=basename($f);?><a href="../sds/<?=rawurlencode($n)?>" target="_blank"><?=h($n)?></a><?php endforeach;?></div></section><?php }

elseif($page==='matrix'){ header_html('営業 横串ビュー',$u); ?>
<section class="card">
  <div class="matrix-head"><div><h2>商品ごと</h2><p>同じ商品が、どの会社で・どの段階まで進んでいるかを横断して確認します。</p></div></div>
  <div class="table-scroll"><table class="matrix"><thead><tr><th>商品</th><th>会社</th><th>段階</th><th>現在地</th><th>次アクション</th></tr></thead><tbody>
  <?php
  $q=db()->query("SELECT p.name product,p.code,c.name company,s.stage,s.summary,s.next_action
      FROM company_product_status s JOIN products p ON p.id=s.product_id JOIN companies c ON c.id=s.company_id
      ORDER BY p.name,c.name");
  foreach($q as $r): ?>
    <tr><td><b><?=h($r['product'])?></b><small><?=h($r['code'])?></small></td><td><?=h($r['company'])?></td><td><span class="stage"><?=h($r['stage'])?></span></td><td><?=h($r['summary'])?></td><td><?=h($r['next_action'])?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
<section class="card">
  <div class="matrix-head"><div><h2>会社ごと</h2><p>各社で動いている商品・テーマ・次の一手をまとめて確認します。</p></div></div>
  <div class="table-scroll"><table class="matrix"><thead><tr><th>会社</th><th>商品</th><th>段階</th><th>現在地</th><th>次アクション</th></tr></thead><tbody>
  <?php
  $q=db()->query("SELECT c.name company,p.name product,p.code,s.stage,s.summary,s.next_action
      FROM company_product_status s JOIN products p ON p.id=s.product_id JOIN companies c ON c.id=s.company_id
      ORDER BY c.name,p.name");
  foreach($q as $r): ?>
    <tr><td><b><?=h($r['company'])?></b></td><td><?=h($r['product'])?><small><?=h($r['code'])?></small></td><td><span class="stage"><?=h($r['stage'])?></span></td><td><?=h($r['summary'])?></td><td><?=h($r['next_action'])?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
<?php }

elseif($page==='tests'){ header_html('試験・現場テスト',$u); ?><section class="card"><?php foreach(db()->query("SELECT t.*,c.name company,p.name product FROM tests t LEFT JOIN companies c ON c.id=t.company_id LEFT JOIN products p ON p.id=t.product_id ORDER BY COALESCE(test_date,'9999-12-31')") as $r):?><article class="activity"><div class="date"><?=h($r['test_date'])?></div><div><b><?=h($r['title'])?></b><p><?=h($r['company'])?> / <?=h($r['site'])?> / <?=h($r['product'])?></p><small>Status: <?=h($r['status'])?></small></div></article><?php endforeach;?></section><?php }
elseif($page==='users'){ if($u['role']!=='admin') exit('Forbidden'); header_html('ユーザー管理',$u); ?><div class="grid2"><section class="card"><h2>Users</h2><?php foreach(db()->query('SELECT username,display_name,role,must_change_password,last_login_at FROM users ORDER BY id') as $r):?><div class="row"><div><b><?=h($r['display_name'])?> (<?=h($r['username'])?>)</b><small><?=h($r['role'])?> / <?= $r['must_change_password']?'初回変更待ち':'active' ?> / last: <?=h($r['last_login_at'])?></small></div></div><?php endforeach;?></section><section class="card"><h2>ユーザー追加</h2><?php if(!empty($notice)):?><p class="ok"><?=h($notice)?></p><?php endif;?><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><p>追加ユーザーの初期パスワードは <b>0921</b>。初回ログイン時に変更必須です。</p><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_user"><label>表示名<input name="display_name" required></label><label>ユーザー名<input name="username" required></label><label>権限<select name="role"><option value="user">user</option><option value="admin">admin</option></select></label><button>追加</button></form></section></div><?php }
footer_html();
