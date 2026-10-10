<?php
declare(strict_types=1);
define('CRM_LIBRARY_ONLY',true);
require __DIR__.'/index.php';
require_once __DIR__.'/mcp-core.php';
header('Cache-Control: no-store');
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') crm_mcp_json(['error'=>'https_required'],400);
$origin=$_SERVER['HTTP_ORIGIN']??null;
if($origin!==null && !in_array($origin,['https://chatgpt.com',CRM_MCP_BASE],true)) crm_mcp_json(['error'=>'forbidden_origin'],403);
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); crm_mcp_json(['error'=>'method_not_allowed'],405); }
if(strtolower(trim(explode(';',$_SERVER['CONTENT_TYPE']??'')[0]))!=='application/json') crm_mcp_json(['error'=>'unsupported_media_type'],415);
$raw=file_get_contents('php://input',false,null,0,100001); if(strlen($raw)>100000) crm_mcp_json(['error'=>'request_too_large'],413);
try { $rpc=json_decode($raw,true,64,JSON_THROW_ON_ERROR); }
catch(JsonException $e) { crm_mcp_json(['jsonrpc'=>'2.0','id'=>null,'error'=>['code'=>-32700,'message'=>'Parse error']],400); }
if(!is_array($rpc) || array_is_list($rpc) || ($rpc['jsonrpc']??'')!=='2.0' || !is_string($rpc['method']??null) || (isset($rpc['id']) && !is_int($rpc['id']) && !is_string($rpc['id']))) crm_mcp_json(['jsonrpc'=>'2.0','id'=>null,'error'=>['code'=>-32600,'message'=>'Invalid request']],400);
$id=$rpc['id']??null; $method=$rpc['method'];
if($method==='notifications/initialized' || $method==='notifications/cancelled') { http_response_code(202); exit; }
if($id===null) { http_response_code(202); exit; }
$reply=fn($r)=>crm_mcp_json(['jsonrpc'=>'2.0','id'=>$id,'result'=>$r]);
if($method==='initialize') {
    $requested=$rpc['params']['protocolVersion']??'';
    $version=in_array($requested,['2024-11-05','2025-03-26','2025-06-18','2025-11-25'],true)?$requested:'2025-06-18';
    $reply(['protocolVersion'=>$version,'capabilities'=>['tools'=>['listChanged'=>false]],'serverInfo'=>['name'=>'daisho-crm','version'=>'1.0.0'],'instructions'=>'Use crm_schema, search/get, preview, apply, then get to verify. Report saved only after a committed receipt. Never infer unreadable OCR or confidential chemistry. No raw SQL, shell or email sending tools are available.']);
}
if($method==='ping') $reply(new stdClass());
if($method==='tools/list') $reply(['tools'=>crm_mcp_tools()]);
if($method!=='tools/call') crm_mcp_json(['jsonrpc'=>'2.0','id'=>$id,'error'=>['code'=>-32601,'message'=>'Method not found']]);
try {
    $auth=$_SERVER['HTTP_AUTHORIZATION']??$_SERVER['REDIRECT_HTTP_AUTHORIZATION']??'';
    $s=null; $u=null;
    if(preg_match('/\ABearer ([A-Za-z0-9_-]{43})\z/i',$auth,$m)) $s=crm_mcp_state($m[1],'access');
    if($s && $s['data']['resource']===CRM_MCP_URL && $s['data']['scope']===CRM_MCP_SCOPE && crm_mcp_state($s['data']['grant'],'grant')) $u=crm_mcp_user($s);
    if(!$u) { header('WWW-Authenticate: Bearer resource_metadata="'.CRM_MCP_BASE.'/.well-known/oauth-protected-resource", scope="'.CRM_MCP_SCOPE.'"'); crm_mcp_json(['error'=>'invalid_token'],401); }
    crm_mcp_rate('tools:'.$u['id'],120);
    $params=$rpc['params']??[]; $args=$params['arguments']??[];
    if(!is_string($params['name']??null) || !is_array($args)) throw new InvalidArgumentException('Invalid tool arguments.');
    $result=crm_mcp_call($params['name'],$args,$u); $reply(['content'=>[['type'=>'text','text'=>gpt_json($result)]],'structuredContent'=>$result,'isError'=>false]);
} catch(InvalidArgumentException|JsonException $e) { $reply(['content'=>[['type'=>'text','text'=>$e->getMessage()]],'isError'=>true]); }
catch(Throwable $e) { error_log('CRM MCP failure: '.get_class($e)); $reply(['content'=>[['type'=>'text','text'=>'CRM operation failed. Do not report completion. If an apply response was lost, retry the SAME preview token to retrieve its receipt.']],'isError'=>true]); }
