<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli' && !defined('CRM_TEST_RUNNER')) { http_response_code(404); exit; }
const APP_NAME='MCP tests';
function ux(string $ja,string $en,string $pl): string { return $en; }
require dirname(__DIR__).'/chatgpt-import.php';
require dirname(__DIR__).'/mcp-core.php';
$n=0;
function check(bool $ok,string $label): void { global $n; if(!$ok) throw new RuntimeException('FAIL: '.$label); $n++; }
function rejects(callable $f,string $label): void { try{$f();}catch(InvalidArgumentException $e){check(true,$label);return;} throw new RuntimeException('FAIL: '.$label); }
class StateDB extends PDO {
    public array $states=[]; public array $records=[]; public array $audit=[]; public int $lastId=0;
    public array $user=['id'=>1,'username'=>'test','display_name'=>'Test','role'=>'admin','must_change_password'=>0,'auth_version'=>2];
    private ?array $backup=null;
    public function __construct() {}
    public function prepare(string $query,array $options=[]): PDOStatement|false { return new StateStatement($this,$query); }
    public function query(string $query,?int $fetchMode=null,mixed ...$args): PDOStatement|false { $s=new StateStatement($this,$query); $s->execute(); return $s; }
    public function lastInsertId(?string $name=null): string|false { return (string)$this->lastId; }
    public function beginTransaction(): bool { $this->backup=[$this->states,$this->records,$this->audit,$this->lastId]; return true; }
    public function commit(): bool { $this->backup=null; return true; }
    public function rollBack(): bool { [$this->states,$this->records,$this->audit,$this->lastId]=$this->backup; $this->backup=null; return true; }
    public function inTransaction(): bool { return $this->backup!==null; }
}
class StateStatement extends PDOStatement {
    private mixed $result=false;
    public function __construct(private StateDB $db,private string $sql) {}
    public function execute(?array $params=null): bool {
        $p=$params??[]; $this->result=false;
        if(str_starts_with($this->sql,'INSERT INTO crm_mcp_state')) { if(isset($this->db->states[$p[0]])) throw new RuntimeException('duplicate'); $this->db->states[$p[0]]=['state_key'=>$p[0],'kind'=>$p[1],'user_id'=>$p[2],'auth_version'=>$p[3],'expires_at'=>$p[4],'payload'=>$p[5]]; }
        elseif(str_starts_with($this->sql,'SELECT * FROM crm_mcp_state')) { $r=$this->db->states[$p[0]]??false; $this->result=$r && $r['kind']===$p[1]?$r:false; }
        elseif(str_starts_with($this->sql,'SELECT id,username')) $this->result=$p[0]===$this->db->user['id']?$this->db->user:false;
        elseif(str_starts_with($this->sql,'DELETE FROM crm_mcp_state')) unset($this->db->states[$p[0]]);
        elseif(str_starts_with($this->sql,'UPDATE crm_mcp_state SET payload=')) { $key=$p[count($p)-1]; $this->db->states[$key]['payload']=$p[0]; if(count($p)===3) $this->db->states[$key]['expires_at']=$p[1]; }
        elseif(str_contains($this->sql,'GET_LOCK(') || str_contains($this->sql,'RELEASE_LOCK(')) $this->result=1;
        elseif(str_starts_with($this->sql,'SELECT r.id FROM `tests`')) { foreach($this->db->records as $r) if($r['title']===$p[0] && ($r['company']??'')===$p[1]) { $this->result=['id'=>$r['id']]; break; } }
        elseif(str_starts_with($this->sql,'SELECT t.*,c.name company,p.code product_code FROM tests')) $this->result=$this->db->records[$p[0]]??false;
        elseif(str_starts_with($this->sql,'SELECT id FROM `tests`')) $this->result=isset($this->db->records[$p[0]])?['id'=>$p[0]]:false;
        elseif(str_starts_with($this->sql,'INSERT INTO `tests`')) {
            preg_match('/INSERT INTO `tests` \((.*?)\) VALUES/',$this->sql,$m); $cols=explode('`,`',trim($m[1],'`'));
            $row=array_combine($cols,$p); $id=++$this->db->lastId; $this->db->records[$id]=$row+['id'=>$id,'company'=>null,'product_code'=>null];
        }
        elseif(str_starts_with($this->sql,'UPDATE `tests` SET ')) {
            preg_match('/SET (.*?) WHERE/',$this->sql,$m); $cols=array_map(fn($v)=>substr($v,1,strpos($v,'`',1)-1),explode(',',$m[1])); $id=array_pop($p); foreach($cols as $i=>$k) $this->db->records[$id][$k]=$p[$i];
        }
        elseif(str_starts_with($this->sql,'INSERT INTO crm_gpt_changes')) $this->db->audit[]=$p;
        else throw new RuntimeException('Unexpected SQL: '.$this->sql);
        return true;
    }
    public function fetchColumn(int $column=0): mixed { return is_array($this->result)?array_values($this->result)[$column]:$this->result; }
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $cursorOrientation=PDO::FETCH_ORI_NEXT,int $cursorOffset=0): mixed { return $this->result; }
}
$fixture=new StateDB(); function db(): PDO { global $fixture; return $fixture; }
$v='dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'; $challenge='E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
check(crm_mcp_pkce($v,$challenge),'RFC 7636 S256 vector');
rejects(fn()=>crm_mcp_validate_extra(['entity'=>'test','set'=>['title'=>'x','test_date'=>'2026-02-30']]),'invalid date');
check(!crm_mcp_pkce(str_repeat('a',43),$challenge),'wrong verifier'); check(!crm_mcp_pkce('short',$challenge),'short verifier');
check(crm_mcp_redirect_allowed('https://chatgpt.com/connector_platform_oauth_redirect'),'stable callback');
foreach(['https://chatgpt.com.evil.example/connector_platform_oauth_redirect','http://chatgpt.com/connector_platform_oauth_redirect','https://chatgpt.com/connector_platform_oauth_redirect?evil=1','https://evil.example','https://chatgpt.com@evil.example/'] as $bad) check(!crm_mcp_redirect_allowed($bad),'redirect rejected');
$client=crm_mcp_secret(); crm_mcp_put($client,'client',['redirect_uris'=>['https://chatgpt.com/connector_platform_oauth_redirect']],0);
$auth=['client_id'=>$client,'redirect_uri'=>'https://chatgpt.com/connector_platform_oauth_redirect','response_type'=>'code','code_challenge'=>$challenge,'code_challenge_method'=>'S256','state'=>'opaque-state','resource'=>CRM_MCP_URL,'scope'=>CRM_MCP_SCOPE];
check(crm_mcp_authorization($auth)['state']==='opaque-state','authorization valid');
foreach(['redirect_uri'=>'https://evil.example','resource'=>'https://other.example','code_challenge_method'=>'plain','scope'=>'admin','client_id'=>'unknown','state'=>''] as $k=>$bad) rejects(fn()=>crm_mcp_authorization(array_replace($auth,[$k=>$bad])),'authorization binding '.$k);
$code=crm_mcp_secret(); crm_mcp_put($code,'code',crm_mcp_authorization($auth),time()+120,$fixture->user);
$exchange=['client_id'=>$client,'grant_type'=>'authorization_code','code'=>$code,'code_verifier'=>$v,'redirect_uri'=>$auth['redirect_uri'],'resource'=>CRM_MCP_URL];
rejects(fn()=>crm_mcp_exchange(array_replace($exchange,['code_verifier'=>str_repeat('a',43)])),'wrong PKCE fails');
check(crm_mcp_state($code,'code')!==null,'failed exchange rolls back');
rejects(fn()=>crm_mcp_exchange(array_replace($exchange,['resource'=>'https://other.example'])),'wrong audience fails');
rejects(fn()=>crm_mcp_exchange(array_replace($exchange,['redirect_uri'=>'https://evil.example'])),'wrong callback fails');
$tokens=crm_mcp_exchange($exchange);
check($tokens['expires_in']===3600,'short access lifetime'); check(crm_mcp_state($code,'code')===null,'code consumed');
rejects(fn()=>crm_mcp_exchange($exchange),'code replay rejected');
$access=crm_mcp_state($tokens['access_token'],'access'); check(crm_mcp_user($access)!==null,'access bound to user/version');
check(!isset($fixture->states[$tokens['access_token']]),'raw token not stored as key');
$refresh=['client_id'=>$client,'grant_type'=>'refresh_token','refresh_token'=>$tokens['refresh_token'],'resource'=>CRM_MCP_URL];
$rotated=crm_mcp_exchange($refresh); check($rotated['refresh_token']!==$tokens['refresh_token'],'refresh rotates');
rejects(fn()=>crm_mcp_exchange($refresh),'refresh reuse rejected');
check(crm_mcp_state($access['data']['grant'],'grant')===null,'reused refresh revokes grant, including access tokens');
$fixture->user['auth_version']++; check(crm_mcp_user($access)===null,'password reset invalidates access'); $fixture->user['auth_version']--;
$fixture->user['must_change_password']=1; check(crm_mcp_user($access)===null,'temporary-password account rejected'); $fixture->user['must_change_password']=0;
$expired=crm_mcp_secret(); crm_mcp_put($expired,'code',[],time()-1,$fixture->user); check(crm_mcp_state($expired,'code')===null,'expired code rejected');
check(crm_mcp_validate_extra(['entity'=>'test','set'=>['title'=>' Test ','result'=>'','test_date'=>null]])===['title'=>'Test'],'unknown OCR omitted');
check(crm_mcp_validate_extra(['entity'=>'test','set'=>[],'clear'=>['result']])===['result'=>null],'explicit result clear');
foreach([['owner_name'=>'Other'],['product_id'=>'1'],['status'=>'shipped'],['test_date'=>'2026-1-1'],['title'=>str_repeat('字',256)]] as $bad) rejects(fn()=>crm_mcp_validate_extra(['entity'=>'test','set'=>$bad]),'invalid experiment field');
rejects(fn()=>crm_mcp_validate_extra(['entity'=>'test','set'=>[],'clear'=>['title']]),'title cannot be erased');
rejects(fn()=>crm_mcp_prepare([['entity'=>'test','action'=>'create','set'=>['title'=>'test']]],array_replace($fixture->user,['role'=>'user'])),'non-admin experiment write rejected');
rejects(fn()=>crm_mcp_prepare([],$fixture->user),'empty batch rejected');
$tools=crm_mcp_tools(); check(count($tools)===5,'five bounded tools');
foreach($tools as $t) { check(is_object($t['inputSchema']['properties']),'valid JSON schema properties object'); check($t['securitySchemes'][0]['type']==='oauth2','OAuth on each tool'); }
check(!$tools[3]['annotations']['readOnlyHint'] && !$tools[4]['annotations']['readOnlyHint'],'preview/apply truthfully marked writes');
rejects(fn()=>crm_mcp_call('crm_get',['entity'=>'users','id'=>1],$fixture->user),'no user/credential table access');
rejects(fn()=>crm_mcp_call('crm_apply',['preview_token'=>'bad'],$fixture->user),'invalid preview token');
rejects(fn()=>crm_mcp_call('execute_sql',[],$fixture->user),'no arbitrary SQL');
// Exercise commit/retry/version/rollback behavior using the bounded SQL fixture.
$ops=[['entity'=>'test','action'=>'create','set'=>['title'=>'Authorized experiment','purpose'=>'Measure flow']]];
$preview=crm_mcp_call('crm_preview',['operations'=>$ops],$fixture->user);
check($preview['saved']===false && count($fixture->records)===0,'preview never registers a test');
$saved=crm_mcp_call('crm_apply',['preview_token'=>$preview['preview_token']],$fixture->user);
check($saved['saved']===1 && count($fixture->records)===1,'apply writes once');
check($saved['records'][0]['record']['status']==='unconfirmed','unknown experiment date is not invented');
$replayed=crm_mcp_call('crm_apply',['preview_token'=>$preview['preview_token']],$fixture->user);
check($replayed===$saved && count($fixture->records)===1 && count($fixture->audit)===1,'apply replay returns same receipt with no duplicate');
$other=array_replace($fixture->user,['id'=>2]); rejects(fn()=>crm_mcp_apply($preview['preview_token'],$other),'receipt bound to user');
$id=$saved['records'][0]['id']; $record=crm_mcp_record('test',$id);
$change=['entity'=>'test','action'=>'update','match'=>['id'=>$id],'expected'=>$record['version'],'set'=>['result'=>'Flow 200 mm']];
$preview=crm_mcp_call('crm_preview',['operations'=>[$change]],$fixture->user);
$fixture->records[$id]['purpose']='Concurrent edit';
rejects(fn()=>crm_mcp_apply($preview['preview_token'],$fixture->user),'stale preview rejects');
check(!isset($fixture->records[$id]['result']) && count($fixture->audit)===1,'stale update leaves no partial write/audit');
$change['expected']=crm_mcp_record('test',$id)['version'];
$preview=crm_mcp_call('crm_preview',['operations'=>[$change]],$fixture->user); crm_mcp_apply($preview['preview_token'],$fixture->user);
check(crm_mcp_record('test',$id)['record']['result']==='Flow 200 mm','fresh update saved and read back');
$before=count($fixture->records); $auditBefore=count($fixture->audit);
$duplicateOps=[['entity'=>'test','action'=>'create','set'=>['title'=>'Same batch title','purpose'=>'a']],['entity'=>'test','action'=>'create','set'=>['title'=>'Same batch title','purpose'=>'b']]];
$preview=crm_mcp_call('crm_preview',['operations'=>$duplicateOps],$fixture->user);
rejects(fn()=>crm_mcp_apply($preview['preview_token'],$fixture->user),'same-batch duplicate detected');
check(count($fixture->records)===$before && count($fixture->audit)===$auditBefore,'whole batch rolled back');
echo "PASS $n MCP/OAuth checks (in-memory PDO fixture; not a live MySQL test)\n";
