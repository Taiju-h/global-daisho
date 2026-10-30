<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }
function import_keller_report_20261028(PDO $db): void {
    $r=json_decode(<<<'REPORT_JSON'
{
  "date": "2026-10-28",
  "subject": "Keller訪問：ジェットグラウトへの適用協議",
  "owner": "大樹",
  "ja": "訪問先：Keller。面談相手：プシャメック氏。同席：オルタ氏（正式表記未確認）。訪問日は本人の「今日」という発言と2026年10月28日の会話日付に基づく。写真ファイル名の日付（20261027）には置き換えていない。\n\nジェットグラウトへの適用について協議した。先方から、泥岩層のような硬い地層では切削した土の排泥が上がりにくいという課題が示された。従来はプリカッティングを二回実施している。先方から、地盤工学に基づく考え方として、水と土の混合により比重を段階的に下げて排泥を上がりやすくするとの説明があった。元の土の比重2.2、水1.0として、一回目1.6、二回目1.3になる計算が示された。ただしプシャメック氏はその比重計算自体は重要ではない旨も発言し、比重低下の大きなメリットを理由として二回施工しているという明確な説明はなかった。\n当方から、プリカッティング時にディーパーを使用して硬い地層を柔らかくし、従来二回のプリカッティングを一回に減らし、その後の本施工で排泥を引き上げる方法を提案した。プシャメック氏から、ディーパー使用後に必要な強度が得られるか懸念が示された。\n併せて、高分子系ポリマーでセメントミルクの性状を調整し、排泥の引き上げ性能の向上を図る案を提案した。現時点では提案段階であり、試験実施の合意はない。\n先方から、深度35メートル以上での改良体の直径測定方法と、その精度をどう担保するかについて情報提供を求められた。",
  "en": "Visited Keller. Met with Przemek. Oruta also attended (official spelling unconfirmed). The visit date follows the user's statement “today” in the conversation dated 28 October 2026; it has not been replaced by the photograph filename date, 20261027.\n\nWe discussed application to jet grouting. Keller described difficulty bringing cut soil spoil to the surface in hard strata such as mudstone. Two pre-cutting passes are currently performed. Keller explained a geotechnical concept of progressively reducing the specific gravity by mixing water and soil to facilitate spoil return. A calculation was presented using 2.2 for the original soil and 1.0 for water, giving 1.6 after the first pass and 1.3 after the second. However, Przemek also stated that the specific-gravity calculation itself was not important. There was no clear explanation that the two passes were performed because reducing specific gravity offered a major benefit.\nWe proposed using Deeper during pre-cutting to soften the hard strata, reduce the two pre-cutting passes to one, and then bring the spoil to the surface during the main grouting operation. Przemek raised concern about whether the required strength could be achieved after using Deeper.\nWe also proposed adjusting the properties of the cement slurry with a high-molecular-weight polymer to improve spoil return. This remains a proposal; no agreement to conduct a trial has been reached.\nKeller requested information on how to measure the diameter of treated columns at depths of 35 metres or more, and how to assure measurement accuracy.",
  "pl": "Wizyta w Keller. Rozmówca: Przemek. Uczestniczyła również osoba określona jako Oruta (oficjalna pisownia nazwiska lub imienia niepotwierdzona). Datę wizyty ustalono na podstawie wypowiedzi użytkownika „dzisiaj” w rozmowie z 28 października 2026 r.; nie zastąpiono jej datą 20261027 z nazwy pliku zdjęcia.\n\nOmówiono zastosowanie w jet grouting. Keller wskazał trudności z odprowadzaniem urobku na powierzchnię w twardych warstwach, takich jak mułowiec. Obecnie wykonuje się dwa przejścia wstępnego rozcinania (pre-cutting). Keller przedstawił koncepcję geotechniczną polegającą na stopniowym obniżaniu gęstości względnej przez mieszanie gruntu z wodą, aby ułatwić wynoszenie urobku. Pokazano obliczenie przyjmujące 2,2 dla pierwotnego gruntu i 1,0 dla wody, dające 1,6 po pierwszym przejściu oraz 1,3 po drugim. Przemek zaznaczył jednak, że samo obliczenie gęstości względnej nie jest istotne. Nie wyjaśniono jednoznacznie, że dwa przejścia wykonuje się ze względu na dużą korzyść wynikającą z obniżenia tej gęstości.\nZaproponowaliśmy zastosowanie Deeper podczas wstępnego rozcinania w celu zmiękczenia twardych warstw, ograniczenia dwóch przejść do jednego, a następnie wyniesienia urobku podczas właściwego jet grouting. Przemek wyraził obawę, czy po zastosowaniu Deeper zostanie osiągnięta wymagana wytrzymałość.\nZaproponowaliśmy również modyfikację właściwości zaczynu cementowego polimerem wielkocząsteczkowym w celu poprawy wynoszenia urobku. Jest to etap propozycji; nie uzgodniono przeprowadzenia próby.\nKeller poprosił o informacje o metodach pomiaru średnicy kolumn na głębokości co najmniej 35 metrów oraz o sposobach zapewnienia dokładności pomiaru.",
  "next_ja": "深度35メートル以上での改良体の直径測定方法と、その精度を担保・検証する方法を調査し、資料を提示する。",
  "next_en": "Investigate methods for measuring the diameter of treated columns at depths of 35 metres or more and for assuring and verifying measurement accuracy, and present supporting materials.",
  "next_pl": "Zbadać metody pomiaru średnicy kolumn na głębokości co najmniej 35 metrów oraz sposoby zapewnienia i weryfikacji dokładności pomiaru, a następnie przedstawić materiały."
}
REPORT_JSON
    ,true,32,JSON_THROW_ON_ERROR);
    $q=$db->prepare('SELECT id FROM users WHERE username=?');
    $q->execute(['taiju']); $uid=$q->fetchColumn();
    if(!$uid) { error_log('Keller report pending: Taiju account not found.'); return; }
    // Schema creation must precede the transaction (MySQL DDL implicitly commits).
    gpt_schema($db);
    $db->beginTransaction();
    try {
        $claim=$db->prepare('INSERT IGNORE INTO crm_seed_batches(seed_key) VALUES(?)');
        $claim->execute(['taiju-keller-jet-grouting-20261028-v1']);
        if(!$claim->rowCount()) { $db->commit(); return; }
        $q=$db->prepare("SELECT id FROM companies WHERE name LIKE ? ORDER BY id");
        $q->execute(['Keller%']); $companies=$q->fetchAll(PDO::FETCH_COLUMN);
        if(count($companies)>1) throw new RuntimeException('Multiple Keller companies: select the correct existing company before import.');
        if($companies) $cid=(int)$companies[0];
        else {
            $db->prepare('INSERT IGNORE INTO companies(name,country) VALUES(?,?)')->execute(['Keller','Poland']);
            $q=$db->prepare('SELECT id FROM companies WHERE name=?'); $q->execute(['Keller']); $cid=(int)$q->fetchColumn();
        }
        // Identify this visit, not unrelated historic Keller trials. Do not change its date
        // based on a photograph filename. If ambiguous, roll back rather than duplicate.
        $q=$db->prepare("SELECT id FROM activities WHERE company_id=? AND activity_date=? AND (created_by=? OR (created_by IS NULL AND owner_name IN ('Taiju','大樹'))) AND (subject=? OR summary_ja LIKE ? OR summary_en LIKE ?) ORDER BY id FOR UPDATE");
        $q->execute([$cid,$r['date'],$uid,$r['subject'],'%プリカッティング%','%pre-cutting%']); $ids=$q->fetchAll(PDO::FETCH_COLUMN);
        if(count($ids)>1) throw new RuntimeException('Multiple matching Keller visits: resolve before import.');
        $values=[$r['subject'],$r['ja'],$r['en'],$r['pl'],$r['next_ja'],$r['next_en'],$r['next_pl'],(int)$uid,$r['owner']];
        if($ids) {
            $aid=(int)$ids[0];
            $q=$db->prepare('SELECT * FROM activities WHERE id=?'); $q->execute([$aid]); $before=$q->fetch();
            $db->prepare('UPDATE activities SET subject=?,summary_ja=?,summary_en=?,summary_pl=?,next_action=?,next_action_en=?,next_action_pl=?,created_by=?,owner_name=? WHERE id=?')->execute([...$values,$aid]);
        } else {
            $before=null;
            $db->prepare("INSERT INTO activities(activity_date,company_id,activity_type,subject,summary_ja,summary_en,summary_pl,next_action,next_action_en,next_action_pl,created_by,owner_name,status) VALUES(?,?,'meeting',?,?,?,?,?,?,?,?,?,'open')")->execute([$r['date'],$cid,...$values]);
            $aid=(int)$db->lastInsertId();
        }
        $title='Keller：深度35m以上の改良体直径の測定方法・精度検証を調査';
        $q=$db->prepare('SELECT task_id FROM crm_gpt_activity_tasks WHERE activity_id=?'); $q->execute([$aid]); $tid=$q->fetchColumn();
        if(!$tid) {
            $q=$db->prepare('SELECT id FROM tasks WHERE company_id=? AND assigned_to=? AND title=? ORDER BY id');
            $q->execute([$cid,$uid,$title]); $taskIds=$q->fetchAll(PDO::FETCH_COLUMN);
            if(count($taskIds)>1) throw new RuntimeException('Multiple matching Keller actions.');
            $tid=$taskIds[0]??null;
        }
        if(!$tid) {
            $db->prepare("INSERT INTO tasks(company_id,title,title_en,title_pl,detail,detail_en,detail_pl,status,assigned_to,owner_name) VALUES(?,?,?,?,?,?,?,'open',?,?)")->execute([$cid,$title,'Keller: investigate column diameter measurement and accuracy at depths of 35 m or more','Keller: pomiar średnicy kolumn i dokładność na głębokości co najmniej 35 m',$r['next_ja'],$r['next_en'],$r['next_pl'],$uid,$r['owner']]);
            $tid=(int)$db->lastInsertId();
        }
        else {
            $db->prepare('UPDATE tasks SET title=?,title_en=?,title_pl=?,detail=?,detail_en=?,detail_pl=? WHERE id=?')->execute([$title,'Keller: investigate column diameter measurement and accuracy at depths of 35 m or more','Keller: pomiar średnicy kolumn i dokładność na głębokości co najmniej 35 m',$r['next_ja'],$r['next_en'],$r['next_pl'],$tid]);
        }
        // Existing task state and deadlines are preserved.
        $db->prepare('INSERT IGNORE INTO crm_gpt_activity_tasks(activity_id,task_id) VALUES(?,?)')->execute([$aid,$tid]);
        $q=$db->prepare('SELECT * FROM activities WHERE id=?'); $q->execute([$aid]); $after=$q->fetch();
        $db->prepare('INSERT INTO crm_gpt_changes(user_id,request_hash,entity,record_id,before_json,after_json) VALUES(?,?,?,?,?,?)')->execute([$uid,hash('sha256','taiju-keller-jet-grouting-20261028-v1'),'activity',$aid,json_encode($before,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),json_encode($after,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
        $db->commit();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}
