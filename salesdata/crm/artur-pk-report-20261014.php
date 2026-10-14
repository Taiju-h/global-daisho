<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }

// Artur's meeting account, received 2026-10-14; date follows the existing PK meeting record.
function import_artur_pk_report_20261014(PDO $db): void {
    $r=json_decode(<<<'REPORT_JSON'
{
  "key": "artur-pk-meeting-report-20261014",
  "date": "2026-10-14",
  "company": "Politechnika Krakowska",
  "subject": "Politechnika Krakowska — prezentacja Daisho i możliwości współpracy badawczej",
  "owner": "Artur",
  "pl": "Uczestniczyliśmy w spotkaniu na Politechnice Krakowskiej, w którym brało udział wielu profesorów i doktorantów. Artur przedstawił prezentację o Daisho, dotychczasowych dokonaniach firmy, planach dalszych działań i możliwościach współpracy z uczelnią. Zaprezentował trzy główne produkty Daisho.\nSpotkanie było krótkie. Przedstawiciele uczelni poprosili o więcej danych i raportów dotyczących firmy oraz jej produktów. Zapowiedzieli powrót do rozmów i wspólnych prac badawczych.\nRaport sporządzony na podstawie relacji Artura.",
  "ja": "クラコフ工科大学（Politechnika Krakowska）での打ち合わせに参加した。多くの教授と博士課程の学生が出席した。ArturがDaishoの会社紹介、これまでの実績、今後の取り組み、大学との協力の可能性についてプレゼンテーションを行い、Daishoの主要3製品を紹介した。\n打ち合わせは短時間だった。大学側は会社および製品について、より詳しいデータとレポートの提供を求めた。今後、協議と共同研究に向けた話し合いを再開する意向を示した。\nArturの説明に基づく報告。",
  "en": "We attended a meeting at Cracow University of Technology (Politechnika Krakowska), with many professors and doctoral students present. Artur delivered a presentation about Daisho, the company's achievements, plans and opportunities for cooperation with the university. He introduced Daisho's three main products.\nThe meeting was brief. University representatives requested further data and reports about the company and its products. They indicated that they would return to discussions and joint research work.\nReport based on Artur's account.",
  "next_pl": "Przygotować i przekazać uczelni dodatkowe dane oraz raporty dotyczące Daisho i produktów, a następnie wrócić do rozmów o wspólnych pracach badawczych.",
  "next_ja": "Daishoと製品に関する追加データおよびレポートを準備して大学へ提供し、その後、共同研究についての協議を再開する。",
  "next_en": "Prepare and provide additional data and reports about Daisho and its products, then resume discussions about joint research."
}
REPORT_JSON
    ,true,32,JSON_THROW_ON_ERROR);
    $q=$db->prepare("SELECT id FROM users WHERE username=?");
    $q->execute(['Artur']); $uid=$q->fetchColumn();
    if(!$uid) { error_log('PK meeting report pending: Artur CRM account not found.'); return; }
    $db->beginTransaction();
    try {
        $q=$db->prepare('INSERT IGNORE INTO crm_seed_batches(seed_key) VALUES(?)');
        $q->execute([$r['key']]);
        if(!$q->rowCount()) { $db->commit(); return; }
        $q=$db->prepare('SELECT id FROM companies WHERE name IN (?,?,?) ORDER BY id');
        $q->execute([$r['company'],'クラコフ工科大学','Cracow University of Technology']);
        $ids=$q->fetchAll(PDO::FETCH_COLUMN);
        if(count($ids)>1) throw new RuntimeException('Ambiguous university company records.');
        if($ids) $cid=(int)$ids[0];
        else {
            $db->prepare('INSERT IGNORE INTO companies(name,country) VALUES(?,?)')->execute([$r['company'],'Poland']);
            $q=$db->prepare('SELECT id FROM companies WHERE name=?'); $q->execute([$r['company']]); $cid=(int)$q->fetchColumn();
        }
        $q=$db->prepare('SELECT id FROM activities WHERE activity_date=? AND company_id=? AND subject=?');
        $q->execute([$r['date'],$cid,$r['subject']]);
        if(!$q->fetchColumn()) {
            $db->prepare("INSERT INTO activities(activity_date,company_id,activity_type,subject,summary_ja,summary_en,summary_pl,next_action,next_action_en,next_action_pl,status,created_by,owner_name) VALUES(?,?,'meeting',?,?,?,?,?,?,?,'open',?,?)")
                ->execute([$r['date'],$cid,$r['subject'],$r['ja'],$r['en'],$r['pl'],$r['next_ja'],$r['next_en'],$r['next_pl'],(int)$uid,$r['owner']]);
        }
        $db->commit();
    } catch(Throwable $e) {
        if($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}
