<?php
declare(strict_types=1);

/**
 * Integration tests for authentication, authorization, migration tables and
 * audit behaviour. Requires a scratch MySQL database:
 *
 *   TEST_DB_HOST=127.0.0.1 TEST_DB_NAME=kynos_test TEST_DB_USER=... TEST_DB_PASS=...
 *   php tests/integration.php
 *
 * The named database is WIPED (tables dropped and recreated). Never point this
 * at production. Exits 0 with "SKIPPED" when the variables are not set.
 */

$required = ['TEST_DB_HOST', 'TEST_DB_NAME', 'TEST_DB_USER'];
foreach ($required as $key) {
    if (getenv($key) === false || getenv($key) === '') {
        echo "SKIPPED: set TEST_DB_HOST, TEST_DB_NAME, TEST_DB_USER (and TEST_DB_PASS) to run integration tests.\n";
        exit(0);
    }
}

putenv('DB_HOST=' . getenv('TEST_DB_HOST'));
putenv('DB_PORT=' . (getenv('TEST_DB_PORT') ?: '3306'));
putenv('DB_NAME=' . getenv('TEST_DB_NAME'));
putenv('DB_USER=' . getenv('TEST_DB_USER'));
putenv('DB_PASS=' . (getenv('TEST_DB_PASS') ?: ''));
putenv('APP_KEY=' . (getenv('TEST_APP_KEY') ?: 'integration-test-key'));

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/audit.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/settings.php';
require_once __DIR__ . '/../app/operations.php';

if (!isset($_SESSION)) {
    $_SESSION = [];
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "  ok  $name\n";
    } else {
        $fail++;
        echo "FAIL  $name" . ($detail !== '' ? " — $detail" : '') . "\n";
    }
}

function expect_thrown(string $name, callable $fn, int $status = 403): void
{
    $thrown = false;
    try {
        $fn();
    } catch (AuthException $error) {
        $thrown = $error->status === $status;
    }
    check($name, $thrown, 'expected AuthException with status ' . $status);
}

echo "== Schema setup (mirrors database-migration-v1.sql) ==\n";
db()->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (['audit_log', 'settings', 'login_attempts', 'admin_users', 'customer_enquiries', 'solar_assessments'] as $table) {
    db()->exec("DROP TABLE IF EXISTS $table");
}
db()->exec('SET FOREIGN_KEY_CHECKS=1');

db()->exec("CREATE TABLE admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','staff') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

db()->exec("CREATE TABLE login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    account_key VARCHAR(190) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL,
    KEY idx_login_attempts_account (account_key, attempted_at),
    KEY idx_login_attempts_ip (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

db()->exec("CREATE TABLE settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_by VARCHAR(190) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

db()->exec("CREATE TABLE audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT UNSIGNED DEFAULT NULL,
    actor_email VARCHAR(190) NOT NULL DEFAULT '',
    actor_role VARCHAR(20) NOT NULL DEFAULT '',
    action VARCHAR(60) NOT NULL,
    entity VARCHAR(40) NOT NULL,
    entity_id VARCHAR(64) NOT NULL DEFAULT '',
    details TEXT DEFAULT NULL,
    ip_hash CHAR(64) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_log_created (created_at),
    KEY idx_audit_log_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

db()->exec("CREATE TRIGGER audit_log_block_update BEFORE UPDATE ON audit_log FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only'");
db()->exec("CREATE TRIGGER audit_log_block_delete BEFORE DELETE ON audit_log FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only'");

db()->exec("CREATE TABLE customer_enquiries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    phone VARCHAR(24) NOT NULL,
    email VARCHAR(120) NULL,
    location VARCHAR(100) NOT NULL,
    property_type VARCHAR(40) NOT NULL,
    usage_mode VARCHAR(40) NOT NULL,
    notes VARCHAR(500) NOT NULL DEFAULT '',
    running_load_w DECIMAL(12,2) NULL,
    daily_energy_kwh DECIMAL(12,2) NULL,
    inverter_kva DECIMAL(10,2) NULL,
    battery_kwh DECIMAL(10,2) NULL,
    panel_count INT UNSIGNED NULL,
    recommended_package VARCHAR(80) NOT NULL,
    appliances_json JSON NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    user_agent VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('new','contacted','quoted','won','closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  ok  schema created\n";

$auditCount = static function (string $action): int {
    $statement = db()->prepare('SELECT COUNT(*) FROM audit_log WHERE action = ?');
    $statement->execute([$action]);
    return (int) $statement->fetchColumn();
};

echo "\n== First super administrator bootstrap ==\n";
check('weak bootstrap password refused on empty table', !bootstrap_first_super_admin('Owner', 'owner@kynossolutions.com', 'short')['ok']);
check('empty table allows bootstrap', bootstrap_first_super_admin('Owner', 'owner@kynossolutions.com', 'owner-password-1')['ok']);
check('bootstrap audited', $auditCount('user.bootstrapped') === 1);
check('second bootstrap refused', !bootstrap_first_super_admin('Other', 'other@kynossolutions.com', 'another-password')['ok']);
$owner = find_admin_by_email('owner@kynossolutions.com');
check('owner exists with super_admin role', $owner !== null && $owner['role'] === 'super_admin');

echo "\n== Login, throttling and sessions ==\n";
$good = attempt_login('owner@kynossolutions.com', 'owner-password-1');
check('valid credentials accepted', $good['ok']);
login_user($good['user']);
check('login audited', $auditCount('auth.login') === 1);
check('wrong password refused', !attempt_login('owner@kynossolutions.com', 'wrong-password-xx')['ok']);
check('failure audited', $auditCount('auth.login_failed') === 1);
for ($i = 0; $i < 4; $i++) {
    attempt_login('owner@kynossolutions.com', 'wrong-password-xx');
}
check('account blocked after five failures', login_throttle_blocked('owner@kynossolutions.com'));
check('blocked login refused even with correct password', !attempt_login('owner@kynossolutions.com', 'owner-password-1')['ok']);
check('blocked attempt audited', $auditCount('auth.login_blocked') >= 1);
clear_login_failures('owner@kynossolutions.com');
check('throttle cleared after failures expire', !login_throttle_blocked('owner@kynossolutions.com'));
check('login works again after clearing', attempt_login('owner@kynossolutions.com', 'owner-password-1')['ok']);

check('current_user resolves signed-in user', current_user() !== null && current_user()['email'] === 'owner@kynossolutions.com');
$_SESSION['auth_started_at'] = time() - AUTH_ABSOLUTE_SECONDS - 1;
check('absolute session expiry signs user out', current_user() === null);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$again = attempt_login('owner@kynossolutions.com', 'owner-password-1');
login_user($again['user']);

$staffResult = team_create_user($owner, 'Staff Member', 'staff@kynossolutions.com', ROLE_STAFF, 'staff-password-12');
check('super admin can create staff account', $staffResult['ok']);
$staff = find_admin_by_email('staff@kynossolutions.com');
$staffLogin = attempt_login('staff@kynossolutions.com', 'staff-password-12');
check('staff can sign in', $staffLogin['ok']);
login_user($staffLogin['user']);
team_set_active($owner, (int) $staff['id'], false);
check('disabled account refused at login', !attempt_login('staff@kynossolutions.com', 'staff-password-12')['ok']);
check('disabled login audited', $auditCount('auth.login_disabled') >= 1);
team_set_active($owner, (int) $staff['id'], true);
check('re-enabled account can sign in', attempt_login('staff@kynossolutions.com', 'staff-password-12')['ok']);

echo "\n== CSRF ==\n";
$_POST['csrf'] = csrf_token();
$csrfOk = true;
try {
    verify_csrf();
} catch (AuthException) {
    $csrfOk = false;
}
check('valid csrf accepted', $csrfOk);
expect_thrown('invalid csrf rejected with 419', static function (): void {
    $_POST['csrf'] = 'forged-token';
    verify_csrf();
}, 419);
unset($_POST['csrf']);

echo "\n== Server-side authorization on operations ==\n";
$staffUser = find_admin_by_email('staff@kynossolutions.com');
$adminResult = team_create_user($owner, 'Admin Person', 'admin@kynossolutions.com', ROLE_ADMIN, 'admin-password-12');
check('super admin can create administrator', $adminResult['ok']);
$adminUser = find_admin_by_email('admin@kynossolutions.com');

expect_thrown('staff cannot save settings', static function () use ($staffUser): void {
    settings_save($staffUser, ['phone' => '053 040 9118']);
});
expect_thrown('staff cannot create users', static function () use ($staffUser): void {
    team_create_user($staffUser, 'X', 'x2@kynossolutions.com', ROLE_STAFF, 'another-password-1');
});
expect_thrown('admin cannot manage users', static function () use ($adminUser): void {
    team_create_user($adminUser, 'X', 'x3@kynossolutions.com', ROLE_STAFF, 'another-password-1');
});
expect_thrown('staff cannot change user roles', static function () use ($staffUser): void {
    team_set_role($staffUser, 1, ROLE_ADMIN);
});

db()->prepare('INSERT INTO customer_enquiries (name, phone, location, property_type, usage_mode, recommended_package, appliances_json, ip_hash) VALUES (?,?,?,?,?,?,?,?)')
    ->execute(['Ama Test', '0244111222', 'Accra', 'Home', 'hybrid', 'Bronze', '[]', 'hash']);
$customerId = (int) db()->lastInsertId();

check('staff can update enquiry status', customer_set_status($staffUser, $customerId, 'contacted')['ok']);
check('status change audited', $auditCount('customer.status_changed') === 1);
check('unknown status refused', !customer_set_status($staffUser, $customerId, 'bogus')['ok']);
check('staff can list customers', count(customer_list()) === 1);
check('staff can search customers', count(customer_list('', 'Ama')) === 1);

echo "\n== Content and settings with audit ==\n";
$save = settings_save($adminUser, [
    'page_home_hero_intro' => 'Clean power, straight from the sun.',
    'social_facebook' => 'https://facebook.com/kynossolutions',
]);
check('admin can publish content', $save['ok'] && in_array('page_home_hero_intro', $save['changed'], true));
check('settings change audited with keys', $auditCount('settings.updated') >= 2);
settings_all_reset();
check('new value readable', setting('page_home_hero_intro') === 'Clean power, straight from the sun.');
check('http social url refused', !settings_save($adminUser, ['social_x' => 'http://x.com/kynos'])['ok']);
check('html page copy refused', !settings_save($adminUser, ['page_home_cta_heading' => '<b>Hi</b>'])['ok']);
check('unknown key refused', !settings_save($adminUser, ['rm -rf' => 'nope'])['ok']);

echo "\n== Final super administrator protection ==\n";
check('last super cannot be demoted by role change', !team_set_role($owner, (int) $owner['id'], ROLE_ADMIN)['ok']);
check('last super cannot be disabled', !team_set_active($owner, (int) $owner['id'], false)['ok']);
$secondSuper = team_create_user($owner, 'Second Super', 'super2@kynossolutions.com', ROLE_SUPER_ADMIN, 'super-password-12');
check('second super admin created', $secondSuper['ok']);
check('super demotion allowed once another super exists', team_set_role($owner, (int) $owner['id'], ROLE_ADMIN)['ok']);
$super2 = find_admin_by_email('super2@kynossolutions.com');
check('role change audited', $auditCount('user.role_changed') >= 1);
check('now-demoted account has admin permissions', !user_can(find_admin_by_id((int) $owner['id']), 'users.manage'));

echo "\n== Password reset ==\n";
check('password reset works', team_reset_password($super2, (int) $super2['id'], 'fresh-password-99')['ok']);
check('old password refused after reset', !attempt_login('super2@kynossolutions.com', 'super-password-12')['ok']);
check('new password accepted', attempt_login('super2@kynossolutions.com', 'fresh-password-99')['ok']);

echo "\n== Audit log is append-only ==\n";
$blocked = false;
try {
    db()->exec('UPDATE audit_log SET action = "tampered" WHERE id = 1');
} catch (Throwable $error) {
    $blocked = str_contains($error->getMessage(), 'append-only');
}
check('database blocks audit updates', $blocked);
$blockedDelete = false;
try {
    db()->exec('DELETE FROM audit_log WHERE id = 1');
} catch (Throwable $error) {
    $blockedDelete = str_contains($error->getMessage(), 'append-only');
}
check('database blocks audit deletes', $blockedDelete);

echo "\n$pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
