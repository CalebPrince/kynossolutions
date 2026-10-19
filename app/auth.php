<?php
declare(strict_types=1);

const AUTH_SESSION_NAME = 'KYNOSSESS';
const AUTH_IDLE_SECONDS = 3600;
const AUTH_ABSOLUTE_SECONDS = 43200;
const AUTH_THROTTLE_WINDOW_MINUTES = 15;
const AUTH_MAX_FAILURES_PER_WINDOW = 5;
const AUTH_PASSWORD_MIN_LENGTH = 10;

const ROLE_SUPER_ADMIN = 'super_admin';
const ROLE_ADMIN = 'admin';
const ROLE_STAFF = 'staff';

const ALL_ROLES = [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_STAFF];

class AuthException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 403)
    {
        parent::__construct($message);
    }
}

function role_permissions(string $role): array
{
    $matrix = [
        ROLE_SUPER_ADMIN => ['customers.view', 'customers.update_status', 'users.manage', 'content.manage', 'settings.manage', 'audit.view'],
        ROLE_ADMIN => ['customers.view', 'customers.update_status', 'content.manage', 'settings.manage'],
        ROLE_STAFF => ['customers.view', 'customers.update_status'],
    ];
    return $matrix[$role] ?? [];
}

function auth_boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name(AUTH_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function admin_user_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

function admin_user_count_active_super(): int
{
    $statement = db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role = 'super_admin' AND is_active = 1");
    $statement->execute();
    return (int) $statement->fetchColumn();
}

function find_admin_by_email(string $email): ?array
{
    $statement = db()->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
    $statement->execute([mb_strtolower(trim($email))]);
    $row = $statement->fetch();
    return $row ?: null;
}

function find_admin_by_id(int $id): ?array
{
    $statement = db()->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $row = $statement->fetch();
    return $row ?: null;
}

function validate_password(string $password, string $email = ''): ?string
{
    if (mb_strlen($password) < AUTH_PASSWORD_MIN_LENGTH) {
        return 'Password must be at least ' . AUTH_PASSWORD_MIN_LENGTH . ' characters long.';
    }
    if ($email !== '') {
        $local = explode('@', $email)[0];
        $containsEmail = mb_stripos($password, $email) !== false
            || (mb_strlen($local) >= 4 && mb_stripos($password, $local) !== false);
        if ($containsEmail) {
            return 'Password must not contain your email address.';
        }
    }
    return null;
}

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

/**
 * True when the action would remove the last active super administrator,
 * either by demoting the role or disabling the account.
 */
function is_last_super_admin_guarded(array $target, string $action, int $activeSuperCount): bool
{
    $isSuper = $target['role'] === ROLE_SUPER_ADMIN && (int) $target['is_active'] === 1;
    if (!$isSuper) {
        return false;
    }
    $reducesSuper = in_array($action, ['demote', 'disable'], true);
    return $reducesSuper && $activeSuperCount <= 1;
}

function assert_permission(array $user, string $permission): void
{
    $permissions = role_permissions((string) $user['role']);
    if (!in_array($permission, $permissions, true)) {
        throw new AuthException('Your account does not permit this action.', 403);
    }
}

function user_can(array $user, string $permission): bool
{
    return in_array($permission, role_permissions((string) $user['role']), true);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        throw new AuthException('Your session expired. Refresh the page and try again.', 419);
    }
}

function login_throttle_key(string $email): string
{
    return mb_strtolower(trim($email));
}

function login_throttle_blocked(string $email): bool
{
    $window = date('Y-m-d H:i:s', time() - AUTH_THROTTLE_WINDOW_MINUTES * 60);
    $statement = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE attempted_at >= ? AND (account_key = ? OR ip_hash = ?)'
    );
    $statement->execute([$window, login_throttle_key($email), request_ip_hash()]);
    return (int) $statement->fetchColumn() >= AUTH_MAX_FAILURES_PER_WINDOW;
}

function record_login_failure(string $email): void
{
    $statement = db()->prepare('INSERT INTO login_attempts (account_key, ip_hash, attempted_at) VALUES (?, ?, NOW())');
    $statement->execute([login_throttle_key($email), request_ip_hash()]);
    if (random_int(1, 20) === 1) {
        db()->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)")->execute();
    }
}

function clear_login_failures(string $email): void
{
    $statement = db()->prepare('DELETE FROM login_attempts WHERE account_key = ?');
    $statement->execute([login_throttle_key($email)]);
}

/**
 * @return array{ok: bool, error: string, user: array|null}
 */
function attempt_login(string $email, string $password): array
{
    $fail = static fn(string $message): array => ['ok' => false, 'error' => $message, 'user' => null];
    $email = trim($email);
    if ($email === '' || $password === '') {
        return $fail('Enter your email and password.');
    }
    if (login_throttle_blocked($email)) {
        audit_event('auth.login_blocked', 'admin_user', null, ['account' => login_throttle_key($email)]);
        return $fail('Too many sign-in attempts. Please wait 15 minutes and try again.');
    }

    $user = find_admin_by_email($email);
    $hash = (string) ($user['password_hash'] ?? '');
    $valid = $user !== null && $hash !== '' && password_verify($password, $hash);

    if (!$valid) {
        record_login_failure($email);
        audit_event('auth.login_failed', 'admin_user', $user !== null ? (int) $user['id'] : null, ['account' => login_throttle_key($email)]);
        return $fail('Incorrect email or password.');
    }
    if ((int) $user['is_active'] !== 1) {
        record_login_failure($email);
        audit_event('auth.login_disabled', 'admin_user', (int) $user['id'], ['account' => login_throttle_key($email)]);
        return $fail('This account has been disabled. Contact the site owner.');
    }
    if (password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12])) {
        $statement = db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $statement->execute([hash_password($password), (int) $user['id']]);
    }
    clear_login_failures($email);
    return ['ok' => true, 'error' => '', 'user' => $user];
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user_id'] = (int) $user['id'];
    $_SESSION['auth_started_at'] = time();
    $_SESSION['auth_last_activity'] = time();
    $statement = db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
    $statement->execute([(int) $user['id']]);
    audit_event('auth.login', 'admin_user', (int) $user['id']);
}

function logout_user(): void
{
    $userId = isset($_SESSION['auth_user_id']) ? (int) $_SESSION['auth_user_id'] : null;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    if ($userId !== null) {
        audit_event('auth.logout', 'admin_user', $userId);
    }
}

/**
 * Resolve the signed-in user, enforcing idle and absolute session expiry.
 * The database row is authoritative, so disabling an account ends access immediately.
 */
function current_user(): ?array
{
    if (empty($_SESSION['auth_user_id'])) {
        return null;
    }
    $last = (int) ($_SESSION['auth_last_activity'] ?? 0);
    $started = (int) ($_SESSION['auth_started_at'] ?? 0);
    $now = time();
    if ($started === 0 || $now - $last > AUTH_IDLE_SECONDS || $now - $started > AUTH_ABSOLUTE_SECONDS) {
        logout_user();
        return null;
    }
    $_SESSION['auth_last_activity'] = $now;
    $user = find_admin_by_id((int) $_SESSION['auth_user_id']);
    if ($user === null || (int) $user['is_active'] !== 1) {
        logout_user();
        return null;
    }
    return $user;
}
