<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }

function migrate_sheet_tests(PDO $db): void {
    // The same SQL file is available to the server update EXEC.
    $sql=file_get_contents(dirname(__DIR__,2).'/.deploy/crm-sql/0005_sheet_tests.ddl.sql');
    if($sql===false) throw new RuntimeException('Experiment schema SQL is missing.');
    $db->exec($sql);
    $records=require __DIR__.'/tests-sheet-data.php';
    $db->beginTransaction();
    try {
        $claim=$db->prepare('INSERT IGNORE INTO crm_sheet_tests(source_key,experiment_number,source_snapshot) VALUES(?,?,?)');
        foreach($records as $r) {
            $claim->execute([$r['source_key'],$r['number'],json_encode($r,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
            if(!$claim->rowCount()) continue;
            $cid=null; $pid=null;
            if($r['company']) {
                // Only genuine company labels are mapped; e.g. "waterproofing test" is not a company.
                $aliases=$r['company']==='ディケイコム'?['ディケイコム','DKコム','株式会社ディケイコム']:[$r['company']];
                $find=$db->prepare('SELECT id FROM companies WHERE name IN ('.implode(',',array_fill(0,count($aliases),'?')).') ORDER BY id'); $find->execute($aliases); $ids=$find->fetchAll(PDO::FETCH_COLUMN);
                if(count($ids)>1) throw new RuntimeException('Ambiguous experiment company: '.$r['company']);
                if($ids) $cid=(int)$ids[0];
                else { $db->prepare('INSERT IGNORE INTO companies(name) VALUES(?)')->execute([$r['company']]); $find=$db->prepare('SELECT id FROM companies WHERE name=?'); $find->execute([$r['company']]); $cid=(int)$find->fetchColumn(); }
            }
            if($r['product_code']) {
                $find=$db->prepare('SELECT id FROM products WHERE code=?'); $find->execute([$r['product_code']]); $pid=$find->fetchColumn();
                if(!$pid) throw new RuntimeException('Experiment product code is missing: '.$r['product_code']);
            }
            // No inferred DT/DEEPER relation: link only product names explicit in this sheet.
            $date=$r['dates']['performed']['iso'];
            $find=$db->prepare('SELECT t.id FROM tests t WHERE t.title=? AND t.test_date <=> ? AND t.company_id <=> ? AND NOT EXISTS (SELECT 1 FROM crm_sheet_tests s WHERE s.test_id=t.id) ORDER BY t.id');
            $find->execute([$r['title'],$date,$cid]); $ids=$find->fetchAll(PDO::FETCH_COLUMN);
            if(count($ids)>1) throw new RuntimeException('Ambiguous existing experiment: '.$r['number']);
            if($ids) {
                $tid=(int)$ids[0];
                // A previously edited record is never overwritten by opening a page.
                $db->prepare("UPDATE tests SET purpose=CASE WHEN purpose IS NULL OR purpose='' THEN ? ELSE purpose END,result=CASE WHEN result IS NULL OR result='' THEN ? ELSE result END,product_id=COALESCE(product_id,?) WHERE id=?")->execute([$r['purpose'],$r['result'],$pid,$tid]);
            } else {
                $db->prepare('INSERT INTO tests(test_date,company_id,product_id,site,title,purpose,result,status,owner_name) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$date,$cid,$pid,$r['site'],$r['title'],$r['purpose'],$r['result'],$r['status'],'Google Sheets import']);
                $tid=(int)$db->lastInsertId();
            }
            $db->prepare('UPDATE crm_sheet_tests SET test_id=? WHERE source_key=?')->execute([$tid,$r['source_key']]);
        }
        $db->commit();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}

function sheet_test_text(array $source,string $field,?string $current=null): string {
    $original=(string)($source[$field]??'');
    if($current!==null && $current!==$original) return $current;
    return (string)($source['translations'][current_lang()][$field]??$original);
}
function sheet_test_status(string $status): string {
    return match($status) {
        'recorded'=>ux('実施日の記録あり','Test date recorded','Zapisano datę badania'),
        'unconfirmed'=>ux('実施日未記載','Test date not recorded','Brak daty badania'),
        'planned'=>ux('予定','Planned','Planowane'),
        'done'=>ux('完了','Completed','Zakończone'),
        default=>$status,
    };
}
function render_sheet_tests(): void {
    $sourceUrl='https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730';
    ?><section class="card"><p><a href="<?=h($sourceUrl)?>" target="_blank" rel="noopener noreferrer"><?=h(ux('元データ：実験管理 → 実験','Source: Experiment management → Experiments','Źródło: Zarządzanie badaniami → Badania'))?> ↗</a></p><p><?=h(ux('2026年10月10日確認のスプレッドシート記録です。結果・期限が空欄のものは補完していません。自動同期ではありません。','Spreadsheet records checked on 10 October 2026. Missing results and deadlines remain blank. This is not a live sync.','Dane z arkusza sprawdzone 10 października 2026. Brakujących wyników i terminów nie uzupełniono. To nie jest synchronizacja na żywo.'))?></p></section>
    <?php $q=db()->query('SELECT t.*,c.name company,p.name product,s.experiment_number,s.source_snapshot FROM tests t LEFT JOIN companies c ON c.id=t.company_id LEFT JOIN products p ON p.id=t.product_id LEFT JOIN crm_sheet_tests s ON s.test_id=t.id ORDER BY s.experiment_number IS NULL,s.experiment_number,COALESCE(t.test_date,\'9999-12-31\'),t.id');
    foreach($q as $t): $source=$t['source_snapshot']?json_decode($t['source_snapshot'],true,512,JSON_THROW_ON_ERROR):[]; ?>
    <article class="card" id="test-<?=(int)$t['id']?>"><h2><?php if($t['experiment_number']): ?>#<?=(int)$t['experiment_number']?> · <?php endif; ?><?=h($source?sheet_test_text($source,'title',$t['title']):legacy_translation($t['title']))?></h2>
    <p><?=h($source?($source['client_original']?:ux('クライアント未記載','Client not specified','Nie podano klienta')):($t['company']??''))?><?php if($t['product_id']): ?> · <a href="?page=product&amp;id=<?=(int)$t['product_id']?>"><?=h($t['product'])?></a><?php endif; ?></p>
    <p><?=h($t['test_date']?:ux('実施日未記載','Test date not recorded','Brak daty badania'))?> · <?=h(sheet_test_status($t['status']))?></p>
    <?php if($t['site']): ?><p><?=h($t['site'])?></p><?php endif; ?>
    <h3><?=h(ux('目的','Purpose','Cel'))?></h3><p><?=nl2br(h(($source?sheet_test_text($source,'purpose',$t['purpose']):$t['purpose'])?:ux('未記載','Not recorded','Nie podano')))?></p>
    <h3><?=h(ux('結果概要','Results summary','Podsumowanie wyników'))?></h3><p><?=nl2br(h(($source?sheet_test_text($source,'result',$t['result']):$t['result'])?:ux('結果未記載','Results not recorded','Brak wyników')))?></p>
    <?php if($source): ?>
    <?php if($source['notes']!==''): ?><h3><?=h(ux('備考・条件','Notes and conditions','Uwagi i warunki'))?></h3><p><?=nl2br(h(sheet_test_text($source,'notes')))?></p><?php endif; ?>
    <details><summary><?=h(ux('日程・原文を確認','Dates and original text','Daty i tekst oryginalny'))?></summary><dl>
    <?php foreach(['due'=>ux('納期','Due date','Termin'),'requested'=>ux('依頼日','Requested','Data zlecenia'),'planned'=>ux('予定日','Planned date','Planowana data'),'performed'=>ux('実験日','Test date','Data badania'),'followup'=>ux('工事後の確認','Post-construction follow-up','Kontrola po wykonaniu')] as $key=>$label): $date=$source['dates'][$key]; ?><dt><?=h($label)?></dt><dd><?=h($date['iso']?:($date['display']?:ux('未記載','Not recorded','Nie podano')))?></dd><?php endforeach; ?></dl><p><?=nl2br(h(implode("\n",[$source['title'],$source['purpose'],$source['notes'],$source['result']])) )?></p></details>
    <?php if($source['media']): ?><details><summary><?=h(ux('写真・動画の記録','Photo/video records','Wykaz zdjęć i filmów'))?>（<?=count($source['media'])?>）</summary><p><?=h(ux('ファイル名の記録です。リンクは元シートの該当行を開きます。画像・動画本体の直リンクはシートに記載されていません。','These are recorded filenames. Links open the source sheet row; the sheet does not provide direct media URLs.','To zapisane nazwy plików. Linki otwierają wiersz arkusza; arkusz nie zawiera bezpośrednich adresów plików.'))?></p><ul><?php foreach($source['media'] as $media): ?><li><?=h($media['date'])?> · <a href="<?=h($media['source_url'])?>" target="_blank" rel="noopener noreferrer"><?=h($media['filename'])?></a></li><?php endforeach; ?></ul></details><?php endif; ?>
    <p><a href="<?=h($source['source_url'])?>" target="_blank" rel="noopener noreferrer"><?=h(ux('元の実験記録を開く','Open the source record','Otwórz zapis źródłowy'))?> ↗</a></p>
    <?php endif; if($t['next_step']): ?><p><?=h(tr('next_action'))?>: <?=nl2br(h($t['next_step']))?></p><?php endif; ?></article>
    <?php endforeach;
}
