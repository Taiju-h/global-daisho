<?php
declare(strict_types=1);
define('CRM_LIBRARY_ONLY',true);
require __DIR__.'/index.php';
require_once __DIR__.'/mcp-core.php';
header('Cache-Control: no-store');
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') crm_mcp_json(['error'=>'https_required'],400); header('Referrer-Policy: no-referrer'); header('X-Frame-Options: DENY');
$route=$_GET['route']??'';
try {
    if(!in_array($route,['register','token','authorize','resume'],true)) crm_mcp_json(['error'=>'not_found'],404);
    if(in_array($route,['register','token'],true)) {
        if($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); crm_mcp_json(['error'=>'method_not_allowed'],405); }
        crm_mcp_rate($route,30);
    }
    if($route==='register') {
        $raw=file_get_contents('php://input',false,null,0,16385); if(strlen($raw)>16384) crm_mcp_json(['error'=>'invalid_client_metadata'],400);
        $p=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
        if(!is_array($p) || ($p['redirect_uris']??null)!==['https://chatgpt.com/connector_platform_oauth_redirect'] || ($p['token_endpoint_auth_method']??'none')!=='none' || array_diff($p['grant_types']??['authorization_code','refresh_token'],['authorization_code','refresh_token']) || ($p['response_types']??['code'])!==['code']) crm_mcp_json(['error'=>'invalid_client_metadata'],400);
        $id=crm_mcp_secret(); $data=['client_id'=>$id,'client_name'=>'ChatGPT','redirect_uris'=>$p['redirect_uris'],'grant_types'=>['authorization_code','refresh_token'],'response_types'=>['code'],'token_endpoint_auth_method'=>'none'];
        crm_mcp_put($id,'client',$data,0); crm_mcp_json($data,201);
    }
    if($route==='token') {
        crm_mcp_json(crm_mcp_exchange($_POST));
    }

    // Browser-only authorization: reuse CRM login, never ask ChatGPT to collect passwords.
    session_start();
    if($route==='authorize' && $_SERVER['REQUEST_METHOD']==='GET') {
        $request=crm_mcp_authorization($_GET); $_SESSION['mcp_auth_request']=['request'=>$request,'expires'=>time()+600,'nonce'=>crm_mcp_secret()];
    } elseif($route!=='resume' && !($route==='authorize' && $_SERVER['REQUEST_METHOD']==='POST')) crm_mcp_json(['error'=>'invalid_request'],400);
    $pending=$_SESSION['mcp_auth_request']??null;
    if(!$pending || $pending['expires']<time()) { unset($_SESSION['mcp_auth_request'],$_SESSION['mcp_login_return']); crm_mcp_json(['error'=>'authorization_expired','message'=>'Restart the connection from ChatGPT.'],400); }
    $u=current_user(); if(!$u || $u['must_change_password']) { $_SESSION['mcp_login_return']=true; redirect('/salesdata/crm/?page='.($u?'change-password':'login')); }
    $request=crm_mcp_authorization($pending['request']+['response_type'=>'code','code_challenge_method'=>'S256']);
    if($_SERVER['REQUEST_METHOD']==='POST') {
        $csrf=$_POST['csrf']??null; $nonce=$_POST['nonce']??null;
        if(!is_string($csrf) || !hash_equals(csrf(),$csrf) || !is_string($nonce) || !hash_equals($pending['nonce'],$nonce)) crm_mcp_json(['error'=>'invalid_request'],400);
        unset($_SESSION['mcp_auth_request'],$_SESSION['mcp_login_return']);
        $reply=['state'=>$request['state'],'iss'=>CRM_MCP_BASE];
        if(($_POST['decision']??'')==='allow') { $code=crm_mcp_secret(); crm_mcp_put($code,'code',$request,time()+120,$u); $reply['code']=$code; }
        else $reply['error']='access_denied';
        redirect($request['redirect_uri'].'?'.http_build_query($reply,'','&',PHP_QUERY_RFC3986));
    }
    ?><!doctype html><html lang="<?=h(current_lang())?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Connect DAISHO CRM</title><link rel="stylesheet" href="style.css"><link rel="icon" href="favicon-admin.svg"></head><body><main class="wrap"><section class="card"><h1><?=h(ux('ChatGPTと接続','Connect ChatGPT','Połącz ChatGPT'))?></h1><p><?=h($u['display_name'])?> · <?=h($u['username'])?></p><p><?=h(ux('ChatGPTにCRMの閲覧と、あなたの権限内での名刺・営業履歴・アクションの登録・修正を許可します。実験の変更は管理者のみです。接続はCRMの「ChatGPT接続」から解除できます。','Allow ChatGPT to read CRM records and create/update contacts, activities and actions within your permissions. Experiment changes require an administrator. Revoke access under Connect ChatGPT in the CRM.','Zezwól ChatGPT odczytywać CRM oraz dodawać i edytować kontakty, historię i zadania w ramach Twoich uprawnień. Zmiany badań wymagają administratora. Dostęp można odwołać w CRM.'))?></p><form method="post" action="oauth.php?route=authorize"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="nonce" value="<?=h($pending['nonce'])?>"><button name="decision" value="allow"><?=h(ux('接続を許可','Allow connection','Zezwól na połączenie'))?></button> <button name="decision" value="deny"><?=h(ux('キャンセル','Cancel','Anuluj'))?></button></form></section></main></body></html><?php
} catch(InvalidArgumentException|JsonException $e) { crm_mcp_json(['error'=>in_array($e->getMessage(),['invalid_client','invalid_grant','unsupported_grant_type'],true)?$e->getMessage():'invalid_request','message'=>$e->getMessage()],400); }
catch(Throwable $e) { error_log('CRM OAuth failure: '.get_class($e)); crm_mcp_json(['error'=>'temporarily_unavailable','message'=>'Ask the CRM administrator to open ChatGPT connection setup.'],503); }
