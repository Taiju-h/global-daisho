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

function handle_chatgpt_import(string $action): void {
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

function render_chatgpt_import(array $u): void {
    $products=db()->query('SELECT code,name FROM products ORDER BY name')->fetchAll();
    $example=['records'=>[['date'=>'YYYY-MM-DD','company'=>'','subject'=>'','summary'=>'','type'=>'note','language'=>current_lang(),'product_codes'=>[],'next_action'=>'','due_date'=>'']]];
    $prompt=ux('会話の営業内容を次のJSON形式だけで出力してください。日付・会社・件名・内容は必須。不明な必須項目は私に質問してください。期限や製品は推測しないでください。担当者名はsummaryに含めてください。next_actionは合意済みの未完了事項だけにし、なければ空文字。1〜20件。','Convert our sales discussion into only the following JSON format. Date, company, subject and summary are required; ask me if missing. Do not guess dates or products. Put the contact name in summary. next_action must contain only agreed outstanding actions; otherwise use an empty string. 1–20 records.','Zwróć treść rozmowy sprzedażowej wyłącznie w poniższym formacie JSON. Data, firma, temat i opis są wymagane; zapytaj o brakujące dane. Nie zgaduj terminów ani produktów. Nazwisko kontaktu umieść w summary. next_action zawiera tylko uzgodnione niezakończone działania; w innym przypadku pusty tekst. 1–20 wpisów.');
    $prompt.="\n".json_encode($example,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\nproduct_codes: ".implode(', ',array_column($products,'code'));
    ?><section class="card"><p><?=h(ux('ChatGPTの出力を貼り付け、内容を確認して登録します。会話の自動取得ではありません。','Paste the ChatGPT output, review it and save. This does not automatically fetch your conversations.','Wklej wynik z ChatGPT, sprawdź i zapisz. Rozmowy nie są pobierane automatycznie.'))?></p>
<?php if(isset($_SESSION['chatgpt_import_result'])): [$saved,$skipped]=$_SESSION['chatgpt_import_result']; unset($_SESSION['chatgpt_import_result']); ?><p class="ok"><?=h(ux('登録済み','Saved','Zapisano'))?>: <?=$saved?> / <?=h(ux('登録済みのためスキップ','Already saved; skipped','Już zapisane; pominięto'))?>: <?=$skipped?></p><a href="?page=activities"><?=h(tr('history'))?> ↗</a><?php endif; ?>
<?php if(isset($_SESSION['chatgpt_import_error'])): ?><p class="error"><?=h($_SESSION['chatgpt_import_error'])?></p><?php unset($_SESSION['chatgpt_import_error']); endif; ?>
<details><summary><?=h(ux('ChatGPTに渡す依頼文','Instructions to copy into ChatGPT','Instrukcja do skopiowania do ChatGPT'))?></summary><textarea rows="13" readonly onclick="this.select()"><?=h($prompt)?></textarea></details>
<form method="post" action="?page=chatgpt-import" class="form"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_preview"><label>ChatGPT JSON<textarea name="payload" rows="10" required spellcheck="false" placeholder='{"records":[...]}'><?=h($_SESSION['chatgpt_payload']??'')?></textarea></label><button><?=h(ux('取り込み内容を確認','Preview import','Sprawdź dane'))?></button></form></section>
<?php $draft=$_SESSION['chatgpt_draft']??null; if($draft && $draft['uid']===(int)$u['id'] && time()-$draft['created']<=1800): ?>
<section class="card"><h2><?=h(ux('登録前の確認','Review before saving','Sprawdź przed zapisem'))?></h2><p><?=h(tr('owner'))?>: <?=h($u['display_name'])?></p>
<?php $find=db()->prepare('SELECT id FROM companies WHERE name=?'); foreach($draft['records'] as $r): $find->execute([$r['company']]); $exists=$find->fetchColumn(); ?>
<article class="activity"><div><?=h($r['date'])?></div><div><h3><?=h($r['company'])?> / <?=h($r['subject'])?></h3><?php if(!$exists): ?><small><?=h(ux('会社を新規作成します','A new company will be created','Zostanie utworzona nowa firma'))?></small><?php endif; ?><p><?=nl2br(h($r['summary']))?></p><p><?=h(tr('product'))?>: <?=h(implode(', ',$r['product_codes']))?></p><p><?=h(tr('next_action'))?>: <?=h($r['next_action'])?> / <?=h($r['due_date'])?></p></div></article>
<?php endforeach; ?><p><?=h(ux('次のアクションがある記録は、ダッシュボードにも未完了タスクを作成します。','Records with a next action also create an open dashboard task.','Wpisy z następnym działaniem tworzą też otwarte zadanie w panelu.'))?></p>
<form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_commit"><input type="hidden" name="draft_token" value="<?=h($draft['token'])?>"><button><?=h(ux('この内容で登録する','Save these records','Zapisz te wpisy'))?></button></form>
<form method="post" action="?page=chatgpt-import"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="chatgpt_cancel"><button class="linkbtn"><?=h(ux('取り消す','Cancel','Anuluj'))?></button></form></section>
<?php endif; }
