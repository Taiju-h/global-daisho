<?php
/* GLOBAL DAISHO Git Deployer - runtime: /var/www/global-daisho-deployer */

date_default_timezone_set('Asia/Tokyo');
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

if (session_id() === '') {
    session_start();
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function secureEquals($known, $given)
{
    if (function_exists('hash_equals')) {
        return hash_equals((string)$known, (string)$given);
    }
    $known = (string)$known;
    $given = (string)$given;
    if (strlen($known) !== strlen($given)) {
        return false;
    }
    $result = 0;
    for ($i = 0, $length = strlen($known); $i < $length; $i++) {
        $result |= ord($known[$i]) ^ ord($given[$i]);
    }
    return $result === 0;
}

function createToken()
{
    if (function_exists('random_bytes')) {
        try {
            return bin2hex(random_bytes(32));
        } catch (Exception $e) {
        }
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        $bytes = openssl_random_pseudo_bytes(32);
        if ($bytes !== false) {
            return bin2hex($bytes);
        }
    }
    return hash('sha256', uniqid(mt_rand(), true));
}

function runHelper($action, &$exitCode)
{
    $allowed = array('status', 'diff', 'production', 'rollback');
    if (!in_array($action, $allowed, true)) {
        $exitCode = 126;
        return 'ERROR: Invalid helper action.';
    }
    if (!function_exists('exec')) {
        $exitCode = 127;
        return 'ERROR: PHP exec() is disabled.';
    }
    $sudo = is_executable('/usr/bin/sudo') ? '/usr/bin/sudo' : 'sudo';
    $helper = '/usr/local/sbin/global-daisho-deploy';
    $output = array();
    $exitCode = 127;
    exec($sudo . ' -n ' . escapeshellarg($helper) . ' ' . escapeshellarg($action) . ' 2>&1', $output, $exitCode);
    return implode("\n", $output);
}

function parseStatus($text)
{
    $status = array();
    $details = array();
    $inDetails = false;
    foreach (preg_split('/\r?\n/', (string)$text) as $line) {
        if ($line === '---DETAILS---') {
            $inDetails = true;
            continue;
        }
        if ($inDetails) {
            if ($line !== '') {
                $details[] = $line;
            }
            continue;
        }
        if (preg_match('/^([A-Z_]+)=(.*)$/', $line, $matches)) {
            $status[$matches[1]] = $matches[2];
        }
    }
    $status['DETAILS'] = implode("\n", $details);
    return $status;
}

function parseOperationOutput($text)
{
    $values = array('BEFORE' => 'unknown', 'AFTER' => 'unknown');
    foreach (preg_split('/\r?\n/', (string)$text) as $line) {
        if (preg_match('/^(BEFORE|AFTER)=([0-9a-f]{40})$/', $line, $matches)) {
            $values[$matches[1]] = $matches[2];
        }
    }
    return $values;
}

function getClientIp()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function readAllowedIps($path)
{
    if (!is_file($path)) {
        return array();
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return array();
    }
    $result = array();
    foreach ($lines as $line) {
        $ip = trim($line);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $result[$ip] = $ip;
        }
    }
    return array_values($result);
}

function writeAllowedIps($path, $ips)
{
    $valid = array();
    foreach ($ips as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $valid[$ip] = $ip;
        }
    }
    $valid = array_values($valid);
    sort($valid, SORT_STRING);
    $contents = count($valid) ? implode("\n", $valid) . "\n" : '';
    return file_put_contents($path, $contents, LOCK_EX) !== false;
}

function appendHistory($path, $entry)
{
    $json = json_encode($entry);
    return $json !== false && file_put_contents($path, $json . "\n", FILE_APPEND | LOCK_EX) !== false;
}

function readHistory($path, $limit)
{
    if (!is_file($path)) {
        return array();
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return array();
    }
    $rows = array();
    foreach (array_reverse($lines) as $line) {
        $row = json_decode($line, true);
        if (is_array($row)) {
            $rows[] = $row;
        }
        if (count($rows) >= $limit) {
            break;
        }
    }
    return $rows;
}

function clipOutput($text, $maxLength)
{
    $text = (string)$text;
    return strlen($text) <= $maxLength ? $text : substr($text, 0, $maxLength) . "\n... output truncated ...";
}

$expectedHost = 'global.daishokagaku.com';
$requestHost = isset($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : '';
$requestHost = preg_replace('/:\d+$/', '', $requestHost);
if ($requestHost !== $expectedHost) {
    header('HTTP/1.1 404 Not Found');
    exit('Not Found');
}

$authUser = '';
if (!empty($_SERVER['REMOTE_USER'])) {
    $authUser = $_SERVER['REMOTE_USER'];
} elseif (!empty($_SERVER['PHP_AUTH_USER'])) {
    $authUser = $_SERVER['PHP_AUTH_USER'];
}
if ($authUser === '') {
    header('HTTP/1.1 403 Forbidden');
    exit('GLOBAL DAISHO Deployer: Basic authentication is required.');
}

$translations = array(
    'ja' => array(
        'title' => 'GLOBAL DAISHO デプロイヤー',
        'subtitle' => 'global.daishokagaku.com / GitHub 本番反映',
        'environment' => '環境', 'production' => '本番', 'target' => '対象', 'path' => 'パス',
        'branch' => 'ブランチ', 'commit' => '現在のコミット', 'commit_time' => 'コミット日時',
        'client_ip' => '接続元IP', 'user' => '認証ユーザー', 'working_tree' => '作業ツリー',
        'clean' => '変更なし', 'dirty' => '未反映のローカル変更あり', 'actions' => '操作',
        'diff' => 'GitHubとの差分確認', 'deploy' => '本番へ反映', 'rollback' => '1つ前へ戻す',
        'reload' => '表示更新', 'history' => 'デプロイ・ロールバック履歴', 'no_history' => '履歴はまだありません。',
        'time' => '日時', 'operation' => '操作', 'from' => '変更前', 'to' => '変更後', 'result' => '結果',
        'ip_control' => 'デプロイ許可IP', 'ip_empty' => '未登録のため、Basic認証済みユーザーを許可しています。',
        'add_current_ip' => '現在のIPを追加', 'remove' => '削除', 'allowed' => '許可済み',
        'blocked' => '未許可（反映・ロールバック不可）', 'output' => '実行結果',
        'confirm_deploy' => 'GitHubのmainを本番へ反映します。よろしいですか？',
        'confirm_rollback' => '本番を1コミット前へ戻します。よろしいですか？',
        'confirm_remove' => 'このIPを削除しますか？', 'github' => 'GitHubを開く', 'site' => '公開サイトを開く',
        'note' => '画面表示だけではfetch・merge・resetを実行しません。', 'tracked' => '追跡対象の変更',
    ),
    'en' => array(
        'title' => 'GLOBAL DAISHO Deployer',
        'subtitle' => 'global.daishokagaku.com / GitHub production deployment',
        'environment' => 'Environment', 'production' => 'Production', 'target' => 'Target', 'path' => 'Path',
        'branch' => 'Branch', 'commit' => 'Current commit', 'commit_time' => 'Commit time',
        'client_ip' => 'Client IP', 'user' => 'Authenticated user', 'working_tree' => 'Working tree',
        'clean' => 'Clean', 'dirty' => 'Tracked local changes detected', 'actions' => 'Actions',
        'diff' => 'Compare with GitHub', 'deploy' => 'Deploy to production', 'rollback' => 'Roll back one commit',
        'reload' => 'Refresh status', 'history' => 'Deployment and rollback history', 'no_history' => 'No history yet.',
        'time' => 'Time', 'operation' => 'Operation', 'from' => 'From', 'to' => 'To', 'result' => 'Result',
        'ip_control' => 'Deployment IP allowlist', 'ip_empty' => 'Empty: authenticated users are currently allowed.',
        'add_current_ip' => 'Add current IP', 'remove' => 'Remove', 'allowed' => 'Allowed',
        'blocked' => 'Not allowed (deploy and rollback disabled)', 'output' => 'Command result',
        'confirm_deploy' => 'Deploy GitHub main to production?',
        'confirm_rollback' => 'Roll production back by one commit?',
        'confirm_remove' => 'Remove this IP?', 'github' => 'Open GitHub', 'site' => 'Open public site',
        'note' => 'Opening this page does not run fetch, merge, or reset.', 'tracked' => 'Tracked changes',
    ),
);

$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : (isset($_SESSION['global_daisho_deploy_lang']) ? $_SESSION['global_daisho_deploy_lang'] : 'ja');
if ($lang !== 'en') {
    $lang = 'ja';
}
$_SESSION['global_daisho_deploy_lang'] = $lang;

function t($key)
{
    global $translations, $lang;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

$stateDir = '/var/lib/global-daisho-deployer';
$historyFile = $stateDir . '/history.jsonl';
$allowedIpFile = $stateDir . '/allowed-ips.txt';
$bootError = '';
if (!is_dir($stateDir) || !is_writable($stateDir)) {
    $bootError = 'State directory is unavailable: ' . $stateDir;
}

if (empty($_SESSION['global_daisho_deploy_csrf'])) {
    $_SESSION['global_daisho_deploy_csrf'] = createToken();
}
$csrfToken = $_SESSION['global_daisho_deploy_csrf'];
$clientIp = getClientIp();
$allowedIps = readAllowedIps($allowedIpFile);
$ipAllowed = count($allowedIps) === 0 || in_array($clientIp, $allowedIps, true);
$message = '';
$messageType = 'info';
$commandOutput = '';

$statusExit = 0;
$statusOutput = runHelper('status', $statusExit);
$status = $statusExit === 0 ? parseStatus($statusOutput) : array();
if ($statusExit !== 0) {
    $bootError = $statusOutput;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bootError === '') {
    $postedToken = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    if (!secureEquals($csrfToken, $postedToken)) {
        $message = 'Invalid CSRF token. Reload the page.';
        $messageType = 'error';
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'add_ip') {
            if (!filter_var($clientIp, FILTER_VALIDATE_IP)) {
                $message = 'Invalid client IP.';
                $messageType = 'error';
            } else {
                if (!in_array($clientIp, $allowedIps, true)) {
                    $allowedIps[] = $clientIp;
                }
                if (writeAllowedIps($allowedIpFile, $allowedIps)) {
                    $message = 'IP added: ' . $clientIp;
                    $messageType = 'success';
                } else {
                    $message = 'Could not update the IP allowlist.';
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'remove_ip') {
            $removeIp = isset($_POST['ip']) ? trim($_POST['ip']) : '';
            if (!filter_var($removeIp, FILTER_VALIDATE_IP)) {
                $message = 'Invalid IP address.';
                $messageType = 'error';
            } else {
                $remaining = array();
                foreach ($allowedIps as $ip) {
                    if ($ip !== $removeIp) {
                        $remaining[] = $ip;
                    }
                }
                if (writeAllowedIps($allowedIpFile, $remaining)) {
                    $message = 'IP removed: ' . $removeIp;
                    $messageType = 'success';
                } else {
                    $message = 'Could not update the IP allowlist.';
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'diff') {
            $exitCode = 0;
            $commandOutput = runHelper('diff', $exitCode);
            $message = $exitCode === 0 ? 'Comparison completed.' : 'Comparison failed.';
            $messageType = $exitCode === 0 ? 'success' : 'error';
        } elseif ($action === 'deploy' || $action === 'rollback') {
            $allowedIps = readAllowedIps($allowedIpFile);
            $ipAllowed = count($allowedIps) === 0 || in_array($clientIp, $allowedIps, true);
            if (!$ipAllowed) {
                $message = 'This IP is not allowed to deploy: ' . $clientIp;
                $messageType = 'error';
            } else {
                $helperAction = $action === 'deploy' ? 'production' : 'rollback';
                $beforeFallback = isset($status['COMMIT_FULL']) ? $status['COMMIT_FULL'] : 'unknown';
                $exitCode = 0;
                $commandOutput = runHelper($helperAction, $exitCode);
                $operation = parseOperationOutput($commandOutput);
                if ($operation['BEFORE'] === 'unknown') {
                    $operation['BEFORE'] = $beforeFallback;
                }
                appendHistory($historyFile, array(
                    'time' => date('Y-m-d H:i:s T'), 'action' => $action,
                    'from' => $operation['BEFORE'], 'to' => $operation['AFTER'],
                    'user' => $authUser, 'ip' => $clientIp,
                    'result' => $exitCode === 0 ? 'success' : 'failed',
                ));
                $message = $exitCode === 0
                    ? ($action === 'deploy' ? 'Deployment completed.' : 'Rollback completed.')
                    : ($action === 'deploy' ? 'Deployment failed.' : 'Rollback failed.');
                $messageType = $exitCode === 0 ? 'success' : 'error';
            }
        } else {
            $message = 'Unknown action.';
            $messageType = 'error';
        }
    }

    $allowedIps = readAllowedIps($allowedIpFile);
    $ipAllowed = count($allowedIps) === 0 || in_array($clientIp, $allowedIps, true);
    $statusExit = 0;
    $statusOutput = runHelper('status', $statusExit);
    if ($statusExit === 0) {
        $status = parseStatus($statusOutput);
    }
}

$branch = isset($status['BRANCH']) ? $status['BRANCH'] : 'unknown';
$commit = isset($status['COMMIT_SHORT']) ? $status['COMMIT_SHORT'] : 'unknown';
$commitTime = isset($status['COMMIT_TIME']) ? $status['COMMIT_TIME'] : 'unknown';
$treeClean = isset($status['TREE_CLEAN']) && $status['TREE_CLEAN'] === '1';
$trackedDetails = isset($status['DETAILS']) ? $status['DETAILS'] : '';
$history = readHistory($historyFile, 30);
?>
<!DOCTYPE html>
<html lang="<?php echo h($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h(t('title')); ?></title>
    <style>
        :root{--primary:#0b4f83;--primary2:#08375b;--bg:#f2f6f9;--card:#fff;--text:#17232d;--muted:#667684;--line:#d8e2e9;--ok:#18794e;--danger:#bd3038}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:"Segoe UI","Noto Sans JP",sans-serif;line-height:1.5}.wrap{max-width:1180px;margin:0 auto;padding:28px 20px 50px}
        header{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}.brand h1{margin:0;color:var(--primary);font-size:28px}.brand p{margin:4px 0 0;color:var(--muted)}
        .language{display:flex;gap:6px;flex:0 0 auto}.language a{padding:7px 11px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--text);text-decoration:none;font-size:13px}.language a.active{background:var(--primary);border-color:var(--primary);color:#fff}
        .top-links{display:flex;gap:14px;margin-top:10px}.top-links a{color:var(--primary)}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.card{background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 18px rgba(17,45,65,.06);padding:20px}.card.full{grid-column:1/-1}.card h2{margin:0 0 15px;font-size:18px}
        .facts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.fact{padding:12px;background:#f8fafc;border:1px solid #e4ebf0;border-radius:9px}.fact small{display:block;color:var(--muted);margin-bottom:3px}.fact strong{display:block;overflow-wrap:anywhere}.status-ok{color:var(--ok)}.status-bad{color:var(--danger)}
        .notice{margin-bottom:18px;padding:13px 15px;border-radius:10px;border:1px solid;white-space:pre-wrap}.notice.info{background:#eef5ff;border-color:#cddfff}.notice.success{background:#edf9f1;border-color:#bfe5cb;color:#12633d}.notice.error{background:#fff0f0;border-color:#f0c2c2;color:#9d2323}
        .actions{display:flex;flex-wrap:wrap;gap:10px}.button,button{appearance:none;border:0;border-radius:9px;padding:11px 15px;font-weight:700;cursor:pointer;text-decoration:none;font-size:14px}.primary{background:var(--primary);color:#fff}.secondary{background:#e5eef5;color:var(--primary2)}.danger{background:#ffe9e9;color:#a82323}.outline{background:#fff;color:var(--text);border:1px solid var(--line)}button:disabled{opacity:.45;cursor:not-allowed}.hint{margin:12px 0 0;color:var(--muted);font-size:13px}
        pre{margin:0;padding:15px;background:#12202a;color:#e8f3fa;border-radius:10px;overflow:auto;max-height:460px;white-space:pre-wrap;word-break:break-word;font:13px/1.55 Consolas,monospace}.ip-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid var(--line)}.ip-row:last-child{border-bottom:0}.inline{display:inline}
        table{width:100%;border-collapse:collapse;font-size:13px}th,td{padding:10px 8px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top}th{color:var(--muted);font-weight:600}.sha{font-family:Consolas,monospace;white-space:nowrap}.meta{color:var(--muted);font-size:12px}
        @media(max-width:760px){.wrap{padding:18px 12px 35px}.brand h1{font-size:22px}.grid{grid-template-columns:1fr}.card.full{grid-column:auto}.facts{grid-template-columns:1fr}.table-wrap{overflow-x:auto}.actions{flex-direction:column}.actions form,.actions .button,.actions button{width:100%}}
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div class="brand">
            <h1><?php echo h(t('title')); ?></h1>
            <p><?php echo h(t('subtitle')); ?></p>
            <div class="top-links">
                <a href="https://global.daishokagaku.com/" target="_blank" rel="noopener noreferrer"><?php echo h(t('site')); ?></a>
                <a href="https://github.com/Taiju-h/global-daisho" target="_blank" rel="noopener noreferrer"><?php echo h(t('github')); ?></a>
            </div>
        </div>
        <nav class="language" aria-label="Language">
            <a href="?lang=ja" class="<?php echo $lang === 'ja' ? 'active' : ''; ?>">日本語</a>
            <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">English</a>
        </nav>
    </header>

    <?php if ($bootError !== ''): ?><div class="notice error"><?php echo h($bootError); ?></div><?php endif; ?>
    <?php if ($message !== ''): ?><div class="notice <?php echo h($messageType); ?>"><?php echo h($message); ?></div><?php endif; ?>

    <div class="grid">
        <section class="card full">
            <h2>Status</h2>
            <div class="facts">
                <div class="fact"><small><?php echo h(t('environment')); ?></small><strong><?php echo h(t('production')); ?></strong></div>
                <div class="fact"><small><?php echo h(t('target')); ?></small><strong>global.daishokagaku.com</strong></div>
                <div class="fact"><small><?php echo h(t('path')); ?></small><strong>/var/www/daisho</strong></div>
                <div class="fact"><small><?php echo h(t('branch')); ?></small><strong><?php echo h($branch); ?></strong></div>
                <div class="fact"><small><?php echo h(t('commit')); ?></small><strong class="sha"><?php echo h($commit); ?></strong></div>
                <div class="fact"><small><?php echo h(t('commit_time')); ?></small><strong><?php echo h($commitTime); ?></strong></div>
                <div class="fact"><small><?php echo h(t('client_ip')); ?></small><strong><?php echo h($clientIp); ?></strong></div>
                <div class="fact"><small><?php echo h(t('user')); ?></small><strong><?php echo h($authUser); ?></strong></div>
                <div class="fact"><small><?php echo h(t('working_tree')); ?></small><strong class="<?php echo $treeClean ? 'status-ok' : 'status-bad'; ?>"><?php echo h($treeClean ? t('clean') : t('dirty')); ?></strong></div>
                <div class="fact"><small>IP</small><strong class="<?php echo $ipAllowed ? 'status-ok' : 'status-bad'; ?>"><?php echo h($ipAllowed ? t('allowed') : t('blocked')); ?></strong></div>
            </div>
        </section>

        <section class="card full">
            <h2><?php echo h(t('actions')); ?></h2>
            <div class="actions">
                <form method="post"><input type="hidden" name="csrf" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="action" value="diff"><button type="submit" class="secondary" <?php echo $bootError !== '' ? 'disabled' : ''; ?>><?php echo h(t('diff')); ?></button></form>
                <form method="post" onsubmit="return confirm(<?php echo h(json_encode(t('confirm_deploy'))); ?>);"><input type="hidden" name="csrf" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="action" value="deploy"><button type="submit" class="primary" <?php echo ($bootError !== '' || !$treeClean || !$ipAllowed) ? 'disabled' : ''; ?>><?php echo h(t('deploy')); ?></button></form>
                <form method="post" onsubmit="return confirm(<?php echo h(json_encode(t('confirm_rollback'))); ?>);"><input type="hidden" name="csrf" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="action" value="rollback"><button type="submit" class="danger" <?php echo ($bootError !== '' || !$treeClean || !$ipAllowed) ? 'disabled' : ''; ?>><?php echo h(t('rollback')); ?></button></form>
                <a class="button outline" href="?lang=<?php echo h($lang); ?>"><?php echo h(t('reload')); ?></a>
            </div>
            <p class="hint"><?php echo h(t('note')); ?></p>
        </section>

        <?php if ($commandOutput !== ''): ?><section class="card full"><h2><?php echo h(t('output')); ?></h2><pre><?php echo h(clipOutput($commandOutput, 40000)); ?></pre></section><?php endif; ?>

        <section class="card">
            <h2><?php echo h(t('ip_control')); ?></h2>
            <?php if (!count($allowedIps)): ?><p class="hint"><?php echo h(t('ip_empty')); ?></p><?php endif; ?>
            <?php foreach ($allowedIps as $ip): ?><div class="ip-row"><span class="sha"><?php echo h($ip); ?></span><form method="post" class="inline" onsubmit="return confirm(<?php echo h(json_encode(t('confirm_remove'))); ?>);"><input type="hidden" name="csrf" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="action" value="remove_ip"><input type="hidden" name="ip" value="<?php echo h($ip); ?>"><button type="submit" class="danger"><?php echo h(t('remove')); ?></button></form></div><?php endforeach; ?>
            <?php if (filter_var($clientIp, FILTER_VALIDATE_IP) && !in_array($clientIp, $allowedIps, true)): ?><form method="post" style="margin-top:14px"><input type="hidden" name="csrf" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="action" value="add_ip"><button type="submit" class="secondary"><?php echo h(t('add_current_ip')); ?> (<?php echo h($clientIp); ?>)</button></form><?php endif; ?>
        </section>

        <section class="card"><h2><?php echo h(t('tracked')); ?></h2><?php if ($treeClean): ?><p class="status-ok"><?php echo h(t('clean')); ?></p><?php else: ?><pre><?php echo h(clipOutput($trackedDetails, 10000)); ?></pre><?php endif; ?></section>

        <section class="card full">
            <h2><?php echo h(t('history')); ?></h2>
            <?php if (!count($history)): ?><p class="hint"><?php echo h(t('no_history')); ?></p><?php else: ?>
            <div class="table-wrap"><table><thead><tr><th><?php echo h(t('time')); ?></th><th><?php echo h(t('operation')); ?></th><th><?php echo h(t('from')); ?></th><th><?php echo h(t('to')); ?></th><th><?php echo h(t('user')); ?> / IP</th><th><?php echo h(t('result')); ?></th></tr></thead><tbody>
            <?php foreach ($history as $row): ?><tr><td><?php echo h(isset($row['time']) ? $row['time'] : ''); ?></td><td><?php echo h(isset($row['action']) ? $row['action'] : ''); ?></td><td class="sha"><?php echo h(isset($row['from']) ? substr($row['from'], 0, 10) : ''); ?></td><td class="sha"><?php echo h(isset($row['to']) ? substr($row['to'], 0, 10) : ''); ?></td><td><?php echo h(isset($row['user']) ? $row['user'] : ''); ?><div class="meta"><?php echo h(isset($row['ip']) ? $row['ip'] : ''); ?></div></td><td><?php echo h(isset($row['result']) ? $row['result'] : ''); ?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </section>
    </div>
</div>
</body>
</html>
