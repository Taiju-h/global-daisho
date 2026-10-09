<?php
declare(strict_types=1);

session_start();
header('Cache-Control: no-store, private');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

const APP_NAME = 'DAISHO Sales & Technical CRM';
require_once __DIR__.'/contact-tools.php';
const INITIAL_PASSWORD_HASH = '$2y$12$h72JAMnv.W/P42/O42Au5uAhEDCsbS/ZJVMxsA3reyEhZcP/nIaYK';

function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function current_lang(): string {
    $allowed=['ja','en','pl'];
    if(isset($_GET['lang']) && in_array($_GET['lang'],$allowed,true)) $_SESSION['lang']=$_GET['lang'];
    $lang=(string)($_SESSION['lang']??'ja');
    return in_array($lang,$allowed,true)?$lang:'ja';
}
function tr(string $key): string {
    static $d=[
      'ja'=>[
        'action_status_open'=>'要対応','action_status_waiting'=>'先方・前工程待ち','action_status_conditional'=>'条件付き','source_email'=>'根拠メール',
        'reset_password'=>'パスワードをリセット',
        'temporary_password'=>'仮パスワード（12文字以上）',
        'admin_password'=>'確認用：ご自身のCRMパスワード',
        'reset_help'=>'対象ユーザー名を確認して仮パスワードを設定してください。相手は次回ログイン後に変更が必要です。',
        'reset_done'=>'パスワードをリセットしました。対象ユーザー名と仮パスワードを本人に伝えてください。',
        'reset_invalid'=>'対象ユーザーまたは確認用パスワードが正しくありません。',
        'reset_short'=>'仮パスワードは12文字以上72バイト以下で入力してください。',
        'login_failed'=>'ユーザー名またはパスワードが違います。',
        'login_help'=>'CRM専用のユーザー名・パスワードを入力してください。入口のBasic認証やGitHubとは別です。忘れた場合はTaijuにリセットを依頼してください。',
        'first_change'=>'仮パスワードでログインしています。新しいパスワードを設定してください。',
        'new_password'=>'新しいパスワード',
        'start'=>'変更して開始',
        'dashboard'=>'営業ダッシュボード','activities'=>'営業履歴','companies'=>'会社・名刺','products'=>'製品 / SDS','matrix'=>'営業 横串ビュー','tests'=>'試験・現場テスト','users'=>'ユーザー管理',
        'next_actions'=>'次のアクション','planned_tests'=>'予定試験','recent_activities'=>'最近の営業履歴','new_activity'=>'新規営業記録','history'=>'履歴','date'=>'日付','company'=>'会社','contact'=>'担当者','type'=>'種別','subject'=>'件名','japanese'=>'日本語','english'=>'English','polish'=>'Polski','next_action'=>'次アクション','due'=>'期限','save'=>'保存','done'=>'完了',
        'company_list'=>'会社一覧','add_company'=>'会社追加','company_name'=>'会社名','country'=>'国','url'=>'URL','memo'=>'メモ','add'=>'追加',
        'product_axis'=>'商品ごと','company_axis'=>'会社ごと','stage'=>'段階','current'=>'現在地','product'=>'商品',
        'product_axis_desc'=>'同じ商品が、どの会社で・どの段階まで進んでいるかを横断して確認します。','company_axis_desc'=>'各社で動いている商品・テーマ・次の一手をまとめて確認します。',
        'sds_desc'=>'SDSは既存の /salesdata/sds/ を自動参照します。製品DBと営業履歴を同じ画面から辿れます。','sds_files'=>'SDSファイル',
        'login'=>'ログイン','username'=>'ユーザー名','password'=>'パスワード','change_password'=>'パスワード変更','language'=>'言語','owner'=>'登録者'
      ],
      'en'=>[
        'action_status_open'=>'Action required','action_status_waiting'=>'Waiting / dependency','action_status_conditional'=>'Conditional','source_email'=>'Source email',
        'reset_password'=>'Reset password',
        'temporary_password'=>'Temporary password (at least 12 characters)',
        'admin_password'=>'Confirm with your own CRM password',
        'reset_help'=>'Check the target username and set a temporary password. The user must change it at the next login.',
        'reset_done'=>'Password reset. Give the username and temporary password to the account owner.',
        'reset_invalid'=>'The selected user or your confirmation password is incorrect.',
        'reset_short'=>'Use at least 12 characters and no more than 72 bytes for the temporary password.',
        'login_failed'=>'Incorrect username or password.',
        'login_help'=>'Enter your CRM username and password. These are separate from Basic authentication and GitHub. Ask Taiju for a reset if you have forgotten them.',
        'first_change'=>'You are using a temporary password. Set a new password to continue.',
        'new_password'=>'New password',
        'start'=>'Change and continue',
        'dashboard'=>'Sales Dashboard','activities'=>'Sales Activities','companies'=>'Companies / Cards','products'=>'Products / SDS','matrix'=>'Cross-Reference View','tests'=>'Tests / Field Trials','users'=>'User Management',
        'next_actions'=>'Next Actions','planned_tests'=>'Planned Tests','recent_activities'=>'Recent Sales Activities','new_activity'=>'New Sales Record','history'=>'History','date'=>'Date','company'=>'Company','contact'=>'Contact','type'=>'Type','subject'=>'Subject','japanese'=>'Japanese','english'=>'English','polish'=>'Polish','next_action'=>'Next Action','due'=>'Due Date','save'=>'Save','done'=>'Done',
        'company_list'=>'Company List','add_company'=>'Add Company','company_name'=>'Company Name','country'=>'Country','url'=>'URL','memo'=>'Notes','add'=>'Add',
        'product_axis'=>'By Product','company_axis'=>'By Company','stage'=>'Stage','current'=>'Current Status','product'=>'Product',
        'product_axis_desc'=>'See which companies are working with each product, the current stage, and the next action.','company_axis_desc'=>'See active products, themes and next actions for each company.',
        'sds_desc'=>'Existing files under /salesdata/sds/ are referenced automatically. Products, SDS and sales history can be followed from the same area.','sds_files'=>'SDS Files',
        'login'=>'Login','username'=>'Username','password'=>'Password','change_password'=>'Change Password','language'=>'Language','owner'=>'Owner'
      ],
      'pl'=>[
        'action_status_open'=>'Do wykonania','action_status_waiting'=>'Oczekuje / zależność','action_status_conditional'=>'Warunkowe','source_email'=>'Mail źródłowy',
        'reset_password'=>'Zresetuj hasło',
        'temporary_password'=>'Hasło tymczasowe (co najmniej 12 znaków)',
        'admin_password'=>'Potwierdź własnym hasłem do CRM',
        'reset_help'=>'Sprawdź nazwę użytkownika i ustaw hasło tymczasowe. Użytkownik musi je zmienić przy następnym logowaniu.',
        'reset_done'=>'Hasło zresetowane. Przekaż nazwę użytkownika i hasło tymczasowe właścicielowi konta.',
        'reset_invalid'=>'Wybrany użytkownik lub Twoje hasło potwierdzające jest nieprawidłowe.',
        'reset_short'=>'Hasło tymczasowe musi mieć co najmniej 12 znaków i nie więcej niż 72 bajty.',
        'login_failed'=>'Nieprawidłowa nazwa użytkownika lub hasło.',
        'login_help'=>'Wpisz nazwę użytkownika i hasło do CRM. Są one odrębne od uwierzytelniania Basic i GitHub. Jeśli ich nie pamiętasz, poproś Taiju o reset.',
        'first_change'=>'Korzystasz z hasła tymczasowego. Ustaw nowe hasło, aby kontynuować.',
        'new_password'=>'Nowe hasło',
        'start'=>'Zmień i kontynuuj',
        'dashboard'=>'Panel sprzedaży','activities'=>'Historia sprzedaży','companies'=>'Firmy / Wizytówki','products'=>'Produkty / SDS','matrix'=>'Widok przekrojowy','tests'=>'Testy / Próby terenowe','users'=>'Użytkownicy',
        'next_actions'=>'Następne działania','planned_tests'=>'Planowane testy','recent_activities'=>'Ostatnie działania sprzedażowe','new_activity'=>'Nowy wpis sprzedażowy','history'=>'Historia','date'=>'Data','company'=>'Firma','contact'=>'Kontakt','type'=>'Typ','subject'=>'Temat','japanese'=>'Japoński','english'=>'Angielski','polish'=>'Polski','next_action'=>'Następne działanie','due'=>'Termin','save'=>'Zapisz','done'=>'Gotowe',
        'company_list'=>'Lista firm','add_company'=>'Dodaj firmę','company_name'=>'Nazwa firmy','country'=>'Kraj','url'=>'URL','memo'=>'Notatki','add'=>'Dodaj',
        'product_axis'=>'Według produktu','company_axis'=>'Według firmy','stage'=>'Etap','current'=>'Aktualny status','product'=>'Produkt',
        'product_axis_desc'=>'Przekrojowy widok: w jakich firmach działa dany produkt, na jakim jest etapie i jaki jest kolejny krok.','company_axis_desc'=>'Produkty, tematy i kolejne działania dla każdej firmy.',
        'sds_desc'=>'Pliki w /salesdata/sds/ są automatycznie dostępne. Produkty, SDS i historię sprzedaży można śledzić w jednym miejscu.','sds_files'=>'Pliki SDS',
        'login'=>'Logowanie','username'=>'Użytkownik','password'=>'Hasło','change_password'=>'Zmiana hasła','language'=>'Język','owner'=>'Właściciel'
      ]
    ];
    $lang=current_lang();
    return $d[$lang][$key]??$d['ja'][$key]??$key;
}
function legacy_translation(?string $value): string {
    $source=(string)$value;
    $lang=current_lang();
    if($lang==='ja' || $source==='') return $source;
    static $catalog=null;
    if($catalog===null) $catalog=require __DIR__.'/legacy-translations.php';
    return $catalog[$lang][$source] ?? $source;
}
function localized(array $row, string $base): string {
    $lang=current_lang();
    $source=(string)($row[$base] ?? ($base==='summary' ? ($row['summary_ja']??'') : ''));
    if($lang==='ja') return $source;
    $translated=(string)($row[$base.'_'.$lang]??'');
    return $translated!==''?$translated:legacy_translation($source);
}
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    // Credentials live outside the Git worktree and web DocumentRoot.
    $configFile = '/etc/global-daisho/crm-db.php';
    $config = is_file($configFile) && is_readable($configFile) ? require $configFile : null;
    $dsn = is_array($config) ? ($config['dsn'] ?? null) : null;
    $user = is_array($config) ? ($config['user'] ?? null) : null;
    $password = is_array($config) ? ($config['password'] ?? null) : null;
    if (!is_string($dsn) || $dsn === '' || !is_string($user) || $user === '' || !is_string($password)) {
        error_log('CRM MySQL configuration missing');
        http_response_code(503);
        exit('CRM database is not configured.');
    }
    if (!str_starts_with($dsn, 'mysql:')) {
        error_log('CRM requires a MySQL PDO DSN');
        http_response_code(503);
        exit('CRM database configuration invalid.');
    }
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    migrate($pdo);
    return $pdo;
}
function migrate(PDO $db): void {
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(191) NOT NULL UNIQUE, display_name VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, role VARCHAR(30) NOT NULL DEFAULT 'user', must_change_password TINYINT NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, last_login_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS companies (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(191) NOT NULL UNIQUE, country VARCHAR(255), website TEXT, notes TEXT, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS contacts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, company_id BIGINT UNSIGNED NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255), email VARCHAR(255), phone VARCHAR(100), notes TEXT, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(191) UNIQUE, name VARCHAR(255) NOT NULL, category VARCHAR(255), summary TEXT, status VARCHAR(30) DEFAULT 'active') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS activities (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, activity_date VARCHAR(10) NOT NULL, company_id BIGINT UNSIGNED NULL, contact_id BIGINT UNSIGNED NULL, activity_type VARCHAR(30) NOT NULL DEFAULT 'meeting', subject VARCHAR(255) NOT NULL, summary_ja TEXT, summary_en TEXT, summary_pl TEXT, next_action TEXT, next_action_date VARCHAR(10), status VARCHAR(30) NOT NULL DEFAULT 'open', created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS activity_products (activity_id BIGINT UNSIGNED NOT NULL, product_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(activity_id, product_id), FOREIGN KEY(activity_id) REFERENCES activities(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS tests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, test_date VARCHAR(10), company_id BIGINT UNSIGNED NULL, product_id BIGINT UNSIGNED NULL, site VARCHAR(255), title VARCHAR(255) NOT NULL, purpose TEXT, result TEXT, next_step TEXT, status VARCHAR(30) NOT NULL DEFAULT 'planned', FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS tasks (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, due_date VARCHAR(10), company_id BIGINT UNSIGNED NULL, contact_id BIGINT UNSIGNED NULL, title VARCHAR(255) NOT NULL, detail TEXT, status VARCHAR(30) NOT NULL DEFAULT 'open', priority VARCHAR(30) NOT NULL DEFAULT 'normal', assigned_to BIGINT UNSIGNED NULL, FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE SET NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL, FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS company_product_status (company_id BIGINT UNSIGNED NOT NULL, product_id BIGINT UNSIGNED NOT NULL, stage VARCHAR(100) NOT NULL DEFAULT 'interest', summary TEXT, next_action TEXT, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(company_id,product_id), FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($tables as $sql) $db->exec($sql);
    ensure_column($db,'products','summary_en','TEXT NULL');
    ensure_column($db,'products','summary_pl','TEXT NULL');
    ensure_column($db,'company_product_status','stage_en','VARCHAR(100) NULL');
    ensure_column($db,'company_product_status','stage_pl','VARCHAR(100) NULL');
    ensure_column($db,'company_product_status','summary_en','TEXT NULL');
    ensure_column($db,'company_product_status','summary_pl','TEXT NULL');
    ensure_column($db,'company_product_status','next_action_en','TEXT NULL');
    ensure_column($db,'company_product_status','next_action_pl','TEXT NULL');
    ensure_column($db,'activities','next_action_en','TEXT NULL');
    ensure_column($db,'activities','next_action_pl','TEXT NULL');
    ensure_column($db,'activities','owner_name','VARCHAR(191) NULL');
    ensure_column($db,'company_product_status','owner_name','VARCHAR(191) NULL');
    ensure_column($db,'tests','owner_name','VARCHAR(191) NULL');
    ensure_column($db,'tasks','owner_name','VARCHAR(191) NULL');
    ensure_column($db,'users','auth_version','BIGINT UNSIGNED NOT NULL DEFAULT 0');
    seed($db);
    sync_current_sales_data($db);
    import_reviewed_mail_actions($db);
    migrate_card_tools($db);
}
function ensure_column(PDO $db,string $table,string $column,string $definition): void {
    $q=$db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $q->execute([$table,$column]);
    if((int)$q->fetchColumn()===0) $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
}
function seed(PDO $db): void {
    if ((int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
        $stmt = $db->prepare('INSERT INTO products(code,name,category,summary) VALUES(?,?,?,?)');
        foreach ([['S-CHEM','S-Chem','Soil additive','粘性土・土質改良向け混和剤'],['DEEPER','DEEPER','Excavation aid','硬質地盤・風化岩等の掘削支援'],['D-RETARDER','D Retarder','Retarder','グルコン酸系遅延剤'],['REAPER','REAPER','Shield/TBM','シールド・TBM向けビット交換回数低減を狙う製品'],['DT','DT剤','Excavation aid','掘削性改善・軟弱化用途']] as $r) $stmt->execute($r);
    }
    if ((int)$db->query('SELECT COUNT(*) FROM companies')->fetchColumn() === 0) {
        $companies = ['鹿島建設株式会社','東急建設株式会社','株式会社テノックス九州','日特建設株式会社','ライト工業株式会社'];
        $s=$db->prepare('INSERT IGNORE INTO companies(name,country) VALUES(?,?)'); foreach($companies as $c) $s->execute([$c,'Japan']);
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
        ['S-CHEM','S-Chem','Soil additive','粘性土・土質改良向け混和剤','Additive for cohesive soil and ground improvement','Dodatek do gruntów spoistych i ulepszania podłoża'],
        ['DEEPER','DEEPER','Excavation aid','岩盤・コンクリート・地山の掘削支援／浸透・軟化評価','Excavation aid for rock, concrete and ground; penetration/softening evaluation','Wspomaganie urabiania skał, betonu i gruntu; ocena penetracji i zmiękczania'],
        ['D-RETARDER','D Retarder','Retarder','セメントの凝結時間・施工可能時間を調整','Controls cement setting time and workable time','Regulacja czasu wiązania cementu i czasu roboczego'],
        ['REAPER','REAPER','Shield/TBM','シールド・TBM向けビット交換回数低減を狙う製品','Product aimed at reducing cutter/bit replacement in shield and TBM work','Produkt mający ograniczyć wymianę narzędzi tnących w tarczach i TBM'],
        ['DT','DT剤','Excavation aid','掘削性改善・軟弱化用途','Improves excavability and softens target material','Poprawa urabialności i zmiękczanie materiału'],
        ['OP-FLOW','OP-flow','Jet grouting','OPTジェット工法向け流動・施工補助','Flow and施工 support for OPT jet grouting','Wspomaganie przepływu i wykonawstwa w technologii OPT Jet'],
        ['ACE-CHEM','エースケム','Soil additive','機械攪拌・ジェット工法への適用を評価中','Under evaluation for mechanical mixing and jet grouting','W trakcie oceny do mieszania mechanicznego i iniekcji strumieniowej'],
        ['V10','V10','Pumping aid','圧送・パンピング用途の候補材','Candidate material for pumping applications','Materiał kandydujący do zastosowań pompowych'],
        ['NF-U','NF-U','Multi-purpose additive','圧送・吹付け・注入・裏込め等への展開候補','Candidate for pumping, spraying, injection and backfilling','Kandydat do pompowania, natrysku, iniekcji i wypełniania'],
        ['BENTONITE-AID','ベントナイト膨潤補助剤','Bentonite','ベントナイトの膨潤性・配合最適化','Improves bentonite swelling and formulation','Poprawa pęcznienia bentonitu i optymalizacja receptury'],
        ['BENTONITE-EMULSION','ベントナイト代替エマルジョン','Bentonite replacement','少量添加で粘性を制御するベントナイト代替候補','Bentonite-replacement emulsion for viscosity control at low dosage','Emulsja zastępująca bentonit do kontroli lepkości przy małym dozowaniu']
    ];
    $ps=$db->prepare('INSERT INTO products(code,name,category,summary,summary_en,summary_pl) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),category=VALUES(category),summary=VALUES(summary),summary_en=VALUES(summary_en),summary_pl=VALUES(summary_pl)');
    foreach($products as $r) $ps->execute($r);

    foreach(['鹿島建設株式会社','東急建設株式会社','ジャパンパイル株式会社','日特建設株式会社','ライト工業株式会社','大林組'] as $name){
        $db->prepare("INSERT IGNORE INTO companies(name,country) VALUES(?, 'Japan')")->execute([$name]);
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

    $up=$db->prepare("INSERT INTO company_product_status(company_id,product_id,stage,stage_en,stage_pl,summary,summary_en,summary_pl,next_action,next_action_en,next_action_pl,owner_name,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE stage=VALUES(stage),stage_en=VALUES(stage_en),stage_pl=VALUES(stage_pl),summary=VALUES(summary),summary_en=VALUES(summary_en),summary_pl=VALUES(summary_pl),next_action=VALUES(next_action),next_action_en=VALUES(next_action_en),next_action_pl=VALUES(next_action_pl),owner_name=VALUES(owner_name),updated_at=CURRENT_TIMESTAMP");
    $rows = [
        ['鹿島建設株式会社','DEEPER','共同評価','Joint evaluation','Wspólna ocena','TBM・シールド用途。地山軟化→掘削抵抗・トルク・発熱低減→カッター／マシン保護を評価。高強度コンクリート、岩盤、鏡切りも候補。','TBM/shield application: evaluate ground softening, lower excavation resistance, torque and heat, and resulting cutter/machine protection. High-strength concrete, rock and face cutting are also candidates.','Zastosowanie TBM/tarcza: ocena zmiękczania gruntu, zmniejszenia oporu urabiania, momentu i nagrzewania oraz ochrony narzędzi/maszyny. Kandydaci: beton wysokiej wytrzymałości, skała i cięcie czoła.','DEEPERサンプル送付。鹿島側でコンクリート・岩盤・TBM想定試験。詳細解析時はNDA検討。','Send DEEPER samples. Kajima to evaluate concrete, rock and TBM use. Consider NDA for detailed analysis.','Wysłać próbki DEEPER. Kajima oceni beton, skałę i zastosowanie TBM. Przy szczegółowej analizie rozważyć NDA.'],
        ['鹿島建設株式会社','BENTONITE-AID','試験準備','Test preparation','Przygotowanie testów','鹿島側がシールド用ベントナイトを複数種類送付予定。相性と適正配合を大翔側で評価。','Kajima plans to send several shield-use bentonites. Daisho will evaluate compatibility and optimum dosage.','Kajima planuje wysłać kilka bentonitów do tarcz. Daisho oceni kompatybilność i optymalne dozowanie.','ベントナイト受領後、各材料の膨潤性・適正配合を試験。','After receiving bentonite, test swelling and optimum formulation.','Po otrzymaniu bentonitu sprawdzić pęcznienie i optymalną recepturę.'],
        ['鹿島建設株式会社','BENTONITE-EMULSION','強い関心','Strong interest','Duże zainteresowanie','欧州でのベントナイト代替材料。少量添加・粘度制御・裏込め等への展開に関心。','Strong interest in a European bentonite replacement: low dosage, viscosity control and backfilling applications.','Duże zainteresowanie europejskim zamiennikiem bentonitu: małe dozowanie, kontrola lepkości i zastosowania do wypełniania.','サンプル・供給条件を整理し、共同評価テーマを設定。','Arrange samples and supply conditions, then define a joint evaluation theme.','Ustalić próbki i warunki dostaw, następnie zdefiniować wspólny temat oceny.'],
        ['鹿島建設株式会社','REAPER','関心','Interest','Zainteresowanie','シールド分野でビット交換頻度低減の可能性を協議。','Discussed the possibility of reducing bit replacement frequency in shield work.','Omówiono możliwość zmniejszenia częstotliwości wymiany narzędzi w pracach tarczowych.','対象地盤・施工条件に合う案件を探索。','Find projects with suitable ground and construction conditions.','Szukać projektów z odpowiednimi warunkami gruntowymi i wykonawczymi.'],
        ['鹿島建設株式会社','S-CHEM','関心','Interest','Zainteresowanie','NUS Chiu先生との共同開発経緯を紹介。','Introduced the joint development history with Prof. Chiu of NUS.','Przedstawiono historię wspólnego rozwoju z prof. Chiu z NUS.','関連動画・技術資料を共有し次テーマへ接続。','Share videos and technical materials and connect to the next technical theme.','Udostępnić filmy i materiały techniczne oraz przejść do kolejnego tematu.'],
        ['東急建設株式会社','DEEPER','試験予定','Test planned','Test planowany','高松様・藤井様とDEEPER／Jet Clayを協議。噴射テストを実施する方針。','Discussed DEEPER/Jet Clay with Mr. Takamatsu and Mr. Fujii. Plan to conduct a spray/jet test.','Omówiono DEEPER/Jet Clay z panem Takamatsu i panem Fujii. Planowany test natrysku/strumienia.','DEEPER噴射試験を行い結果を返答。','Conduct DEEPER spray test and report the results.','Przeprowadzić test natrysku DEEPER i przekazać wyniki.'],
        ['ライト工業株式会社','OP-FLOW','受注見込','Likely order','Prawdopodobne zamówienie','大林組横浜現場。11月開始予定、OP-flow約50tの見込み。','Obayashi Yokohama site. Start planned in November; approximately 50 t of OP-flow expected.','Budowa Obayashi w Jokohamie. Start planowany w listopadzie; przewidywane ok. 50 t OP-flow.','12月初旬に吉田様へ使用感ヒアリング。','Follow up with Mr. Yoshida in early December for usage feedback.','Na początku grudnia zebrać opinię pana Yoshidy z użytkowania.'],
        ['ライト工業株式会社','NF-U','強い関心','Strong interest','Duże zainteresowanie','長井（長）様、黒柳本部長へ紹介。吹付け・注入・裏込め材代替・ベントナイト関連など複数用途案。','Introduced to Mr. Cho (Nagai) and General Manager Kuroyanagi. Multiple application ideas: spraying, injection, backfill replacement and bentonite-related uses.','Przedstawiono panu Cho (Nagai) i dyrektorowi Kuroyanagi. Pomysły: natrysk, iniekcja, zamiennik materiału wypełniającego i zastosowania bentonitowe.','試験材を手配し、用途別の適用条件・試験配合を整理。','Arrange test material and define application conditions and test formulations by use.','Przygotować materiał testowy oraz warunki zastosowania i receptury dla poszczególnych zastosowań.'],
        ['ライト工業株式会社','V10','紹介済','Introduced','Przedstawiono','圧送材・パンピング用途として紹介。','Introduced as a candidate for pumping applications.','Przedstawiono jako materiał do zastosowań pompowych.','NF-U、S-Chemと並行して適用条件を整理。','Define application conditions in parallel with NF-U and S-Chem.','Określić warunki zastosowania równolegle z NF-U i S-Chem.'],
        ['ライト工業株式会社','S-CHEM','紹介済','Introduced','Przedstawiono','圧送材・パンピング用途として紹介。','Introduced for pumping applications.','Przedstawiono do zastosowań pompowych.','圧送試験候補として評価条件を整理。','Define evaluation conditions for a pumping test.','Określić warunki oceny do testu pompowania.'],
        ['日特建設株式会社','ACE-CHEM','評価予定','Evaluation planned','Planowana ocena','機械攪拌で使用できるか検討中。ジェット工法（既存材Nジェット）への適用も検討。','Under consideration for mechanical mixing and jet grouting, alongside Nittoku\'s existing N-Jet material.','Rozważane zastosowanie do mieszania mechanicznego i jet grouting obok istniejącego materiału N-Jet firmy Nittoku.','エースケムのサンプルを送付し、機械攪拌・ジェット工法で評価。','Send ACE-CHEM sample and evaluate in mechanical mixing and jet grouting.','Wysłać próbkę ACE-CHEM i ocenić w mieszaniu mechanicznym oraz jet grouting.'],
        ['ジャパンパイル株式会社','S-CHEM','試験中','Testing','W testach','北海道土を用いた試験を進行。','Testing in progress using Hokkaido soil.','Trwają testy z użyciem gruntu z Hokkaido.','ジャパンパイル実験の返答時期を管理し、試験結果を返答。','Manage the response timing and report the test results to Japan Pile.','Kontrolować termin odpowiedzi i przekazać wyniki testów Japan Pile.']
    ];
    foreach($rows as $r) if(isset($co[$r[0]],$pr[$r[1]])) $up->execute([$co[$r[0]],$pr[$r[1]],...array_slice($r,2),'Taiju']);

    // Dated activities from the latest reports.
    $ins=$db->prepare("INSERT INTO activities(activity_date,company_id,activity_type,subject,summary_ja,next_action,status,owner_name)
        SELECT ?,?,'meeting',?,?,?,'open','Taiju' WHERE NOT EXISTS (SELECT 1 FROM activities WHERE activity_date=? AND company_id=? AND subject=?)");
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
        if($aid) foreach($codes as $code) $db->prepare('INSERT IGNORE INTO activity_products(activity_id,product_id) VALUES(?,?)')->execute([$aid,$pr[$code]]);
    }

    // All legacy/imported records currently in this CRM were entered from Taiju's sales data.
    $db->exec("UPDATE activities SET owner_name='Taiju' WHERE owner_name IS NULL OR owner_name=''");
    $db->exec("UPDATE company_product_status SET owner_name='Taiju' WHERE owner_name IS NULL OR owner_name=''");
    $db->exec("UPDATE tests SET owner_name='Taiju' WHERE owner_name IS NULL OR owner_name=''");
    $db->exec("UPDATE tasks SET owner_name='Taiju' WHERE owner_name IS NULL OR owner_name=''");
}


function import_reviewed_mail_actions(PDO $db): void {
    // Source-linked review snapshot, 2026-10-10 JST. Never reopen completed imports.
    foreach(['title_en','title_pl','detail_en','detail_pl','source_url'] as $column) ensure_column($db,'tasks',$column,'TEXT NULL');
    $db->exec("CREATE TABLE IF NOT EXISTS crm_mail_imports (source_key VARCHAR(191) PRIMARY KEY, task_id BIGINT UNSIGNED NULL, imported_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $records=[['key'=>'tryme-trial-schedule-20260925','company'=>'トライム株式会社','contact'=>'志田 唯花','email'=>'y-shida@tryme.co.jp','status'=>'open','due'=>null,'priority'=>'normal','source'=>'1a0d54ef1267e508','title'=>['トライム：帰国予定を連絡し11月の試作日程を調整','Tryme: confirm availability and arrange the November trial','Tryme: potwierdzić dostępność i uzgodnić listopadową próbę'],'detail'=>['9/25に帰国予定は分かり次第連絡すると返信。9/14の先方提案は11月頃の試作。確認したメールでは日程確定の返信なし。帰国予定・対応可能日を志田様に伝え、原料搬入と試作日を調整する。ソーダ灰単体・軽灰・25kg袋詰めは回答済み。SDS・写真は9/18送付済み。期限未合意。','On 25 Sep, Taiju promised to provide his return date once known. Tryme proposed a trial around November. No confirmed trial date was found in the reviewed emails. Send availability to Ms Shida and coordinate delivery of the material and the trial date. Light soda ash alone, repacked into 25 kg bags, was already specified; SDS and photos were sent on 18 Sep. No deadline agreed.','25 września Taiju zapowiedział przekazanie daty powrotu. Tryme proponowało próbę około listopada. W sprawdzonych mailach brak uzgodnionej daty. Przekazać dostępność pani Shida i uzgodnić dostawę materiału oraz termin próby. Ustalono lekką sodę kalcynowaną bez domieszek i worki 25 kg. SDS i zdjęcia wysłano 18 września. Brak ustalonego terminu odpowiedzi.']],['key'=>'kajima-bentonite-address-20261008','company'=>'鹿島建設株式会社','contact'=>'佐藤 一成','email'=>'satohis@kajima.com','status'=>'open','due'=>null,'priority'=>'high','source'=>'1a119c8a93655956','title'=>['鹿島建設：ベントナイトの受取住所を佐藤様へ回答','Kajima: give Mr Sato the bentonite delivery address','Kajima: podać panu Sato adres dostawy bentonitu'],'detail'=>['10/8に佐藤様からベントナイト送付先住所の照会。10/9の返信はDEEPER発送予定のみで、受取住所の回答は確認できない。実際に受け取れる住所・宛名・連絡先を確認して回答する。住所は推測しない。期限の指定なし。','Mr Sato requested the bentonite delivery address on 8 Oct. The 9 Oct reply only confirmed the planned DEEPER shipment; no reply with the receiving address was found. Confirm and send the actual receiving address, recipient and contact details. Do not assume an address. No deadline specified.','8 października pan Sato poprosił o adres dostawy bentonitu. Odpowiedź z 9 października dotyczyła tylko wysyłki DEEPER; nie znaleziono odpowiedzi z adresem. Potwierdzić i przekazać adres odbioru, odbiorcę oraz kontakt. Nie zgadywać adresu. Brak wskazanego terminu.']],['key'=>'kajima-deeper-shipment-20261012','company'=>'鹿島建設株式会社','contact'=>'佐藤 一成','email'=>'satohis@kajima.com','status'=>'open','due'=>'2026-10-12','priority'=>'high','source'=>'1a1200d09016a70b','title'=>['鹿島建設：DEEPERサンプルを10/12発送','Kajima: ship the DEEPER sample on 12 October','Kajima: wysłać próbkę DEEPER 12 października'],'detail'=>['10/9の送信メールで10/12発送と約束済み。発送完了ではなく発送予定。送り先：〒182-0025 東京都調布市多摩川1-36-1 24号館、鹿島建設 技術研究所 佐藤一成様。発送後に追跡番号を記録し完了へ変更する（追跡番号の記録は管理上の提案）。','Taiju committed by email on 9 Oct to ship on 12 Oct; this is planned, not confirmed dispatched. Recipient: Mr Sato, Kajima Technical Research Institute, Building 24, 1-36-1 Tamagawa, Chofu, Tokyo 182-0025. After dispatch, record the tracking number and mark complete (tracking is a suggested administrative step).','W mailu z 9 października Taiju zobowiązał się wysłać próbkę 12 października. To plan, nie potwierdzenie wysyłki. Odbiorca: pan Sato, Kajima Technical Research Institute, budynek 24, 1-36-1 Tamagawa, Chofu, Tokyo 182-0025. Po wysyłce zapisać numer śledzenia i zakończyć zadanie (zapis numeru to proponowany krok organizacyjny).']],['key'=>'kajima-bentonite-receipt-20261008','company'=>'鹿島建設株式会社','contact'=>'佐藤 一成','email'=>'satohis@kajima.com','status'=>'waiting','due'=>null,'priority'=>'normal','source'=>'1a119c8a93655956','title'=>['鹿島建設：複数種類のベントナイト受領を確認','Kajima: confirm receipt of the bentonite samples','Kajima: potwierdzić odbiór próbek bentonitu'],'detail'=>['先方担当は鹿島・佐藤様。複数種類を送付する予定。まず当方の受取住所回答が必要。確認したメールでは発送・受領完了は不明。住所回答後、先方発送と当方受領を確認する。期限未合意。','Kajima / Mr Sato is to send several bentonite types. DAISHO must first provide the receiving address. Dispatch and receipt are not confirmed in the reviewed emails. After supplying the address, follow up on dispatch and confirm receipt. No agreed deadline.','Kajima / pan Sato ma wysłać kilka rodzajów bentonitu. Najpierw DAISHO musi podać adres odbioru. W sprawdzonych mailach brak potwierdzenia wysyłki i odbioru. Po podaniu adresu potwierdzić wysyłkę i odbiór. Brak uzgodnionego terminu.']],['key'=>'kajima-bentonite-evaluation-20261008','company'=>'鹿島建設株式会社','contact'=>'佐藤 一成','email'=>'satohis@kajima.com','status'=>'waiting','due'=>null,'priority'=>'normal','source'=>'1a119c8a93655956','title'=>['鹿島建設：受領後に膨潤補助剤の添加量・粘性改善を評価','Kajima: assess swelling-aid dosage and viscosity after sample receipt','Kajima: po odbiorze ocenić dozowanie dodatku i poprawę lepkości'],'detail'=>['10/8議事メモの当方担当事項。前提はベントナイト複数種の受領。種類ごとに適した膨潤補助剤の添加量と粘性改善効果を試験・記録する。未受領の段階で試験中とは扱わない。試験期限未合意。','Assigned to Taiju in the 8 Oct meeting memo. Depends on receipt of the different bentonite samples. Test and record suitable swelling-aid dosage and viscosity improvement for each type. Do not report testing as underway before samples arrive. No trial deadline agreed.','Zadanie Taiju z notatki ze spotkania 8 października. Zależy od odbioru różnych próbek bentonitu. Zbadać i zapisać odpowiednie dozowanie dodatku wspomagającego pęcznienie oraz poprawę lepkości dla każdego rodzaju. Nie oznaczać prób jako rozpoczętych przed odbiorem. Brak uzgodnionego terminu.']],['key'=>'kajima-nda-conditional-20261008','company'=>'鹿島建設株式会社','contact'=>'佐藤 一成','email'=>'satohis@kajima.com','status'=>'conditional','due'=>null,'priority'=>'normal','source'=>'1a119c8a93655956','title'=>['鹿島建設：予備試験で開発可能性を確認後にNDAを検討','Kajima: consider an NDA if preliminary tests support further development','Kajima: rozważyć NDA po pozytywnej ocenie możliwości rozwoju'],'detail'=>['条件付き事項。10/8メモでは、予備試験で開発可能性が確認され、本格検討が必要になった場合にNDA締結を検討する。現時点で締結済み・直ちに締結必須とは扱わない。期限なし。','Conditional action from the 8 Oct memo: consider an NDA if preliminary tests establish development potential and full-scale investigation becomes necessary. Not signed, and not an immediate unconditional commitment. No deadline.','Działanie warunkowe z notatki z 8 października: rozważyć NDA, jeżeli próby wstępne potwierdzą potencjał rozwoju i potrzebę pełnych badań. Nie jest to podpisana umowa ani bezwarunkowe zobowiązanie do natychmiastowego podpisania. Brak terminu.']],['key'=>'shimz-dflow-sds-check-20261006','company'=>'清水建設株式会社','contact'=>'小林 望','email'=>'kobayashi.nozomi@shimz.biz','status'=>'open','due'=>null,'priority'=>'normal','source'=>'1a10ab287de0deb1','title'=>['清水建設：D FlowのSDS同梱・提出状況を泉様に確認','Shimizu: confirm D Flow SDS delivery with Izumi','Shimizu: potwierdzić z Izumi przekazanie SDS D Flow'],'detail'=>['完了不明の確認事項。10/5に空輸用のMaterial Data Sheet添付依頼があり、泉様はSDS対応を了承。10/6に泉様からサンプル発送済みの連絡あり。サンプル発送は未処理に戻さない。SDS同梱・別送の完了は確認したメールでは不明なので、泉様に確認し、未提出の場合のみ送付を手配する。','Completion needs verification. On 5 Oct, Shimizu requested a Material Data Sheet for air transport and Izumi acknowledged the SDS request. Izumi reported the sample dispatched on 6 Oct, so do not reopen sample shipment as pending. The reviewed emails do not confirm SDS enclosure or separate delivery. Check with Izumi and arrange delivery only if still outstanding.','Należy potwierdzić wykonanie. 5 października Shimizu poprosiło o Material Data Sheet do transportu lotniczego; Izumi potwierdził przyjęcie prośby o SDS. 6 października zgłosił wysyłkę próbki, więc nie traktować jej ponownie jako niewysłanej. Brak jednoznacznego potwierdzenia dołączenia lub osobnego wysłania SDS. Sprawdzić z Izumi i wysłać tylko w razie braku.']]];
    $claim=$db->prepare('INSERT IGNORE INTO crm_mail_imports(source_key) VALUES(?)');
    $companyInsert=$db->prepare("INSERT IGNORE INTO companies(name,country) VALUES(?,'Japan')");
    $companyFind=$db->prepare('SELECT id FROM companies WHERE name=?');
    $contactFind=$db->prepare('SELECT id FROM contacts WHERE company_id=? AND (email=? OR name=?) ORDER BY id LIMIT 1');
    $contactInsert=$db->prepare('INSERT INTO contacts(company_id,name,email) VALUES(?,?,?)');
    $taskInsert=$db->prepare("INSERT INTO tasks(due_date,company_id,contact_id,title,title_en,title_pl,detail,detail_en,detail_pl,status,priority,assigned_to,owner_name,source_url) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $owner=$db->query("SELECT id FROM users WHERE username='taiju' LIMIT 1")->fetchColumn();
    $db->beginTransaction();
    try {
        foreach($records as $r) {
            $claim->execute(['mail:'.$r['key']]);
            if($claim->rowCount()===0) continue;
            $companyInsert->execute([$r['company']]);
            $companyFind->execute([$r['company']]); $cid=(int)$companyFind->fetchColumn();
            $contactFind->execute([$cid,$r['email'],$r['contact']]); $contactId=$contactFind->fetchColumn();
            if(!$contactId) { $contactInsert->execute([$cid,$r['contact'],$r['email']]); $contactId=$db->lastInsertId(); }
            $notes=[
                "\n確認日: 2026-10-10。確認済みメールに基づく整理。メール外の対応状況は未確認。",
                "\nReviewed: 10 Oct 2026. Based on reviewed emails; actions outside email are unverified.",
                "\nSprawdzono: 10 października 2026. Na podstawie maili; działania poza pocztą nie zostały potwierdzone."
            ];
            $taskInsert->execute([$r['due'],$cid,$contactId,...$r['title'],$r['detail'][0].$notes[0],$r['detail'][1].$notes[1],$r['detail'][2].$notes[2],$r['status'],$r['priority'],$owner?:null,'Taiju','https://mail.google.com/mail/u/0/#all/'.$r['source']]);
            $taskId=(int)$db->lastInsertId();
            $db->prepare('UPDATE crm_mail_imports SET task_id=? WHERE source_key=?')->execute([$taskId,'mail:'.$r['key']]);
        }
        // The original seed reminder was fulfilled by the 1 Oct email containing both video links.
        $claim->execute(['mail:kajima-schem-video-sent-20261001']);
        if($claim->rowCount()===1) {
            $db->prepare("UPDATE tasks t JOIN companies c ON c.id=t.company_id SET t.status='done' WHERE c.name=? AND t.title=? AND t.due_date=? AND t.owner_name='Taiju' AND t.status='open'")
                ->execute(['鹿島建設株式会社','S-Chem動画を送付','2026-09-26']);
        }
        $db->commit();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}

function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function check_csrf(): void {
    $expected=$_SESSION['csrf']??null;
    $provided=$_POST['csrf']??null;
    if(!is_string($expected) || $expected==='' || !is_string($provided) || !hash_equals($expected,$provided)) {
        $_SESSION['form_expired']=true;
        header('Location:'.(empty($_SESSION['uid'])?'?page=login':'?'),true,303);
        exit;
    }
}
function user_count(): int { return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(); }
function current_user(): ?array { if(empty($_SESSION['uid'])) return null; $s=db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$_SESSION['uid']]); $u=$s->fetch(); if(!$u || (int)($_SESSION['auth_version']??0)!==(int)$u['auth_version']) { unset($_SESSION['uid'],$_SESSION['auth_version']); return null; } return $u; }
function require_login(): array { $u=current_user(); if(!$u){ header('Location:?page=login'); exit; } return $u; }
function redirect(string $to): never { header('Location:'.$to); exit; }

$action=$_POST['action']??'';
if($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    if($action==='bootstrap' && user_count()===0) {
        $code=(string)($_POST['initial_password']??''); $pw=(string)($_POST['password']??'');
        if(!password_verify($code, INITIAL_PASSWORD_HASH)) $error='初期パスワードが違います。'; elseif(strlen($pw)<8) $error='新しいパスワードは8文字以上にしてください。'; else { $s=db()->prepare('INSERT INTO users(username,display_name,password_hash,role,must_change_password) VALUES(?,?,?,?,0)'); try{$s->execute([trim($_POST['username']??'admin'),trim($_POST['display_name']??'Administrator'),password_hash($pw,PASSWORD_DEFAULT),'admin']); $_SESSION['uid']=(int)db()->lastInsertId(); redirect('?');} catch(Throwable $e){$error='ユーザー名が使用済みです。';} }
    } elseif($action==='login') {
        $s=db()->prepare('SELECT * FROM users WHERE username=?'); $s->execute([trim($_POST['username']??'')]); $u=$s->fetch(); if(!$u || !password_verify((string)($_POST['password']??''),$u['password_hash'])) $error=tr('login_failed'); else {session_regenerate_id(true); $_SESSION['uid']=(int)$u['id']; $_SESSION['auth_version']=(int)$u['auth_version']; db()->prepare('UPDATE users SET last_login_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$u['id']]); redirect($u['must_change_password']?'?page=change-password':'?');}
    } elseif($action==='logout') { session_destroy(); redirect('?page=login'); }
    elseif($action==='change_password') { $u=require_login(); $pw=(string)($_POST['password']??''); if(strlen($pw)<8) $error='新しいパスワードは8文字以上にしてください。'; else {db()->prepare('UPDATE users SET password_hash=?,must_change_password=0 WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$u['id']]); redirect('?');} }
    elseif($action==='add_user') { $u=require_login(); if($u['role']!=='admin') exit('Forbidden'); $s=db()->prepare('INSERT INTO users(username,display_name,password_hash,role,must_change_password) VALUES(?,?,?,?,1)'); try{$s->execute([trim($_POST['username']),trim($_POST['display_name']),INITIAL_PASSWORD_HASH,$_POST['role']==='admin'?'admin':'user']); $notice='ユーザーを追加しました。初回パスワードは0921、ログイン後に変更必須です。';}catch(Throwable $e){$error='ユーザーを追加できませんでした。';} }
    elseif($action==='reset_password') {
        $u=require_login();
        if($u['role']!=='admin' || $u['must_change_password']) { http_response_code(403); exit('Forbidden'); }
        $targetId=(int)($_POST['target_id']??0);
        $pw=(string)($_POST['temporary_password']??'');
        $q=db()->prepare('SELECT id FROM users WHERE id=?');
        $q->execute([$targetId]);
        if(!password_verify((string)($_POST['admin_password']??''),$u['password_hash']) || !$q->fetch() || $targetId===(int)$u['id']) {
            $error=tr('reset_invalid');
        } elseif(preg_match_all('/./us',$pw)<12 || strlen($pw)>72) {
            $error=tr('reset_short');
        } else {
            db()->prepare('UPDATE users SET password_hash=?,must_change_password=1,auth_version=auth_version+1 WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$targetId]);
            $notice=tr('reset_done');
        }
    }
    elseif($action==='add_company') { require_login(); db()->prepare('INSERT INTO companies(name,country,website,notes) VALUES(?,?,?,?)')->execute([trim($_POST['name']),trim($_POST['country']),trim($_POST['website']),trim($_POST['notes'])]); redirect('?page=companies'); }
    elseif($action==='add_activity') { $u=require_login(); $s=db()->prepare('INSERT INTO activities(activity_date,company_id,contact_id,activity_type,subject,summary_ja,summary_en,summary_pl,next_action,next_action_date,status,created_by,owner_name) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)'); $s->execute([$_POST['activity_date'],$_POST['company_id']?:null,$_POST['contact_id']?:null,$_POST['activity_type'],trim($_POST['subject']),trim($_POST['summary_ja']),trim($_POST['summary_en']),trim($_POST['summary_pl']),trim($_POST['next_action']),$_POST['next_action_date']?:null,$_POST['status'],$u['id'],$u['display_name']]); redirect('?page=activities'); }
    elseif($action==='task_done') { require_login(); db()->prepare("UPDATE tasks SET status='done' WHERE id=?")->execute([(int)$_POST['id']]); redirect('?'); }
}
$page=$action==='reset_password'?'users':($_GET['page']??'dashboard'); if(user_count()===0) $page='bootstrap'; $u=current_user(); if(!in_array($page,['login','bootstrap'],true) && !$u) redirect('?page=login'); if($u && $u['must_change_password'] && $page!=='change-password') $page='change-password';
function header_html(string $title, ?array $u): void { $lang=current_lang(); ?>
<!doctype html><html lang="<?=h($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> | <?=APP_NAME?></title><link rel="stylesheet" href="style.css"></head><body>
<header class="top"><div><strong>DAISHO</strong><span>Sales & Technical CRM</span></div><?php if($u): ?><nav><a href="?"><?=h(tr('dashboard'))?></a><a href="?page=activities"><?=h(tr('activities'))?></a><a href="?page=companies"><?=h(tr('companies'))?></a><a href="?page=products"><?=h(tr('products'))?></a><a href="?page=matrix"><?=h(tr('matrix'))?></a><a href="?page=tests"><?=h(tr('tests'))?></a><?php if($u['role']==='admin'):?><a href="?page=users"><?=h(tr('users'))?></a><?php endif;?></nav><?php endif; ?><div class="lang-switch" aria-label="<?=h(tr('language'))?>"><a class="<?=current_lang()==='ja'?'active':''?>" href="?<?=http_build_query(array_merge($_GET,['lang'=>'ja']))?>">日本語</a><a class="<?=current_lang()==='en'?'active':''?>" href="?<?=http_build_query(array_merge($_GET,['lang'=>'en']))?>">EN</a><a class="<?=current_lang()==='pl'?'active':''?>" href="?<?=http_build_query(array_merge($_GET,['lang'=>'pl']))?>">PL</a></div><?php if($u): ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="logout"><button class="linkbtn">Logout</button></form><?php endif; ?></header><main class="wrap"><h1><?=h($title)?></h1><?php if(!empty($_SESSION['form_expired'])): unset($_SESSION['form_expired']); ?>
<p class="error"><?=h(['ja'=>'フォームの有効期限が切れました。もう一度入力してください。保存操作は行われていません。','en'=>'This form has expired. Please enter your details again. No changes were saved.','pl'=>'Formularz wygasł. Wpisz dane ponownie. Żadne zmiany nie zostały zapisane.'][$lang])?></p>
<?php endif; }
function footer_html(): void { echo '</main></body></html>'; }
if($page==='bootstrap'){ header_html('初期設定',null); ?><div class="auth card"><p>初回のみ、初期パスワード <b>0921</b> で管理者を登録します。登録後は0921ではログインできません。</p><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="bootstrap"><label>初期パスワード<input type="password" name="initial_password" required></label><label>表示名<input name="display_name" required></label><label><?=h(tr('username'))?><input name="username" value="taiju" required></label><label>新しいパスワード<input type="password" name="password" minlength="8" required></label><button>管理者を登録</button></form></div><?php footer_html(); exit; }
if($page==='login'){ header_html(tr('login'),null); ?><div class="auth card"><p><?=h(tr('login_help'))?></p><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="login"><label><?=h(tr('username'))?><input name="username" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus></label><label><?=h(tr('password'))?><input type="password" name="password" autocomplete="current-password" required></label><button><?=h(tr('login'))?></button></form></div><?php footer_html(); exit; }
if($page==='change-password'){ $u=require_login(); header_html(tr('change_password'),$u); ?><div class="auth card"><p><?=h(tr('first_change'))?></p><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="change_password"><label><?=h(tr('new_password'))?><input type="password" name="password" autocomplete="new-password" minlength="8" required></label><button><?=h(tr('start'))?></button></form></div><?php footer_html(); exit; }
$u=require_login();
if($page==='dashboard'){ header_html(tr('dashboard'),$u); $stats=['companies'=>(int)db()->query('SELECT COUNT(*) FROM companies')->fetchColumn(),'activities'=>(int)db()->query('SELECT COUNT(*) FROM activities')->fetchColumn(),'open_tasks'=>(int)db()->query("SELECT COUNT(*) FROM tasks WHERE status IN ('open','waiting','conditional')")->fetchColumn(),'planned_tests'=>(int)db()->query("SELECT COUNT(*) FROM tests WHERE status='planned'")->fetchColumn()]; ?><div class="stats"><?php foreach($stats as $k=>$v):?><div class="stat"><b><?=$v?></b><span><?=h(str_replace('_',' ',$k))?></span></div><?php endforeach;?></div><div class="grid2"><section class="card"><h2><?=h(tr('next_actions'))?></h2><?php $q=db()->query("SELECT tasks.*,companies.name company FROM tasks LEFT JOIN companies ON companies.id=tasks.company_id WHERE tasks.status IN ('open','waiting','conditional') ORDER BY FIELD(tasks.status,'open','waiting','conditional'),CASE WHEN tasks.priority='high' THEN 0 ELSE 1 END,COALESCE(due_date,'9999-12-31'),tasks.id"); foreach($q as $r):?><div class="row"><div><b><?=h($r['due_date'])?> <?=h(localized($r,'title'))?></b><small><?=h($r['company'])?> / <?=h(localized($r,'detail'))?></small><small><?=h(tr('action_status_'.$r['status']))?> · <?=h(tr('owner'))?>: <?=h($r['owner_name']?:'Taiju')?></small><?php if(!empty($r['source_url'])):?><a href="<?=h($r['source_url'])?>" target="_blank" rel="noopener noreferrer"><?=h(tr('source_email'))?></a><?php endif;?></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="task_done"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="small"><?=h(tr('done'))?></button></form></div><?php endforeach;?></section><section class="card"><h2><?=h(tr('planned_tests'))?></h2><?php foreach(db()->query("SELECT tests.*,companies.name company FROM tests LEFT JOIN companies ON companies.id=tests.company_id WHERE tests.status='planned' ORDER BY test_date LIMIT 12") as $r):?><div class="row"><div><b><?=h($r['test_date'])?> <?=h(legacy_translation($r['title']))?></b><small><?=h($r['company'])?> / <?=h(legacy_translation($r['site']))?></small></div></div><?php endforeach;?></section></div><section class="card"><h2><?=h(tr('recent_activities'))?></h2><?php foreach(db()->query("SELECT a.*,c.name company,ct.name contact FROM activities a LEFT JOIN companies c ON c.id=a.company_id LEFT JOIN contacts ct ON ct.id=a.contact_id ORDER BY activity_date DESC,a.id DESC LIMIT 8") as $r):?><article class="activity"><div class="date"><?=h($r['activity_date'])?></div><div><b><?=h($r['company'])?> / <?=h($r['contact'])?> — <?=h(legacy_translation($r['subject']))?></b><p><?=nl2br(h(localized($r,'summary')))?></p><?php if($r['next_action']):?><small>Next: <?=h($r['next_action_date'])?> <?=h(localized($r,'next_action'))?></small><?php endif;?></div></article><?php endforeach;?></section><?php render_completed_actions(); }
elseif($page==='activities'){ header_html(tr('activities'),$u); $companies=db()->query('SELECT * FROM companies ORDER BY name')->fetchAll(); $contacts=db()->query('SELECT * FROM contacts ORDER BY name')->fetchAll(); ?><div class="grid2"><section class="card"><h2><?=h(tr('new_activity'))?></h2><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_activity"><label><?=h(tr('date'))?><input type="date" name="activity_date" value="<?=date('Y-m-d')?>" required></label><label><?=h(tr('company'))?><select name="company_id"><option value="">--</option><?php foreach($companies as $c):?><option value="<?=$c['id']?>"><?=h($c['name'])?></option><?php endforeach;?></select></label><label><?=h(tr('contact'))?><select name="contact_id"><option value="">--</option><?php foreach($contacts as $c):?><option value="<?=$c['id']?>"><?=h($c['name'])?></option><?php endforeach;?></select></label><label><?=h(tr('type'))?><select name="activity_type"><option>meeting</option><option>email</option><option>phone</option><option>sample</option><option>test</option></select></label><label><?=h(tr('subject'))?><input name="subject" required></label><label><?=h(tr('japanese'))?><textarea name="summary_ja" rows="6"></textarea></label><label><?=h(tr('english'))?><textarea name="summary_en" rows="4"></textarea></label><label><?=h(tr('polish'))?><textarea name="summary_pl" rows="4"></textarea></label><label><?=h(tr('next_action'))?><input name="next_action"></label><label><?=h(tr('due'))?><input type="date" name="next_action_date"></label><input type="hidden" name="status" value="open"><button><?=h(tr('save'))?></button></form></section><section class="card"><h2><?=h(tr('history'))?></h2><?php foreach(db()->query("SELECT a.*,c.name company,ct.name contact FROM activities a LEFT JOIN companies c ON c.id=a.company_id LEFT JOIN contacts ct ON ct.id=a.contact_id ORDER BY activity_date DESC,id DESC") as $r):?><article class="activity"><div class="date"><?=h($r['activity_date'])?></div><div><b><?=h($r['company'])?> / <?=h($r['contact'])?></b><span class="owner-badge"><?=h(tr('owner'))?>: <?=h($r['owner_name']?:'Taiju')?></span><h3><?=h(legacy_translation($r['subject']))?></h3><p><?=nl2br(h(localized($r,'summary')))?></p><?php if($r['summary_en']):?><details><summary>English</summary><p><?=nl2br(h($r['summary_en']))?></p></details><?php endif;?><?php if($r['summary_pl']):?><details><summary>Polski</summary><p><?=nl2br(h($r['summary_pl']))?></p></details><?php endif;?><small>Next: <?=h($r['next_action_date'])?> <?=h(localized($r,'next_action'))?></small></div></article><?php endforeach;?></section></div><?php }
elseif($page==='companies'){ header_html(tr('companies'),$u); ?><?php render_cards($u); ?><div class="grid2"><section class="card"><h2><?=h(tr('company_list'))?></h2><?php foreach(db()->query('SELECT c.*,COUNT(ct.id) contacts FROM companies c LEFT JOIN contacts ct ON ct.company_id=c.id GROUP BY c.id ORDER BY c.name') as $r):?><div class="row"><div><b><?=h($r['name'])?></b><small><?=h($r['country'])?> / contacts: <?=$r['contacts']?></small></div></div><?php endforeach;?></section><section class="card"><h2><?=h(tr('add_company'))?></h2><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_company"><label><?=h(tr('company_name'))?><input name="name" required></label><label><?=h(tr('country'))?><input name="country"></label><label><?=h(tr('url'))?><input name="website"></label><label><?=h(tr('memo'))?><textarea name="notes"></textarea></label><button><?=h(tr('add'))?></button></form></section></div><?php }
elseif($page==='products'){ header_html(tr('products'),$u); ?><section class="card"><p><?=h(tr('sds_desc'))?></p><div class="products"><?php foreach(db()->query('SELECT * FROM products ORDER BY name') as $r):?><div class="product"><b><?=h($r['name'])?></b><small><?=h($r['code'])?> / <?=h($r['category'])?></small><p><?=h(localized($r,'summary'))?></p></div><?php endforeach;?></div><h2><?=h(tr('sds_files'))?></h2><div class="sds-list"><?php $files=glob(__DIR__.'/../sds/*.{html,pdf}',GLOB_BRACE)?:[]; sort($files); foreach($files as $f): $n=basename($f);?><a href="../sds/<?=rawurlencode($n)?>" target="_blank"><?=h($n)?></a><?php endforeach;?></div></section><?php }

elseif($page==='matrix'){ header_html(tr('matrix'),$u); ?>
<section class="card">
  <div class="matrix-head"><div><h2><?=h(tr('product_axis'))?></h2><p><?=h(tr('product_axis_desc'))?></p></div></div>
  <div class="table-scroll"><table class="matrix"><thead><tr><th><?=h(tr('product'))?></th><th><?=h(tr('company'))?></th><th><?=h(tr('stage'))?></th><th><?=h(tr('current'))?></th><th><?=h(tr('next_action'))?></th><th><?=h(tr('owner'))?></th></tr></thead><tbody>
  <?php
  $q=db()->query("SELECT p.name product,p.code,c.name company,s.stage,s.stage_en,s.stage_pl,s.summary,s.summary_en,s.summary_pl,s.next_action,s.next_action_en,s.next_action_pl,s.owner_name
      FROM company_product_status s JOIN products p ON p.id=s.product_id JOIN companies c ON c.id=s.company_id
      ORDER BY p.name,c.name");
  foreach($q as $r): ?>
    <tr><td><b><?=h($r['product'])?></b><small><?=h($r['code'])?></small></td><td><?=h($r['company'])?></td><td><span class="stage"><?=h(localized($r,'stage'))?></span></td><td><?=h(localized($r,'summary'))?></td><td><?=h(localized($r,'next_action'))?></td><td><span class="owner-badge"><?=h($r['owner_name']?:'Taiju')?></span></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
<section class="card">
  <div class="matrix-head"><div><h2><?=h(tr('company_axis'))?></h2><p><?=h(tr('company_axis_desc'))?></p></div></div>
  <div class="table-scroll"><table class="matrix"><thead><tr><th><?=h(tr('company'))?></th><th><?=h(tr('product'))?></th><th><?=h(tr('stage'))?></th><th><?=h(tr('current'))?></th><th><?=h(tr('next_action'))?></th><th><?=h(tr('owner'))?></th></tr></thead><tbody>
  <?php
  $q=db()->query("SELECT c.name company,p.name product,p.code,s.stage,s.stage_en,s.stage_pl,s.summary,s.summary_en,s.summary_pl,s.next_action,s.next_action_en,s.next_action_pl,s.owner_name
      FROM company_product_status s JOIN products p ON p.id=s.product_id JOIN companies c ON c.id=s.company_id
      ORDER BY c.name,p.name");
  foreach($q as $r): ?>
    <tr><td><b><?=h($r['company'])?></b></td><td><?=h($r['product'])?><small><?=h($r['code'])?></small></td><td><span class="stage"><?=h(localized($r,'stage'))?></span></td><td><?=h(localized($r,'summary'))?></td><td><?=h(localized($r,'next_action'))?></td><td><span class="owner-badge"><?=h($r['owner_name']?:'Taiju')?></span></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
<?php }

elseif($page==='tests'){ header_html(tr('tests'),$u); ?><section class="card"><?php foreach(db()->query("SELECT t.*,c.name company,p.name product FROM tests t LEFT JOIN companies c ON c.id=t.company_id LEFT JOIN products p ON p.id=t.product_id ORDER BY COALESCE(test_date,'9999-12-31')") as $r):?><article class="activity"><div class="date"><?=h($r['test_date'])?></div><div><b><?=h(legacy_translation($r['title']))?></b><p><?=h($r['company'])?> / <?=h(legacy_translation($r['site']))?> / <?=h($r['product'])?></p><small>Status: <?=h($r['status'])?></small></div></article><?php endforeach;?></section><?php }
elseif($page==='users'){ if($u['role']!=='admin') exit('Forbidden'); header_html(tr('users'),$u); ?><div class="grid2"><section class="card"><h2>Users</h2><?php foreach(db()->query('SELECT id,username,display_name,role,must_change_password,last_login_at FROM users ORDER BY id') as $r):?><div class="row"><div><b><?=h($r['display_name'])?> (<?=h($r['username'])?>)</b><small><?=h($r['role'])?> / <?= $r['must_change_password']?'初回変更待ち':'active' ?> / last: <?=h($r['last_login_at'])?></small></div></div><?php endforeach;?></section><section class="card"><h2>ユーザー追加</h2><?php if(!empty($notice)):?><p class="ok"><?=h($notice)?></p><?php endif;?><?php if(!empty($error)):?><p class="error"><?=h($error)?></p><?php endif;?><p>追加ユーザーの初期パスワードは <b>0921</b>。初回ログイン時に変更必須です。</p><form method="post" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="add_user"><label>表示名<input name="display_name" required></label><label><?=h(tr('username'))?><input name="username" required></label><label>権限<select name="role"><option value="user">user</option><option value="admin">admin</option></select></label><button><?=h(tr('add'))?></button></form></section></div>
<section class="card"><h2><?=h(tr('reset_password'))?></h2><p><?=h(tr('reset_help'))?></p>
<form method="post" class="form" action="?page=users">
<input type="hidden" name="csrf" value="<?=h(csrf())?>">
<input type="hidden" name="action" value="reset_password">
<label><?=h(tr('username'))?><select name="target_id" required><option value="">--</option>
<?php foreach(db()->query('SELECT id,username,display_name FROM users ORDER BY username') as $target): if((int)$target['id']===(int)$u['id']) continue; ?>
<option value="<?=(int)$target['id']?>"><?=h($target['display_name'])?> (<?=h($target['username'])?>)</option>
<?php endforeach;?></select></label>
<label><?=h(tr('temporary_password'))?><input type="password" name="temporary_password" autocomplete="new-password" minlength="12" maxlength="72" required></label>
<label><?=h(tr('admin_password'))?><input type="password" name="admin_password" autocomplete="current-password" required></label>
<button><?=h(tr('reset_password'))?></button>
</form></section><?php }
footer_html();

