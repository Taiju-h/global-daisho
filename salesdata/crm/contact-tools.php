<?php
declare(strict_types=1);
// Loaded only by the authenticated CRM entry point; direct requests expose nothing.
if (!defined('APP_NAME')) { http_response_code(404); exit; }

function card_text(string $key): string {
    $labels=[
        'cards'=>['名刺','Business Cards','Wizytówki'],
        'recent_views'=>['最近見た名刺','Recently viewed','Ostatnio oglądane'],
        'recent_scans'=>['最近スキャン・受領した名刺','Recently scanned / received','Ostatnio zeskanowane / otrzymane'],
        'all'=>['全ての名刺・連絡先','All cards and contacts','Wszystkie wizytówki i kontakty'],
        'open'=>['名刺を開く','Open card','Otwórz wizytówkę'],
        'empty'=>['まだありません','None yet','Brak'],
        'search'=>['氏名・会社・メールで検索','Search name, company or email','Szukaj nazwiska, firmy lub e-maila'],
        'source'=>['出典','Source','Źródło'],
        'received'=>['受領日','Received','Otrzymano'],
        'scanned'=>['スキャン日','Scanned','Zeskanowano'],
        'date_note'=>['スキャン日不明の名刺は元データの受領日順です。直近30日から最大6件を表示します。','When the scan date is unknown, the original receipt date is used. Up to six cards from the last 30 days are shown.','Gdy data skanowania jest nieznana, używana jest data otrzymania. Do sześciu wizytówek z ostatnich 30 dni.'],
        'back'=>['名刺一覧へ戻る','Back to cards','Wróć do wizytówek'],
        'completed'=>['完了済みアクション','Completed actions','Zakończone działania'],
    ];
    return $labels[$key][array_search(current_lang(),['ja','en','pl'],true)]??$key;
}

function migrate_card_tools(PDO $db): void {
    foreach(['mobile','fax','address','card_source'] as $column) ensure_column($db,'contacts',$column,'TEXT NULL');
    ensure_column($db,'contacts','card_received_on','DATE NULL');
    ensure_column($db,'contacts','card_scanned_on','DATE NULL');
    $db->exec("CREATE TABLE IF NOT EXISTS crm_card_imports (source_key VARCHAR(191) PRIMARY KEY, contact_id BIGINT UNSIGNED NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS contact_views (user_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NOT NULL, viewed_at DATETIME NOT NULL, PRIMARY KEY(user_id,contact_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $records=reviewed_cards();
    $claim=$db->prepare('INSERT IGNORE INTO crm_card_imports(source_key) VALUES(?)');
    $findCompany=$db->prepare('SELECT id FROM companies WHERE name=?');
    $insertCompany=$db->prepare('INSERT IGNORE INTO companies(name,country,website) VALUES(?,?,?)');
    $findContact=$db->prepare("SELECT id FROM contacts WHERE company_id=? AND (email=? OR REPLACE(name,' ','')=? OR name=?) ORDER BY id LIMIT 1 FOR UPDATE");
    $insertContact=$db->prepare('INSERT INTO contacts(company_id,name,title,email,phone,notes,mobile,fax,address,card_source,card_received_on) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $db->beginTransaction();
    try {
        foreach($records as $r) {
            $key='cards-20261010:'.strtolower($r['email']);
            $claim->execute([$key]);
            if(!$claim->rowCount()) continue;
            $insertCompany->execute([$r['company'],$r['country'],$r['website']]);
            $findCompany->execute([$r['company']]); $cid=(int)$findCompany->fetchColumn();
            $alias=$r['email']==='yanais@kajima.com'?'柳井':$r['name'];
            $findContact->execute([$cid,$r['email'],str_replace(' ','',$r['name']),$alias]); $id=$findContact->fetchColumn();
            if(!$id) {
                $insertContact->execute([$cid,$r['name'],$r['title'],$r['email'],$r['phone'],$r['notes'],$r['mobile'],$r['fax'],$r['address'],$r['source'],$r['received']]);
                $id=(int)$db->lastInsertId();
            } else {
                // Fill missing fields only; never overwrite later user edits.
                foreach(['title','email','phone','notes','mobile','fax','address','card_source','card_received_on'] as $field) {
                    $source=['card_source'=>'source','card_received_on'=>'received'][$field]??$field;
                    $db->prepare("UPDATE contacts SET `$field`=? WHERE id=? AND (`$field` IS NULL OR CAST(`$field` AS CHAR)='')")->execute([$r[$source],$id]);
                }
                if($alias==='柳井') $db->prepare("UPDATE contacts SET name=? WHERE id=? AND name='柳井'")->execute([$r['name'],$id]);
            }
            $db->prepare('UPDATE crm_card_imports SET contact_id=? WHERE source_key=?')->execute([$id,$key]);
        }
        $db->commit();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
    import_contract_actions($db);
}

function render_contact_card(array $ct, bool $full=false): void { ?>
<article class="contact-card">
<div class="contact-company"><?=h($ct['company'])?></div>
<strong class="contact-name"><?=h($ct['name'])?></strong><div><?=h(legacy_translation($ct['title']))?></div>
<?php foreach(['email'=>'mailto:','mobile'=>'tel:','phone'=>'tel:'] as $field=>$scheme): if(!empty($ct[$field])): ?>
<div><a href="<?=h($scheme.$ct[$field])?>"><?=h($ct[$field])?></a></div>
<?php endif; endforeach; ?>
<?php if($full): ?>
<?php if(!empty($ct['fax'])): ?><p>FAX: <?=h($ct['fax'])?></p><?php endif; ?>
<?php if(!empty($ct['address'])): ?><p><?=h($ct['address'])?></p><?php endif; ?>
<?php if(!empty($ct['notes'])): ?><p class="contact-note"><?=nl2br(h(legacy_translation($ct['notes'])))?></p><?php endif; ?>
<?php if(!empty($ct['card_source'])): ?><small><?=h(card_text('source'))?>: <?=h($ct['card_source'])?></small><?php endif; ?>
<?php endif; ?>
<?php if(!empty($ct['card_scanned_on'])): ?><small><?=h(card_text('scanned'))?>: <?=h($ct['card_scanned_on'])?></small>
<?php elseif(!empty($ct['card_received_on'])): ?><small><?=h(card_text('received'))?>: <?=h($ct['card_received_on'])?></small><?php endif; ?>
<?php if(!$full): ?><p><a href="?page=companies&amp;card=<?=(int)$ct['id']?>"><?=h(card_text('open'))?></a></p><?php endif; ?>
</article>
<?php }

function render_cards(array $user): void {
    $db=db();
    $id=(int)($_GET['card']??0);
    if($id>0) {
        $q=$db->prepare('SELECT ct.*,co.name company FROM contacts ct LEFT JOIN companies co ON co.id=ct.company_id WHERE ct.id=?'); $q->execute([$id]); $ct=$q->fetch();
        if($ct) {
            // Only opening a detail counts as viewing; listing/importing never changes recency.
            $db->prepare('INSERT INTO contact_views(user_id,contact_id,viewed_at) VALUES(?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE viewed_at=UTC_TIMESTAMP()')->execute([$user['id'],$id]);
            echo '<section class="card"><a href="?page=companies">'.h(card_text('back')).'</a>';
            render_contact_card($ct,true); echo '</section>';
        }
    }
    $q=trim((string)($_GET['q']??'')); ?>
<section class="card"><h2><?=h(card_text('cards'))?></h2>
<form method="get"><input type="hidden" name="page" value="companies"><label><?=h(card_text('search'))?><input type="search" name="q" value="<?=h($q)?>"></label><button><?=h(card_text('search'))?></button></form></section>
<?php if($q!=='') {
        $s=$db->prepare("SELECT ct.*,co.name company FROM contacts ct LEFT JOIN companies co ON co.id=ct.company_id WHERE ct.name LIKE ? OR co.name LIKE ? OR ct.email LIKE ? ORDER BY co.name,ct.name");
        $s->execute(array_fill(0,3,'%'.$q.'%')); $rows=$s->fetchAll();
        echo '<section class="card"><div class="contact-cards">';
        foreach($rows as $ct) render_contact_card($ct);
        if(!$rows) echo '<p>'.h(card_text('empty')).'</p>';
        echo '</div></section>';
    } else {
        $s=$db->prepare('SELECT ct.*,co.name company FROM contact_views v JOIN contacts ct ON ct.id=v.contact_id LEFT JOIN companies co ON co.id=ct.company_id WHERE v.user_id=? ORDER BY v.viewed_at DESC,ct.id DESC LIMIT 6'); $s->execute([$user['id']]);
        $recent=$s->fetchAll();
        $scans=$db->query("SELECT ct.*,co.name company FROM contacts ct LEFT JOIN companies co ON co.id=ct.company_id WHERE COALESCE(ct.card_scanned_on,ct.card_received_on)>=DATE_SUB(DATE(DATE_ADD(UTC_TIMESTAMP(), INTERVAL 9 HOUR)), INTERVAL 30 DAY) ORDER BY COALESCE(ct.card_scanned_on,ct.card_received_on) DESC,ct.id DESC LIMIT 6")->fetchAll();
        foreach(['recent_views'=>$recent,'recent_scans'=>$scans] as $label=>$rows) {
            echo '<section class="card"><h2>'.h(card_text($label)).'</h2>';
            if($label==='recent_scans') echo '<p>'.h(card_text('date_note')).'</p>';
            echo '<div class="contact-cards">';
            foreach($rows as $ct) render_contact_card($ct);
            if(!$rows) echo '<p>'.h(card_text('empty')).'</p>';
            echo '</div></section>';
        }
    }
    $groups=[];
    foreach($db->query('SELECT ct.*,co.name company FROM contacts ct LEFT JOIN companies co ON co.id=ct.company_id ORDER BY co.name,ct.name') as $ct) $groups[$ct['company']??'—'][]=$ct;
    ?><section class="card"><details><summary>📁 <?=h(card_text('all'))?> (<?=array_sum(array_map('count',$groups))?>)</summary>
    <?php foreach($groups as $company=>$rows): ?><details><summary>📁 <?=h($company)?> (<?=count($rows)?>)</summary><div class="contact-cards"><?php foreach($rows as $ct) render_contact_card($ct); ?></div></details><?php endforeach; ?>
    </details></section><?php
}

function import_contract_actions(PDO $db): void {
    $records=[
        ['english-created','done','英語版の販売委託契約書を作成済み','English sales consignment agreement prepared','Przygotowano angielską umowę sprzedaży komisowej','2026-10-07（日本時間）に Sales_Consignment_Agreement_EN.docx を作成済み。全13条。相手方 Maeda and Leonardo Trading CO Inc、D Retarder、25kg袋あたり300 PHP。作成済みであり、送付・署名完了を意味しない。','Sales_Consignment_Agreement_EN.docx was created on 7 Oct 2026 JST. All 13 articles; Maeda and Leonardo Trading CO Inc; D Retarder; PHP 300 per 25 kg bag. Preparation does not establish delivery or signature.','Plik Sales_Consignment_Agreement_EN.docx utworzono 7 października 2026 czasu JST. 13 artykułów; Maeda and Leonardo Trading CO Inc; D Retarder; 300 PHP za worek 25 kg. Utworzenie nie potwierdza wysłania ani podpisania.'],
        ['english-delivery','open','泉様に英語契約書の送付状況を確認し、未送付なら送付','Confirm English agreement delivery with Izumi; send if outstanding','Potwierdzić z Izumi wysłanie angielskiej umowy; wysłać, jeśli brak','10/6に泉様が英訳を依頼し、当方は了承。英語版は10/7作成済み。確認したメールでは英語版送付を確認できない。送付状況を確認し、未送付なら作成済みファイルを泉様へ送る。期限未合意。','Izumi requested the English version on 6 Oct; Taiju acknowledged. The file was created on 7 Oct JST. Delivery of the English version is unconfirmed in reviewed emails. Check and send the existing file to Izumi only if outstanding. No agreed deadline.','Izumi poprosił o wersję angielską 6 października; Taiju potwierdził. Plik powstał 7 października JST. W sprawdzonych mailach brak potwierdzenia wysłania wersji angielskiej. Sprawdzić i wysłać istniejący plik do Izumi, jeśli nadal nie wysłano. Brak ustalonego terminu.'],
        ['english-execution','waiting','英語契約書の先方確認・署名済み返送を確認','Confirm counterparty review and return of the signed English agreement','Potwierdzić akceptację i zwrot podpisanej umowy angielskiej','英語版送付後の確認事項。10/6に泉様から先方は契約内容に問題なしと回答したとの連絡あり。これは英語版の署名完了とは別。泉様経由で英語版確認・署名済み返送の状況を確認し、締結版を保管する。期限未合意。','Depends on English-version delivery. On 6 Oct, Izumi reported that the counterparty accepted the contract contents; this does not confirm execution of the English version. Check review and signed-return status through Izumi and retain the executed copy. No agreed deadline.','Po wysłaniu wersji angielskiej. 6 października Izumi poinformował o akceptacji treści umowy; nie potwierdza to podpisania wersji angielskiej. Sprawdzić przez Izumi status akceptacji i zwrotu podpisanego egzemplarza oraz zachować zawartą umowę. Brak uzgodnionego terminu.'],
    ];
    $db->beginTransaction();
    try {
        $claim=$db->prepare('INSERT IGNORE INTO crm_mail_imports(source_key) VALUES(?)');
        foreach($records as $r) {
            $key='contract-maeda-20261007:'.$r[0]; $claim->execute([$key]); if(!$claim->rowCount()) continue;
            $db->prepare("INSERT IGNORE INTO companies(name,country) VALUES(?,'Philippines')")->execute(['Maeda and Leonardo Trading CO Inc']);
            $co=$db->prepare('SELECT id FROM companies WHERE name=?'); $co->execute(['Maeda and Leonardo Trading CO Inc']); $cid=$co->fetchColumn();
            $owner=$db->query("SELECT id FROM users WHERE username='taiju' LIMIT 1")->fetchColumn();
            $db->prepare('INSERT INTO tasks(company_id,title,title_en,title_pl,detail,detail_en,detail_pl,status,owner_name,assigned_to,source_url) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$cid,$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[1],'Taiju',$owner?:null,'https://mail.google.com/mail/u/0/#all/1a10fdaae3a5497e']);
            $id=$db->lastInsertId(); $db->prepare('UPDATE crm_mail_imports SET task_id=? WHERE source_key=?')->execute([$id,$key]);
        }
        $db->commit();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}

function render_completed_actions(): void { ?>
<section class="card"><details><summary><?=h(card_text('completed'))?></summary>
<?php foreach(db()->query("SELECT t.*,c.name company FROM tasks t LEFT JOIN companies c ON c.id=t.company_id WHERE t.status='done' ORDER BY t.id DESC LIMIT 30") as $r): ?>
<div class="row"><div><b><?=h(localized($r,'title'))?></b><small><?=h($r['company'])?> / <?=h(localized($r,'detail'))?></small></div></div>
<?php endforeach; ?></details></section><?php }

function reviewed_cards(): array {
    return json_decode(<<<'CARDS_JSON'
[
  {
    "company": "STOCKMEIER Chemia Sp. z o.o.",
    "country": "Poland",
    "name": "Zmysłowski Jakub",
    "title": "Product Manager - Organics",
    "email": "j.zmyslowski@stockmeier.pl",
    "mobile": "+48 609 333 352",
    "phone": "+48 61 666 10 66",
    "fax": "",
    "address": "ul. Obornicka 277, 60-691 Poznań, Poland",
    "website": "https://pl.stockmeier.com",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 01"
  },
  {
    "company": "ジャパンパイル株式会社",
    "country": "Japan",
    "name": "今 広人 / Hirohito Kon",
    "title": "施工技術開発部長・博士（工学）",
    "email": "hirohito_kon@japanpile.co.jp",
    "mobile": "",
    "phone": "03-5843-4196",
    "fax": "03-5651-1905",
    "address": "東京都中央区日本橋箱崎町36-2 Daiwaリバーゲート",
    "website": "https://www.japanpile.co.jp/",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 02"
  },
  {
    "company": "SOLENIS POLAND Sp. z o.o.",
    "country": "Poland",
    "name": "Jakub Kochanowski",
    "title": "Area Manager Poland/Baltics/Ukraine",
    "email": "jkochanowski@solenis.com",
    "mobile": "+48 601 155 186",
    "phone": "",
    "fax": "",
    "address": "ul. Giełdowa 1, 01-211 Warszawa, Poland",
    "website": "",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 03"
  },
  {
    "company": "清水建設株式会社",
    "country": "Singapore",
    "name": "岡部 真佳 / Masayoshi OKABE",
    "title": "シンガポール土木担当マネージャー",
    "email": "okabe.masayoshi@shimz.biz",
    "mobile": "+65 8139 0546",
    "phone": "+65 6220 0406",
    "fax": "",
    "address": "8 Kallang Avenue #05-05 Aperia Tower 1, Singapore 339509",
    "website": "https://www.shimz.com.sg/",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 04"
  },
  {
    "company": "PGW PAWLAK GEOLOGIA",
    "country": "Poland",
    "name": "Grzegorz Sujka",
    "title": "członek zarządu",
    "email": "grzegorz.sujka@pgwpawlak.pl",
    "mobile": "+48 609 341 748",
    "phone": "",
    "fax": "",
    "address": "Wolbromska 7, 03-680 Warszawa, Poland",
    "website": "",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 05"
  },
  {
    "company": "三京化成株式会社",
    "country": "Japan",
    "name": "阿部 兼希 / Kazuki ABE",
    "title": "東京支社 営業第一課 課長",
    "email": "k-abe@sankyokasei-corp.co.jp",
    "mobile": "",
    "phone": "03-6222-7121",
    "fax": "03-6222-7180",
    "address": "東京都中央区新川1丁目23番5号 ONE SHINKAWA 7F",
    "website": "",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 06"
  },
  {
    "company": "Keller Polska sp. z o.o.",
    "country": "Poland",
    "name": "Lech Danieluk",
    "title": "Szczecin Office Director",
    "email": "lech.danieluk@keller.com",
    "mobile": "+48 723 440 174",
    "phone": "",
    "fax": "",
    "address": "ul. Jerzego Janosika 17, 71-424 Szczecin, Poland",
    "website": "https://www.keller.com.pl/",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 07"
  },
  {
    "company": "Tergon Sp. z o.o.",
    "country": "Poland",
    "name": "Konrad Klimkowski",
    "title": "Prezes Zarządu",
    "email": "konrad.klimkowski@tergon.pl",
    "mobile": "+48 690 29 49 39",
    "phone": "",
    "fax": "",
    "address": "本社: os. Na stoku 81/13, 25-437 Kielce; 事務所: ul. Ryżowa 89, Opacz Kolonia 05-816",
    "website": "https://tergon.pl/",
    "notes": "同一名刺の重複1件",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 08, 12"
  },
  {
    "company": "東洋建設株式会社",
    "country": "Japan",
    "name": "曠野 博紀 / Hiroki KOHNO",
    "title": "国際支店 土木技術部 課長",
    "email": "kouno-hiroki@toyo-const.co.jp",
    "mobile": "070-4199-0700",
    "phone": "03-6361-5480",
    "fax": "03-3518-9567",
    "address": "東京都千代田区神田神保町1丁目105番地 神保町三井ビルディング",
    "website": "",
    "notes": "姓の漢字は稀字のため原本で要確認",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 09"
  },
  {
    "company": "Budimex SA / Budimex-Gülermak",
    "country": "Poland",
    "name": "Radosław Czyż",
    "title": "Technolog Rejonu",
    "email": "radoslaw.czyz@budimex.pl",
    "mobile": "+48 515 740 711",
    "phone": "",
    "fax": "",
    "address": "ul. Siedmiogrodzka 9, 01-204 Warszawa, Poland",
    "website": "",
    "notes": "14番は共同事業体表記あり。同一人物・同一連絡先",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 10, 14"
  },
  {
    "company": "Tergon Sp. z o.o.",
    "country": "Poland",
    "name": "Piotr Głowacki",
    "title": "Dyrektor Operacyjny",
    "email": "piotr.glowacki@tergon.pl",
    "mobile": "+48 600 008 720",
    "phone": "",
    "fax": "",
    "address": "本社: os. Na stoku 81/13, 25-437 Kielce; 事務所: ul. Ryżowa 89, Opacz Kolonia 05-816",
    "website": "https://tergon.pl/",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 11"
  },
  {
    "company": "Bags & Fruits",
    "country": "Italy",
    "name": "個人名の記載なし",
    "title": "",
    "email": "info@bagsandfruits.com",
    "mobile": "+39 347 196 7768",
    "phone": "",
    "fax": "",
    "address": "Via dei Giubbonari, 106, 00186 Roma, Italy",
    "website": "https://www.bagsandfruits.com/",
    "notes": "会社の代表連絡先",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 13"
  },
  {
    "company": "鹿島建設株式会社",
    "country": "Japan",
    "name": "柳井 修司 / Shuji YANAI",
    "title": "技術研究所 主席研究員・博士（工学）",
    "email": "yanais@kajima.com",
    "mobile": "090-1053-5666",
    "phone": "",
    "fax": "042-488-3394",
    "address": "東京都調布市飛田給2丁目19-1",
    "website": "",
    "notes": "名刺上の連絡先を記載",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 15"
  },
  {
    "company": "日特建設株式会社",
    "country": "Japan",
    "name": "中野 亮 / Ryo NAKANO",
    "title": "海外事業部 営業部 部長",
    "email": "ryou.nakano@nittoc.co.jp",
    "mobile": "090-7329-5568",
    "phone": "03-5645-5055",
    "fax": "03-5645-5056",
    "address": "東京都中央区東日本橋3丁目10-6 Daiwa東日本橋ビル5階",
    "website": "https://www.nittoc.co.jp/",
    "notes": "",
    "received": "2026-09-25",
    "source": "名刺一覧_2026-09-24.xlsx / 原本 16"
  },
  {
    "company": "林六株式会社",
    "country": "Japan",
    "name": "大和田 忠臣 / Tadaomi OWADA",
    "title": "第II事業本部 課長代理 / Manager, II Dept. (Water & Soil Treatment business)",
    "email": "t.owada@hayashiroku.co.jp",
    "mobile": "080-8546-3107",
    "phone": "03-3256-4937",
    "fax": "03-3252-0168",
    "address": "〒101-0041 東京都千代田区神田須田町2-6 ランディック神田ビル7F",
    "website": "https://www.hayashiroku.co.jp/",
    "notes": "",
    "received": "2026-09-25",
    "source": "(会社名)_上 丁ロ.pdf（両面）"
  },
  {
    "company": "東洋建設株式会社",
    "country": "Japan",
    "name": "常盤 敏 / Satoshi TOKIWA",
    "title": "土木事業本部 土木技術部 専門部長 / Assistant General Manager; 技術士（建設部門）; APECエンジニア",
    "email": "tokiwa-satoshi@toyo-const.co.jp",
    "mobile": "",
    "phone": "03-6361-5464",
    "fax": "03-3518-9479",
    "address": "〒101-0051 東京都千代田区神田神保町一丁目105番地 神保町三井ビルディング",
    "website": "",
    "notes": "",
    "received": "2026-09-25",
    "source": "(会社名)_上下 ロ．.pdf（両面）"
  },
  {
    "company": "Budimex SA / Budimex-Gülermak",
    "country": "Poland",
    "name": "Marcin Curkowicz",
    "title": "Z-ca Kierownika Kontraktu ds. Technicznych i Kontaktu z Mediami",
    "email": "marcin.curkowicz@budimex.pl",
    "mobile": "+48 798 137 747",
    "phone": "",
    "fax": "",
    "address": "ul. Siedmiogrodzka 9, 01-204 Warszawa, Poland",
    "website": "https://www.budimex.pl/",
    "notes": "LK 104 odc. D Limanowa – bocznica Klęczany; Budownictwo Kolejowe DBK – Rejon 2. Two sides show Budimex and Budimex-Gülermak.",
    "received": "2026-09-25",
    "source": "(会社名)_口 ハγ.pdf（両面）"
  },
  {
    "company": "三京化成株式会社",
    "country": "Japan",
    "name": "佐々木 博隆 / Hirotaka SASAKI",
    "title": "SB事業部 ニュービジネス推進G 開発部長",
    "email": "hirotaka-sasaki@sankyokasei-corp.co.jp",
    "mobile": "",
    "phone": "03-6222-7121",
    "fax": "03-6222-7180",
    "address": "〒104-0033 東京都中央区新川1丁目23番5号 ONE SHINKAWA 7F",
    "website": "",
    "notes": "",
    "received": "2026-09-25",
    "source": "(会社名)_山 ？×.pdf（両面）"
  },
  {
    "company": "日特建設株式会社",
    "country": "Japan",
    "name": "阿部 智彦",
    "title": "事業本部 技術開発部 部長",
    "email": "tomohiko.abe@nittoc.co.jp",
    "mobile": "080-1203-4578",
    "phone": "048-766-6066",
    "fax": "048-766-6065",
    "address": "〒349-0134 埼玉県蓮田市大字駒崎1772番地1",
    "website": "",
    "notes": "本店：〒103-0004 東京都中央区東日本橋3-10-6 / 本店電話：03-5645-5110",
    "received": "2026-10-08",
    "source": "名刺追加_日特建設_2026-10-08.md / image-1791424959986.jpg"
  },
  {
    "company": "日特建設株式会社",
    "country": "Japan",
    "name": "竹谷 裕",
    "title": "事業本部 技術開発部 課長",
    "email": "yutaka.taketani@nittoc.co.jp",
    "mobile": "090-3693-9416",
    "phone": "048-766-6066",
    "fax": "048-766-6065",
    "address": "〒349-0134 埼玉県蓮田市大字駒崎1772番地1",
    "website": "",
    "notes": "",
    "received": "2026-10-08",
    "source": "名刺追加_日特建設_2026-10-08.md / image-1791424959986.jpg"
  },
  {
    "company": "Keller Polska sp. z o.o.",
    "country": "Poland",
    "name": "Przemysław Filbrandt",
    "title": "",
    "email": "przemyslaw.filbrandt@keller.com",
    "mobile": "",
    "phone": "",
    "fax": "",
    "address": "",
    "website": "",
    "notes": "氏名・メールは名刺管理トピックのユーザー確認記録に基づく。原本画像・スキャン日未確認。 Name and email confirmed in the business-card conversation; original scan/date not verified.",
    "received": null,
    "source": "名刺管理トピック / 2026-09-25 JST"
  },
  {
    "company": "Keller Polska sp. z o.o.",
    "country": "Poland",
    "name": "Przemysław Wójtowicz",
    "title": "",
    "email": "przemyslaw.wojtowicz@keller.com",
    "mobile": "",
    "phone": "",
    "fax": "",
    "address": "",
    "website": "",
    "notes": "氏名・メールは名刺管理トピックのユーザー確認記録に基づく。原本画像・スキャン日未確認。 Name and email confirmed in the business-card conversation; original scan/date not verified.",
    "received": null,
    "source": "名刺管理トピック / 2026-09-25 JST"
  }
]
CARDS_JSON, true, 512, JSON_THROW_ON_ERROR);
}
