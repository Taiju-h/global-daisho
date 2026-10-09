<?php
if(PHP_SAPI!=='cli' && !defined('CRM_TEST_RUNNER')) { http_response_code(404); exit; }
const APP_NAME='test';
function current_lang(): string { return 'en'; }
function ux(string $ja,string $en,string $pl): string { return $en; }
function h(?string $value): string {return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function tr(string $key): string{return $key;}
function legacy_translation(?string $value): string{return (string)$value;}
require dirname(__DIR__).'/tests-sheet.php';
$data=require dirname(__DIR__).'/tests-sheet-data.php';
$checks=0;
function verify(bool $ok,string $label): void {global $checks;if(!$ok)throw new RuntimeException($label);$checks++;}
verify(count($data)===11,'11 records');
verify(array_column($data,'number')===range(101,111),'source identifiers');
verify(count(array_unique(array_column($data,'source_key')))===11,'unique keys');
verify($data[3]['dates']['performed']['iso']==='2026-09-08','hidden full date from serial');
verify($data[2]['dates']['performed']['iso']===null,'note date not invented as performed');
verify($data[5]['company']===null,'non-company label not a company');
verify($data[9]['product_code']===null,'Japan Pile product not inferred');
verify(!in_array('DT',array_column($data,'product_code'),true),'no confidential chemical alias inferred');
verify(array_sum(array_map(fn($r)=>count($r['media']),$data))===70,'70 relevant media entries');
foreach($data as $r){
 verify($r['title']===$r['original_values'][1],'original title preserved');
 verify($r['purpose']===trim($r['original_values'][8]),'purpose source fidelity');
 verify($r['result']===trim($r['original_values'][10]),'result source fidelity');
 verify($r['notes']===trim($r['original_values'][9]),'notes source fidelity');
 verify($r['status']!=='planned' && $r['status']!=='done','completion/plans not invented');
 verify(isset($r['translations']['en']['title'],$r['translations']['pl']['title']),'translated titles');
}
verify(sheet_test_text($data[9],'title','A later edit')==='A later edit','later edits override snapshot translation');
class FixtureDB {
 function query(string $sql): array {
  global $data;$rows=[];
  foreach($data as $r)$rows[]=['id'=>$r['number'],'test_date'=>$r['dates']['performed']['iso'],'product_id'=>null,'company'=>$r['company'],'site'=>$r['site'],'title'=>$r['title'],'purpose'=>$r['purpose'],'result'=>$r['result'],'status'=>$r['status'],'next_step'=>null,'experiment_number'=>$r['number'],'source_snapshot'=>json_encode($r)];
  return $rows;
 }
}
function db(): FixtureDB {return new FixtureDB();}
ob_start();render_sheet_tests();$html=ob_get_clean();
verify(substr_count($html,'id="test-')===11,'11 rendered cards');
verify(str_contains($html,'#110'),'experiment identifiers shown');
verify(str_contains($html,'Results not recorded'),'blank results labelled');
verify(str_contains($html,'source sheet row'),'media link limits explained');
verify(str_contains($html,'2026-09-08'),'full date rendered');
echo "PASS: $checks experiment checks\n";
