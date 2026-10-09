<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli' && !defined('CRM_TEST_RUNNER')) { http_response_code(404); exit; }
const APP_NAME='CRM import tests';
function ux(string $ja,string $en,string $pl): string { return $en; }
require dirname(__DIR__).'/chatgpt-import.php';
$tests=0;
function check(bool $ok,string $label): void { global $tests; if(!$ok) throw new RuntimeException('FAIL: '.$label); $tests++; }
function rejects(callable $run,string $label): void { try { $run(); } catch(InvalidArgumentException $e) { check(true,$label); return; } throw new RuntimeException('FAIL: '.$label); }
function parse_op(array $set,array $extra=[]): array { return gpt_parse_operations(json_encode(['operations'=>[array_replace(['entity'=>'contact','action'=>'upsert','set'=>$set],$extra)]]))[0]; }
$op=parse_op(['name'=>' Alice ','company'=>'Example','phone'=>'','email'=>null]);
check($op['set']===['company'=>'Example','name'=>'Alice'],'blank/null OCR fields omitted');
check(parse_op(['name'=>'Alice'],['clear'=>['phone']])['set']['phone']===null,'explicit clearing supported');
check(parse_op(['name'=>'Alice'],['clear'=>['title']])['set']['title']===null,'card title may be cleared');
rejects(fn()=>parse_op(['name'=>'Alice'],['clear'=>['name']]),'required name cannot be cleared');
rejects(fn()=>parse_op(['phone'=>'123'],['clear'=>['phone']]),'conflicting clear rejected');
rejects(fn()=>parse_op(['created_by'=>'1']),'owner cannot be supplied');
rejects(fn()=>parse_op(['email'=>'invalid']),'invalid email rejected');
rejects(fn()=>parse_op(['card_scanned_on'=>'2026-02-30']),'invalid dates rejected');
rejects(fn()=>parse_op(['card_scanned_on'=>'2026-2-3']),'strict dates');
rejects(fn()=>parse_op(['name'=>str_repeat('字',256)]),'Unicode length enforced');
rejects(fn()=>parse_op(['name'=>'A'],['match'=>['id'=>'1']]),'ID must be numeric integer');
rejects(fn()=>parse_op(['name'=>'A'],['match'=>['email'=>['bad']]]),'match arrays rejected');
rejects(fn()=>parse_op(['name'=>'A'],['expected'=>'not-a-hash']),'invalid versions rejected');
rejects(fn()=>parse_op(['name'=>'A'],['action'=>'delete']),'delete unsupported');
rejects(fn()=>gpt_parse_operations('{"operations":[]}'),'empty batch rejected');
rejects(fn()=>gpt_parse_operations(json_encode(['operations'=>array_fill(0,21,['entity'=>'contact','action'=>'upsert','set'=>['name'=>'A']])])),'batch limit');
rejects(fn()=>gpt_parse_operations(str_repeat(' ',100001)),'payload limit');
$a=parse_op(['product_codes'=>['DT','DT']],['entity'=>'activity','action'=>'update','match'=>['id'=>1]]);
check($a['set']['product_codes']===['DT'],'product links deduplicated');
$a=parse_op(['product_codes'=>[]],['entity'=>'activity','action'=>'update','match'=>['id'=>1]]);
check($a['set']['product_codes']===[],'explicit empty products clears links');
check(gpt_fingerprint(['b'=>2,'a'=>1])===gpt_fingerprint(['a'=>1,'b'=>2]),'stable version independent of key order');
rejects(fn()=>gpt_authorize('activity',['created_by'=>2],['id'=>1,'role'=>'user']),'other user activity blocked');
gpt_authorize('contact',[],['id'=>1,'role'=>'user']); check(true,'shared contact editing');
gpt_authorize('activity',['created_by'=>2],['id'=>1,'role'=>'admin']); check(true,'admin editing');
$legacy=parse_chatgpt_records('{"records":[{"date":"2026-10-10","company":"Example","subject":"Test","summary":"Text","language":"en"}]}');
check($legacy[0]['type']==='note','legacy input preserved');
// Deterministic fake read database exercises identity and preview behavior without live data.
class TestDB {
 public array $rows=[];
 function prepare(string $sql): TestQuery { return new TestQuery($this,$sql); }
}
class TestQuery {
 private array $result=[];
 function __construct(private TestDB $db,private string $sql) {}
 function execute(array $params=[]): bool {
  $this->result=[];
  if(str_contains($this->sql,'SELECT name FROM companies')) { $this->result=[['name'=>'Example']]; return true; }
  if(str_contains($this->sql,'SELECT * FROM')) { foreach($this->db->rows as $r) if($r['id']===$params[0]) $this->result[]=$r; return true; }
  if(str_contains($this->sql,'activity_products')) return true;
  if(str_contains($this->sql,'SELECT r.id')) {
   preg_match_all('/(?:r\.`([^`]+)`|c\.(name))=\?/',$this->sql,$m,PREG_SET_ORDER);
   foreach($this->db->rows as $r) { $ok=true; foreach($m as $i=>$f) { $key=$f[1]?:'company'; if(($r[$key]??'')!==$params[$i]) $ok=false; } if($ok) $this->result[]=['id'=>$r['id']]; } return true;
  }
  if(str_contains($this->sql,'SELECT ct.id')) { foreach($this->db->rows as $r) if($r['company']===$params[0] && $r['name']===$params[1]) $this->result[]=['id'=>$r['id']]; return true; }
  throw new RuntimeException('Unhandled test query '.$this->sql);
 }
 function fetch(): array|false { return $this->result[0]??false; }
 function fetchColumn(): mixed { return $this->result?array_values($this->result[0])[0]:false; }
 function fetchAll(int $mode=PDO::FETCH_ASSOC): array { return $mode===PDO::FETCH_COLUMN?array_map(fn($r)=>array_values($r)[0],$this->result):$this->result; }
}
$testDB=new TestDB(); function db(): TestDB { global $testDB; return $testDB; }
$user=['id'=>1,'role'=>'admin'];
$card=['id'=>7,'company_id'=>1,'company'=>'Example','name'=>'Alice','email'=>'alice@example.com','phone'=>'OLD'];
$testDB->rows=[$card];
$p=gpt_prepare([parse_op(['company'=>'Example','name'=>'Alice','phone'=>'NEW'],['match'=>['email'=>'alice@example.com']])],$user);
check($p[0]['id']===7 && $p[0]['before']['phone']==='OLD' && $p[0]['set']['phone']==='NEW','unique email update previews before and after');
$p=gpt_prepare([parse_op(['company'=>'Example','name'=>'Alice','email'=>'new@example.com'])],$user);
check($p[0]['id']===7,'email change falls back to exact company/name');
$testDB->rows=[$card,array_replace($card,['id'=>8])];
rejects(fn()=>gpt_prepare([parse_op(['phone'=>'NEW'],['match'=>['email'=>'alice@example.com']])],$user),'ambiguous contact blocked');
$testDB->rows=[$card];
rejects(fn()=>gpt_prepare([parse_op(['phone'=>'NEW'],['action'=>'update','match'=>['id'=>99]])],$user),'missing update never creates');
rejects(fn()=>gpt_prepare([parse_op(['phone'=>'NEW'],['action'=>'update','match'=>['id'=>7],'expected'=>str_repeat('0',64)])],$user),'stale context blocked');
$testDB->rows=[];
$p=gpt_prepare([parse_op(['company'=>'Example','name'=>'Alice','email'=>'alice@example.com'])],$user);
check($p[0]['id']===0,'unmatched card creates');
rejects(fn()=>gpt_prepare([parse_op(['email'=>'alice@example.com'])],$user),'new card requires company/name');
$activity=['id'=>9,'company_id'=>1,'company'=>'Example','created_by'=>1,'subject'=>'Old','summary_ja'=>'古い','summary_en'=>'Old','summary_pl'=>'Stare','next_action'=>null,'next_action_date'=>null,'product_codes'=>[]];
$testDB->rows=[$activity];
$p=gpt_prepare([parse_op(['summary_en'=>'Corrected'],['entity'=>'activity','action'=>'update','match'=>['id'=>9]])],$user);
check($p[0]['set']['summary_ja']==='Corrected' && $p[0]['set']['summary_pl']===null,'stale translations cleared with readable fallback');
echo "PASS: $tests import checks\n";
