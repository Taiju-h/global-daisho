<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (($argv[1] ?? '') !== 'artur') { fwrite(STDERR, "Usage: php reset-crm-artur.php artur\n"); exit(1); }
try {
    $c = require '/etc/global-daisho/crm-db.php';
    $db = new PDO($c['dsn'], $c['user'], $c['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $q = $db->prepare('SELECT id,username,display_name,role FROM users WHERE BINARY username=?');
    $q->execute(['artur']);
    $u = $q->fetch();
    if (!$u || strtolower(trim($u['display_name'])) !== 'artur' || $u['role'] !== 'user') {
        fwrite(STDERR, "Artur account did not match. No changes made.\n"); exit(1);
    }
    if (!$db->query("SHOW COLUMNS FROM users LIKE 'auth_version'")->fetch()) {
        $db->exec('ALTER TABLE users ADD COLUMN auth_version BIGINT UNSIGNED NOT NULL DEFAULT 0');
    }
    $db->beginTransaction();
    $q = $db->prepare('UPDATE users SET password_hash=?,must_change_password=1,auth_version=auth_version+1 WHERE id=? AND BINARY username=? AND role=?');
    $q->execute([password_hash('0921', PASSWORD_DEFAULT), $u['id'], 'artur', 'user']);
    if ($q->rowCount() !== 1) throw new RuntimeException('Account changed during reset');
    $q = $db->prepare('SELECT password_hash,must_change_password FROM users WHERE id=?');
    $q->execute([$u['id']]);
    $saved = $q->fetch();
    if (!$saved || !password_verify('0921', $saved['password_hash']) || (int)$saved['must_change_password'] !== 1) {
        throw new RuntimeException('Password verification failed');
    }
    $db->commit();
    echo "SUCCESS: username=artur password=0921\nPassword change required after login.\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "Reset failed. No password change committed. Check database access and schema.\n");
    exit(1);
}
