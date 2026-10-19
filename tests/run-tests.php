<?php
declare(strict_types=1);

/**
 * Unit tests for the approved security baseline v1.0.0.
 * Run: php tests/run-tests.php
 * These tests need no database: they cover the role matrix, the final-super-admin
 * guard, password policy and the settings allowlist validators.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/audit.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/settings.php';
require_once __DIR__ . '/../app/operations.php';

if (!isset($_SESSION)) {
    $_SESSION = [];
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

echo "== Role matrix (baseline v1.0.0 contract) ==\n";

$expected = [
    'super_admin' => ['customers.view', 'customers.update_status', 'users.manage', 'content.manage', 'settings.manage', 'audit.view'],
    'admin' => ['customers.view', 'customers.update_status', 'content.manage', 'settings.manage'],
    'staff' => ['customers.view', 'customers.update_status'],
];
foreach ($expected as $role => $permissions) {
    check("$role has exactly the approved permissions", role_permissions($role) === $permissions, json_encode(role_permissions($role)));
}
check('unknown role gets no permissions', role_permissions('owner') === []);

$user = static fn(string $role): array => ['id' => 1, 'role' => $role, 'is_active' => 1, 'email' => 't@example.com'];

check('staff cannot manage users', !user_can($user('staff'), 'users.manage'));
check('staff cannot publish content', !user_can($user('staff'), 'content.manage'));
check('staff cannot change settings', !user_can($user('staff'), 'settings.manage'));
check('staff cannot view audit log', !user_can($user('staff'), 'audit.view'));
check('staff can view customers', user_can($user('staff'), 'customers.view'));
check('staff can update enquiry status', user_can($user('staff'), 'customers.update_status'));

check('admin cannot manage users', !user_can($user('admin'), 'users.manage'));
check('admin cannot view audit log', !user_can($user('admin'), 'audit.view'));
check('admin can publish content', user_can($user('admin'), 'content.manage'));
check('admin can manage customers', user_can($user('admin'), 'customers.view'));

check('super admin can manage users', user_can($user('super_admin'), 'users.manage'));
check('super admin can view audit log', user_can($user('super_admin'), 'audit.view'));

$denied = false;
try {
    assert_permission($user('staff'), 'users.manage');
} catch (AuthException $error) {
    $denied = $error->status === 403;
}
check('assert_permission throws 403 for forbidden action', $denied);

echo "\n== Final super administrator guard ==\n";
$super = ['role' => 'super_admin', 'is_active' => 1];
$adminRow = ['role' => 'admin', 'is_active' => 1];
$inactiveSuper = ['role' => 'super_admin', 'is_active' => 0];
check('last super cannot be demoted', is_last_super_admin_guarded($super, 'demote', 1));
check('last super cannot be disabled', is_last_super_admin_guarded($super, 'disable', 1));
check('super among many can be demoted', !is_last_super_admin_guarded($super, 'demote', 3));
check('non-super is never guarded', !is_last_super_admin_guarded($adminRow, 'demote', 1));
check('already-inactive super is not guarded', !is_last_super_admin_guarded($inactiveSuper, 'disable', 1));
check('promote is never guarded', !is_last_super_admin_guarded($super, 'promote', 1));

echo "\n== Password policy ==\n";
check('short password rejected', validate_password('short') !== null);
check('10-char password accepted', validate_password('correct horse battery') === null);
check('password containing email rejected', validate_password('secretary1234', 'secretary1234@example.com') !== null);
check('hash is bcrypt/argon one-way', str_starts_with(hash_password('correct horse battery'), '$'));

echo "\n== Settings allowlist and validators ==\n";
check('unknown key rejected', !validate_setting('evil_key', 'x')['ok']);
check('html in text rejected', !validate_setting('page_home_hero_intro', '<script>alert(1)</script>')['ok']);
check('angle bracket alone rejected', !validate_setting('footer_blurb', 'a > b')['ok']);
check('normal text accepted', validate_setting('page_home_hero_intro', 'Clean energy for every home.')['ok']);
check('over-length text rejected', !validate_setting('footer_blurb', str_repeat('a', 200))['ok']);

check('ghana local phone accepted', validate_setting('phone', '053 040 9118')['ok']);
check('ghana intl phone accepted', validate_setting('phone', '+233530409118')['ok']);
check('foreign phone rejected', !validate_setting('phone', '1234567890')['ok']);
check('empty phone rejected', !validate_setting('phone', '')['ok']);

check('whatsapp normalised to 233 form', validate_setting('whatsapp', '0530409118')['value'] === '233530409118');
check('bad whatsapp rejected', !validate_setting('whatsapp', 'call-me')['ok']);

check('valid email accepted', validate_setting('email', 'info@kynossolutions.com')['ok']);
check('invalid email rejected', !validate_setting('email', 'not-an-email')['ok']);

check('https url accepted', validate_setting('social_facebook', 'https://facebook.com/kynos')['ok']);
check('http url rejected', !validate_setting('social_facebook', 'http://facebook.com/kynos')['ok']);
check('javascript url rejected', !validate_setting('social_facebook', 'javascript:alert(1)')['ok']);
check('empty social url allowed', validate_setting('social_x', '')['ok']);

$goodPackages = json_encode([
    1 => ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => ['5 × 620W solar panels', '1 × battery', '1 × inverter']],
    2 => ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => ['6 × panels', '1 × battery', '1 × inverter']],
    3 => ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => ['10 × panels', '1 × battery', '1 × inverter']],
]);
check('valid packages accepted', validate_packages_payload($goodPackages) !== null);
check('packages with html item rejected', validate_packages_payload(json_encode([
    1 => ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => ['<b>x</b>']],
    2 => ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => ['x']],
    3 => ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => ['x']],
])) === null);
check('packages missing a tier rejected', validate_packages_payload(json_encode([
    1 => ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => ['x']],
])) === null);
check('packages without GHC price rejected', validate_packages_payload(json_encode([
    1 => ['name' => 'Bronze', 'price' => '39000', 'items' => ['x']],
    2 => ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => ['x']],
    3 => ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => ['x']],
])) === null);
check('packages with too many items rejected', validate_packages_payload(json_encode([
    1 => ['name' => 'Bronze', 'price' => 'GHC 1', 'items' => ['a', 'b', 'c', 'd', 'e', 'f', 'g']],
    2 => ['name' => 'Silver', 'price' => 'GHC 1', 'items' => ['x']],
    3 => ['name' => 'Gold', 'price' => 'GHC 1', 'items' => ['x']],
])) === null);

echo "\n== Phone helpers ==\n";
check('local number converts to international', phone_to_intl('053 040 9118') === '233530409118');
check('international number kept', phone_to_intl('233530409118') === '233530409118');
check('+233 number converts', phone_to_intl('+233 53 040 9118') === '233530409118');

echo "\n== Settings defaults without database ==\n";
check('settings fall back to defaults when database is unavailable', setting('phone') === '053 040 9118');
check('default packages parse into three tiers', count(setting_packages()) === 3);
check('cheapest package price detected', setting_packages_from() === 'GHC 39,000');

echo "\n$pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
