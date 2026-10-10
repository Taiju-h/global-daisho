<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }
const CRM_MCP_BASE='https://global.daishokagaku.com';
const CRM_MCP_URL=CRM_MCP_BASE.'/salesdata/crm/mcp.php';
const CRM_MCP_SCOPE='crm.read crm.write';
function crm_mcp_json(mixed $data,int $status=200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR); exit;
}
function crm_mcp_secret(): string { return rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'='); }
function crm_mcp_key(string $s): string { return hash('sha256',$s); }
function crm_mcp_schema(): void {
    $sql=file_get_contents(dirname(__DIR__,2).'/.deploy/crm-sql/0006_mcp_state.ddl.sql');
    if($sql===false) throw new RuntimeException('MCP schema SQL is missing.');
    db()->exec($sql);
    gpt_schema(db());
}
function crm_mcp_put(string $key,string $kind,array $payload,int $expiry,?array $user=null): void {
    db()->prepare('INSERT INTO crm_mcp_state(state_key,kind,user_id,auth_version,expires_at,payload) VALUES(?,?,?,?,?,?)')->execute([crm_mcp_key($key),$kind,$user['id']??null,$user['auth_version']??0,$expiry,gpt_json($payload)]);
}
function crm_mcp_state(string $key,string $kind,bool $lock=false): ?array {
    $q=db()->prepare('SELECT * FROM crm_mcp_state WHERE state_key=? AND kind=?'.($lock?' FOR UPDATE':'')); $q->execute([crm_mcp_key($key),$kind]); $r=$q->fetch();
    if(!$r || ($r['expires_at'] && (int)$r['expires_at']<=time())) return null;
    $r['data']=json_decode($r['payload'],true,64,JSON_THROW_ON_ERROR); return $r;
}
function crm_mcp_user(array $state): ?array {
    $q=db()->prepare('SELECT id,username,display_name,role,must_change_password,auth_version FROM users WHERE id=?'); $q->execute([$state['user_id']]); $u=$q->fetch();
    return $u && !$u['must_change_password'] && (int)$u['auth_version']===(int)$state['auth_version']?$u:null;
}
function crm_mcp_pkce(string $verifier,string $challenge): bool {
    return preg_match('/\A[A-Za-z0-9._~-]{43,128}\z/',$verifier)===1 && hash_equals($challenge,rtrim(strtr(base64_encode(hash('sha256',$verifier,true)),'+/','-_'),'='));
}
function crm_mcp_redirect_allowed(string $url): bool {
    // Stable callback is bound to this issuer by RFC 9207. No caller-controlled hosts.
    return $url==='https://chatgpt.com/connector_platform_oauth_redirect';
}
function crm_mcp_authorization(array $p): array {
    foreach(['client_id','redirect_uri','response_type','code_challenge','code_challenge_method','state','resource','scope'] as $k) if(!isset($p[$k]) || !is_string($p[$k])) throw new InvalidArgumentException('Missing authorization parameter: '.$k);
    if($p['response_type']!=='code' || $p['code_challenge_method']!=='S256' || !preg_match('/\A[A-Za-z0-9_-]{43}\z/',$p['code_challenge']) || $p['resource']!==CRM_MCP_URL || !crm_mcp_redirect_allowed($p['redirect_uri']) || strlen($p['state'])<1 || strlen($p['state'])>2048) throw new InvalidArgumentException('Invalid authorization request.');
    $scopes=preg_split('/\s+/',trim($p['scope'])); sort($scopes); if($scopes!==['crm.read','crm.write']) throw new InvalidArgumentException('Request crm.read and crm.write.');
    $c=crm_mcp_state($p['client_id'],'client'); if(!$c || !in_array($p['redirect_uri'],$c['data']['redirect_uris'],true)) throw new InvalidArgumentException('Unknown client.');
    return array_intersect_key($p,array_flip(['client_id','redirect_uri','code_challenge','state','resource','scope']));
}
function crm_mcp_rate(string $bucket,int $limit): void {
    $key=crm_mcp_key('rate:'.$bucket.':'.(string)($_SERVER['REMOTE_ADDR']??'').':'.intdiv(time(),60));
    db()->prepare("INSERT INTO crm_mcp_state(state_key,kind,expires_at,payload) VALUES(?,'rate',?,'1') ON DUPLICATE KEY UPDATE payload=CAST(payload AS UNSIGNED)+1")->execute([$key,time()+120]);
    $q=db()->prepare('SELECT payload FROM crm_mcp_state WHERE state_key=?'); $q->execute([$key]); if((int)$q->fetchColumn()>$limit) crm_mcp_json(['error'=>'rate_limited'],429);
}
function crm_mcp_fields(string $entity): array {
    if($entity==='test') return ['company'=>191,'product_code'=>191,'test_date'=>10,'site'=>255,'title'=>255,'purpose'=>12000,'result'=>12000,'next_step'=>12000,'status'=>30];
    return gpt_fields($entity);
}
function crm_mcp_table(string $entity): string {
    if($entity==='test') return 'tests'; return gpt_table($entity);
}
function crm_mcp_snapshot(string $entity,int $id): ?array {
    if($entity!=='test') return gpt_snapshot($entity,$id);
    $q=db()->prepare('SELECT t.*,c.name company,p.code product_code FROM tests t LEFT JOIN companies c ON c.id=t.company_id LEFT JOIN products p ON p.id=t.product_id WHERE t.id=?'); $q->execute([$id]); return $q->fetch()?:null;
}
function crm_mcp_can_edit(string $entity,array $row,array $user): void {
    if($entity==='test') { if($user['role']!=='admin') throw new InvalidArgumentException('Experiment changes currently require a CRM administrator.'); }
    else gpt_authorize($entity,$row,$user);
}
function crm_mcp_link(string $entity,int $id): string {
    return CRM_MCP_BASE.'/salesdata/crm/?'.($entity==='contact'?'page=card&id='.$id:($entity==='test'?'page=tests#test-'.$id:($entity==='activity'?'page=activities':'page=dashboard')));
}
function crm_mcp_record(string $entity,int $id): array {
    $r=crm_mcp_snapshot($entity,$id); if(!$r) throw new InvalidArgumentException('Record not found.');
    return ['entity'=>$entity,'id'=>$id,'record'=>$r,'version'=>gpt_fingerprint($r),'url'=>crm_mcp_link($entity,$id)];
}
function crm_mcp_validate_extra(array $o): array {
    $entity=$o['entity']; $set=$o['set']??[]; $clear=$o['clear']??[];
    if(!is_array($set) || !is_array($clear) || !array_is_list($clear)) throw new InvalidArgumentException('Invalid fields.');
    $fields=crm_mcp_fields($entity); $clean=[];
    foreach($set as $k=>$v) {
        if(!isset($fields[$k])) throw new InvalidArgumentException('Unknown field: '.$k);
        if($v===null || $v==='') continue;
        if(!is_string($v) || preg_match('//u',$v)!==1 || preg_match_all('/./us',$v)>$fields[$k]) throw new InvalidArgumentException('Invalid field: '.$k);
        $v=trim($v); if($v==='') continue;
        if(in_array($k,['test_date','due_date'],true)) { $d=DateTimeImmutable::createFromFormat('!Y-m-d',$v); if(!$d || $d->format('Y-m-d')!==$v) throw new InvalidArgumentException('Invalid date.'); }
        if($k==='status' && !in_array($v,$entity==='test'?['planned','recorded','unconfirmed','done']:['open','waiting','conditional','done'],true)) throw new InvalidArgumentException('Invalid status.');
        $clean[$k]=$v;
    }
    foreach($clear as $k) { if(!is_string($k) || !isset($fields[$k]) || in_array($k,['company','title','status'],true) || isset($clean[$k])) throw new InvalidArgumentException('Invalid clear field.'); $clean[$k]=null; }
    if(!$clean) throw new InvalidArgumentException('No fields to change.'); return $clean;
}
function crm_mcp_prepare(array $operations,array $user): array {
    if(!array_is_list($operations) || count($operations)<1 || count($operations)>20) throw new InvalidArgumentException('Use 1–20 operations.');
    $result=[]; $targets=[];
    foreach($operations as $o) {
        if(!is_array($o) || array_diff(array_keys($o),['entity','action','match','set','clear','expected'])) throw new InvalidArgumentException('Invalid operation.');
        $entity=$o['entity']??''; $action=$o['action']??'';
        if(!is_string($entity) || !in_array($entity,['contact','activity','task','test'],true) || !in_array($action,['create','update','upsert'],true) || ($action==='upsert' && $entity!=='contact')) throw new InvalidArgumentException('Invalid entity/action.');
        if($entity==='test' || ($entity==='task' && $action==='create')) {
            if($entity==='test' && $user['role']!=='admin') throw new InvalidArgumentException('Experiment changes currently require a CRM administrator.');
            $set=crm_mcp_validate_extra($o); $match=$o['match']??[]; $before=[];
            if($action==='update') {
                if(array_keys($match)!==['id'] || !is_int($match['id']) || $match['id']<1) throw new InvalidArgumentException('Experiment updates require match.id.');
                $before=crm_mcp_snapshot($entity,$match['id']); if(!$before) throw new InvalidArgumentException('Record not found.');
                if(!isset($o['expected']) || $o['expected']!==gpt_fingerprint($before)) throw new InvalidArgumentException('Read the current record and provide its version as expected.');
            } else {
                if($match) throw new InvalidArgumentException('Do not supply match for a new experiment/task.');
                if(empty($set['title'])) throw new InvalidArgumentException('A title is required.');
                if($entity==='task' && empty($set['company'])) throw new InvalidArgumentException('A company is required.');
                $table=crm_mcp_table($entity); $q=db()->prepare("SELECT r.id FROM `$table` r LEFT JOIN companies c ON c.id=r.company_id WHERE r.title=? AND COALESCE(c.name,'')=? LIMIT 1"); $q->execute([$set['title'],$set['company']??'']);
                if($q->fetchColumn()) throw new InvalidArgumentException('A matching title/company already exists. Read and update that record.');
                $set['status']=$set['status']??($entity==='task'?'open':(!empty($set['test_date'])?'recorded':'unconfirmed'));
            }
            if(isset($set['product_code'])) { $q=db()->prepare('SELECT id FROM products WHERE code=?'); $q->execute([$set['product_code']]); if(!$q->fetchColumn()) throw new InvalidArgumentException('Unknown product code.'); }
            $p=['operation'=>$o,'entity'=>$entity,'id'=>(int)($before['id']??0),'before'=>$before,'set'=>$set,'version'=>gpt_fingerprint($before?:null),'extra'=>true];
        } else {
            $parsed=gpt_parse_operations(gpt_json(['operations'=>[$o]])); $p=gpt_prepare($parsed,$user)[0];
            if($p['id'] && empty($o['expected'])) throw new InvalidArgumentException('Read the record and provide its version as expected before updating it.');
            if($entity==='activity' && $p['id']) {
                $q=db()->prepare('SELECT t.* FROM tasks t JOIN crm_gpt_activity_tasks l ON l.task_id=t.id WHERE l.activity_id=?'); $q->execute([$p['id']]); $task=$q->fetch();
                if($task) gpt_authorize('task',$task,$user);
                $p['task_version']=$task?gpt_fingerprint($task):null; $p['linked_task']=$task?:null;
                if($task && array_key_exists('next_action',$p['set']) && empty($p['set']['next_action'])) throw new InvalidArgumentException('Resolve the linked task before removing its next action.');
            }
        }
        $key=$entity.':'.($p['id']?:crm_mcp_key(gpt_json($p['set']))); if(isset($targets[$key])) throw new InvalidArgumentException('One operation per record.'); $targets[$key]=true; $result[]=$p;
    }
    foreach($result as $p) if(!empty($p['linked_task'])) foreach($result as $other) if($other['entity']==='task' && $other['id']===(int)$p['linked_task']['id']) throw new InvalidArgumentException('Update a linked activity and its task in separate requests.');
    return $result;
}
function crm_mcp_write_extra(array $p,array $user): int {
    $set=$p['set']; $entity=$p['entity']; $id=$p['id']; $table=crm_mcp_table($entity);
    if(isset($set['company'])) { db()->prepare('INSERT IGNORE INTO companies(name) VALUES(?)')->execute([$set['company']]); $q=db()->prepare('SELECT id FROM companies WHERE name=?'); $q->execute([$set['company']]); $set['company_id']=(int)$q->fetchColumn(); unset($set['company']); }
    if(array_key_exists('product_code',$set)) { $pid=null; if($set['product_code']!==null) { $q=db()->prepare('SELECT id FROM products WHERE code=?'); $q->execute([$set['product_code']]); $pid=$q->fetchColumn(); if(!$pid) throw new InvalidArgumentException('Product no longer exists.'); } $set['product_id']=$pid; unset($set['product_code']); }
    if($id) { $sql=implode(',',array_map(fn($k)=>'`'.$k.'`=?',array_keys($set))); db()->prepare("UPDATE `$table` SET $sql WHERE id=?")->execute([...array_values($set),$id]); }
    else { $set['owner_name']=$user['display_name']; if($entity==='task') $set['assigned_to']=$user['id']; db()->prepare("INSERT INTO `$table` (`".implode('`,`',array_keys($set)).'`) VALUES('.implode(',',array_fill(0,count($set),'?')).')')->execute(array_values($set)); $id=(int)db()->lastInsertId(); }
    return $id;
}
function crm_mcp_apply(string $token,array $user): array {
    $db=db(); if((int)$db->query("SELECT GET_LOCK('daisho_crm_gpt_import_v2',5)")->fetchColumn()!==1) throw new InvalidArgumentException('Another import is running; retry the same preview token.');
    try {
        $db->beginTransaction();
        try {
            $s=crm_mcp_state($token,'preview',true); if(!$s || (int)$s['user_id']!==(int)$user['id'] || (int)$s['auth_version']!==(int)$user['auth_version']) throw new InvalidArgumentException('Preview expired or belongs to another user.');
            if(isset($s['data']['receipt'])) { $receipt=$s['data']['receipt']; $db->commit(); return $receipt; }
            // Lock the original records before establishing a read snapshot. Manual CRM edits
            // must not race between the version check and the write.
            foreach($s['data']['items'] as $original) {
                if($original['id']) { $table=crm_mcp_table($original['entity']); $q=$db->prepare("SELECT id FROM `$table` WHERE id=? FOR UPDATE"); $q->execute([$original['id']]); $q->fetch(); }
                if(!empty($original['linked_task'])) { $q=$db->prepare('SELECT id FROM tasks WHERE id=? FOR UPDATE'); $q->execute([$original['linked_task']['id']]); $q->fetch(); }
            }
            $items=crm_mcp_prepare($s['data']['operations'],$user); $saved=[];
            foreach($items as $i=>$p) {
                if($p['version']!==$s['data']['items'][$i]['version'] || ($p['task_version']??null)!==($s['data']['items'][$i]['task_version']??null)) throw new InvalidArgumentException('Data changed after preview. Read and preview again.');
                if(!$p['id']) crm_mcp_prepare([$s['data']['operations'][$i]],$user); // detect duplicates created earlier in this batch
                $id=!empty($p['extra'])?crm_mcp_write_extra($p,$user):gpt_write($p,$user);
                $record=crm_mcp_record($p['entity'],$id); $saved[]=$record;
                $db->prepare('INSERT INTO crm_gpt_changes(user_id,request_hash,entity,record_id,before_json,after_json) VALUES(?,?,?,?,?,?)')->execute([$user['id'],crm_mcp_key($token.':'.$i),$p['entity'],$id,gpt_json(['record'=>$p['before'],'related_task'=>$p['linked_task']??null]),gpt_json(['record'=>$record['record'],'related_task'=>!empty($p['linked_task'])?gpt_snapshot('task',(int)$p['linked_task']['id']):null])]);
            }
            $receipt=['saved'=>count($saved),'records'=>$saved,'committed_at'=>gmdate('c')];
            $db->prepare('UPDATE crm_mcp_state SET payload=?,expires_at=? WHERE state_key=?')->execute([gpt_json(['receipt'=>$receipt]),time()+86400*30,crm_mcp_key($token)]); $db->commit(); return $receipt;
        } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
    } finally { $db->query("SELECT RELEASE_LOCK('daisho_crm_gpt_import_v2')"); }
}
function crm_mcp_tools(): array {
    $schema=fn(array $p,array $required)=>['type'=>'object','properties'=>(object)$p,'required'=>$required,'additionalProperties'=>false];
    $entity=['type'=>'string','enum'=>['contact','activity','task','test']]; $str=['type'=>'string'];
    $specs=[
      ['crm_schema','Get supported CRM fields, matching rules and record types before preparing changes.',$schema([],[]),true],
      ['crm_search','Search existing contacts, activities, actions and experiments by name/title/company. Read exact records before updates.',$schema(['entity'=>$entity,'query'=>$str],['entity','query']),true],
      ['crm_get','Read a CRM record with current version and a browser link. Use version as expected when correcting it.',$schema(['entity'=>$entity,'id'=>['type'=>'integer','minimum'=>1]],['entity','id']),true],
      ['crm_preview','Validate user-requested changes without changing business records. Returns before/after and a short-lived preview token. Use crm_schema first. Unknown OCR fields must be omitted. Never infer confidential product chemistry.',$schema(['operations'=>['type'=>'array','minItems'=>1,'maxItems'=>20,'items'=>['type'=>'object','properties'=>['entity'=>$entity,'action'=>['type'=>'string','enum'=>['create','update','upsert']],'match'=>['type'=>'object','additionalProperties'=>true],'set'=>['type'=>'object','additionalProperties'=>true],'clear'=>['type'=>'array','items'=>$str],'expected'=>$str],'required'=>['entity','action','set'],'additionalProperties'=>false]]],['operations']),false],
      ['crm_apply','Save an authorized preview atomically. Retrying the SAME token returns the same receipt without duplicate records. Report completion only on a saved receipt, then read records back with crm_get.',$schema(['preview_token'=>$str],['preview_token']),false],
    ];
    return array_map(fn($s)=>['name'=>$s[0],'description'=>$s[1],'inputSchema'=>$s[2],'annotations'=>['readOnlyHint'=>$s[3],'destructiveHint'=>false,'idempotentHint'=>$s[0]!=='crm_preview','openWorldHint'=>false],'securitySchemes'=>[['type'=>'oauth2','scopes'=>['crm.read','crm.write']]]],$specs);
}
function crm_mcp_call(string $name,array $a,array $user): array {
    $known=array_column(crm_mcp_tools(),null,'name'); if(!isset($known[$name])) throw new InvalidArgumentException('Unknown tool.');
    $schema=$known[$name]['inputSchema']; if(array_diff(array_keys($a),array_keys((array)$schema['properties'])) || array_diff($schema['required'],array_keys($a))) throw new InvalidArgumentException('Invalid arguments.');
    if(isset($a['entity']) && (!is_string($a['entity']) || !in_array($a['entity'],['contact','activity','task','test'],true))) throw new InvalidArgumentException('Invalid entity.');
    if($name==='crm_schema') { $fields=[]; foreach(['contact','activity','task','test'] as $e) $fields[$e]=crm_mcp_fields($e); return ['fields'=>$fields,'rules'=>['create'=>['contact','activity','task','test'],'update'=>['contact','activity','task','test'],'upsert'=>['contact'],'experiment_write'=>'CRM administrators only','updates'=>'Search/get, then match.id plus expected=current version. Existing permissions apply.','new_experiment'=>'Missing dates remain blank; status unconfirmed unless a real test date is supplied.','new_task'=>'company and title required','clear'=>'Only explicitly requested optional fields; unknown/blank OCR never deletes data.','translations'=>'Supply ja/en/pl text when known; do not fabricate.','workflow'=>'preview -> apply -> get. A preview is not a completed registration.'],'user'=>['id'=>(int)$user['id'],'name'=>$user['display_name'],'role'=>$user['role']]]; }
    if($name==='crm_get') { if(!is_int($a['id']) || $a['id']<1) throw new InvalidArgumentException('Invalid ID.'); return crm_mcp_record($a['entity'],$a['id']); }
    if($name==='crm_search') {
        if(!is_string($a['query']) || strlen($a['query'])>300) throw new InvalidArgumentException('Invalid query.');
        $table=crm_mcp_table($a['entity']); $title=['contact'=>'name','activity'=>'subject','task'=>'title','test'=>'title'][$a['entity']];
        $term='%'.strtr($a['query'],['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
        $q=db()->prepare("SELECT r.id,r.`$title` title,c.name company FROM `$table` r LEFT JOIN companies c ON c.id=r.company_id WHERE r.`$title` LIKE ? ESCAPE '!' OR c.name LIKE ? ESCAPE '!' ORDER BY r.id DESC LIMIT 51"); $q->execute([$term,$term]); $rows=$q->fetchAll(); return ['matches'=>array_slice($rows,0,50),'has_more'=>count($rows)>50];
    }
    if($name==='crm_preview') {
        $items=crm_mcp_prepare($a['operations'],$user); $token=crm_mcp_secret(); crm_mcp_put($token,'preview',['operations'=>$a['operations'],'items'=>$items],time()+600,$user);
        return ['preview_token'=>$token,'expires_in'=>600,'saved'=>false,'changes'=>array_map(fn($p)=>['entity'=>$p['entity'],'id'=>$p['id'],'before'=>$p['before'],'after'=>array_replace($p['before'],$p['set']),'related_task'=>$p['linked_task']??null],$items)];
    }
    if(!is_string($a['preview_token']) || !preg_match('/\A[A-Za-z0-9_-]{43}\z/',$a['preview_token'])) throw new InvalidArgumentException('Invalid preview token.');
    return crm_mcp_apply($a['preview_token'],$user);
}
function crm_mcp_connect_page(): void {
    $u=require_login(); if($u['must_change_password']) redirect('?page=change-password');
    if($u['role']==='admin') { crm_mcp_schema(); db()->prepare('DELETE FROM crm_mcp_state WHERE expires_at>0 AND expires_at<? LIMIT 500')->execute([time()]); }
    if($_SERVER['REQUEST_METHOD']==='POST') { check_csrf(); if(($_POST['action']??'')!=='mcp_revoke') { http_response_code(400); exit; } db()->prepare("DELETE FROM crm_mcp_state WHERE user_id=? AND kind IN ('grant','code','access','refresh','preview')")->execute([$u['id']]); redirect('?page=mcp-connect&revoked=1'); }
    header_html(ux('ChatGPT接続','Connect ChatGPT','Połącz ChatGPT'),$u);
    ?><section class="card"><p><?=h(ux('初回だけChatGPTに接続すると、会話から名刺・営業履歴・アクションを登録・修正できます。実験の変更は管理者に対応しています。','Connect once to create and update contacts, activities and actions from your conversations. Experiment changes require an administrator.','Połącz raz, aby dodawać i edytować kontakty, historię i zadania w rozmowie. Zmiany badań wymagają administratora.'))?></p>
    <label>Server URL<input readonly value="<?=h(CRM_MCP_URL)?>" onclick="this.select()"></label>
    <p><?=h(ux('ChatGPTのプラグイン → ＋ → カスタムMCPサーバーを追加。名前は「DAISHO CRM」、URLは上記、認証はOAuthを選択します。クライアントIDとシークレットは空欄です。接続時に通常のCRMアカウントでログインしてください。','ChatGPT Plugins → + → Add custom MCP server. Name: DAISHO CRM. Use the URL above and OAuth. Leave client ID and secret blank. Sign in with your normal CRM account when connecting.','ChatGPT: Plugins → + → Add custom MCP server. Nazwa: DAISHO CRM. Wpisz powyższy URL i wybierz OAuth. Client ID i secret pozostaw puste. Zaloguj się kontem CRM.'))?></p>
    <p><?=h(ux('ここを開いただけではChatGPTとの接続完了にはなりません。接続後に「CRMの登録内容を確認して」と依頼してください。','Opening this page does not complete the ChatGPT connection. After connecting, ask ChatGPT to check the CRM records.','Samo otwarcie tej strony nie kończy połączenia. Po połączeniu poproś ChatGPT o sprawdzenie wpisów CRM.'))?></p>
    <?php if(isset($_GET['revoked'])): ?><p><?=h(ux('接続を解除しました。','Connection revoked.','Połączenie odwołano.'))?></p><?php endif; ?>
    <form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="mcp_revoke"><button><?=h(ux('自分のChatGPT接続を解除','Revoke my ChatGPT connection','Odwołaj moje połączenie ChatGPT'))?></button></form></section></main></body></html><?php
}

function crm_mcp_exchange(array $p): array {
        foreach(['client_id','grant_type','resource'] as $k) if(!isset($p[$k]) || !is_string($p[$k])) throw new InvalidArgumentException('invalid_request');
        if($p['resource']!==CRM_MCP_URL || !crm_mcp_state($p['client_id'],'client')) throw new InvalidArgumentException('invalid_client');
        $isCode=$p['grant_type']==='authorization_code'; if(!$isCode && $p['grant_type']!=='refresh_token') throw new InvalidArgumentException('unsupported_grant_type');
        $secret=$p[$isCode?'code':'refresh_token']??''; if(!is_string($secret) || strlen($secret)!==43) throw new InvalidArgumentException('invalid_grant');
        $db=db(); $db->beginTransaction();
        try {
            $s=crm_mcp_state($secret,$isCode?'code':'refresh',true);
            if(!$s || $s['data']['client_id']!==$p['client_id'] || $s['data']['resource']!==$p['resource'] || !($u=crm_mcp_user($s))) throw new InvalidArgumentException('invalid_grant');
            $d=$s['data'];
            if($isCode) {
                if(!is_string($p['redirect_uri']??null) || $p['redirect_uri']!==$d['redirect_uri'] || !is_string($p['code_verifier']??null) || !crm_mcp_pkce($p['code_verifier'],$d['code_challenge'])) throw new InvalidArgumentException('invalid_grant');
                $db->prepare('DELETE FROM crm_mcp_state WHERE state_key=?')->execute([crm_mcp_key($secret)]);
                $grant=crm_mcp_secret(); crm_mcp_put($grant,'grant',['client_id'=>$p['client_id'],'resource'=>CRM_MCP_URL,'scope'=>CRM_MCP_SCOPE],time()+86400*30,$u);
            } else {
                $grant=$d['grant'];
                if(!empty($d['used'])) {
                    $db->prepare("DELETE FROM crm_mcp_state WHERE state_key=? AND kind='grant'")->execute([crm_mcp_key($grant)]); $db->commit(); throw new InvalidArgumentException('invalid_grant');
                }
                if(!crm_mcp_state($grant,'grant',true)) throw new InvalidArgumentException('invalid_grant');
                $d['used']=true; $db->prepare('UPDATE crm_mcp_state SET payload=? WHERE state_key=?')->execute([gpt_json($d),crm_mcp_key($secret)]);
            }
            $a=crm_mcp_secret(); $r=crm_mcp_secret(); $data=['grant'=>$grant,'client_id'=>$p['client_id'],'resource'=>CRM_MCP_URL,'scope'=>CRM_MCP_SCOPE];
            crm_mcp_put($a,'access',$data,time()+3600,$u); crm_mcp_put($r,'refresh',$data,time()+86400*30,$u); $db->commit();
            return ['access_token'=>$a,'token_type'=>'Bearer','expires_in'=>3600,'refresh_token'=>$r,'scope'=>CRM_MCP_SCOPE];
        } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }

}
