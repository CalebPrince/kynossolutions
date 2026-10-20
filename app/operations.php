<?php
declare(strict_types=1);

/**
 * Privileged administration operations. Every function checks its own
 * permission and business rules so authorization cannot be bypassed by
 * calling handlers directly; the HTTP layer only wires inputs and output.
 */

const CUSTOMER_STATUSES = ['new', 'contacted', 'quoted', 'won', 'closed'];

function customer_list(string $statusFilter = '', string $search = '', int $limit = 250): array
{
    $sql = 'SELECT * FROM customer_enquiries';
    $where = [];
    $params = [];
    if (in_array($statusFilter, CUSTOMER_STATUSES, true)) {
        $where[] = 'status = ?';
        $params[] = $statusFilter;
    }
    if ($search !== '') {
        $where[] = '(name LIKE ? OR phone LIKE ? OR location LIKE ?)';
        $like = '%' . mb_substr($search, 0, 60) . '%';
        array_push($params, $like, $like, $like);
    }
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, min(500, $limit));
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function customer_find(int $id): ?array
{
    $statement = db()->prepare('SELECT * FROM customer_enquiries WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $row = $statement->fetch();
    return $row ?: null;
}

/** @return array<string, int> Status key => count, plus a 'total' key. */
function customer_status_counts(): array
{
    $counts = array_fill_keys(CUSTOMER_STATUSES, 0);
    $counts['total'] = 0;
    foreach (db()->query('SELECT status, COUNT(*) AS n FROM customer_enquiries GROUP BY status')->fetchAll() as $row) {
        $status = (string) $row['status'];
        $n = (int) $row['n'];
        $counts[$status] = $n;
        $counts['total'] += $n;
    }
    return $counts;
}

/** @return array{ok: bool, error: string} */
function customer_set_status(array $actor, int $customerId, string $status): array
{
    assert_permission($actor, 'customers.update_status');
    if (!in_array($status, CUSTOMER_STATUSES, true)) {
        return ['ok' => false, 'error' => 'Unknown enquiry status.'];
    }
    $customer = customer_find($customerId);
    if ($customer === null) {
        return ['ok' => false, 'error' => 'That enquiry no longer exists.'];
    }
    $previous = (string) $customer['status'];
    if ($previous === $status) {
        return ['ok' => true, 'error' => ''];
    }
    try {
        db()->beginTransaction();
        $statement = db()->prepare('UPDATE customer_enquiries SET status = ? WHERE id = ?');
        $statement->execute([$status, $customerId]);
        audit_event_as($actor, 'customer.status_changed', 'customer_enquiry', $customerId, [
            'from' => $previous,
            'to' => $status,
        ]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS customer status update failed: ' . $error->getMessage());
        return ['ok' => false, 'error' => 'The status could not be saved. Please try again.'];
    }
    return ['ok' => true, 'error' => ''];
}

function team_list(): array
{
    return db()->query('SELECT id, name, email, role, is_active, last_login_at, created_at FROM admin_users ORDER BY role, name')->fetchAll();
}

/** @return array{ok: bool, error: string, id: int|null} */
function team_create_user(array $actor, string $name, string $email, string $role, string $password): array
{
    assert_permission($actor, 'users.manage');
    $fail = static fn(string $error): array => ['ok' => false, 'error' => $error, 'id' => null];

    $name = trim($name);
    $email = mb_strtolower(trim($email));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        return $fail('Enter the team member’s name (2 to 80 characters).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        return $fail('Enter a valid email address.');
    }
    if (!in_array($role, ALL_ROLES, true)) {
        return $fail('Choose a valid role.');
    }
    $passwordError = validate_password($password, $email);
    if ($passwordError !== null) {
        return $fail($passwordError);
    }
    if (find_admin_by_email($email) !== null) {
        return $fail('An account with that email already exists.');
    }

    try {
        db()->beginTransaction();
        $statement = db()->prepare('INSERT INTO admin_users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)');
        $statement->execute([$name, $email, hash_password($password), $role]);
        $id = (int) db()->lastInsertId();
        audit_event_as($actor, 'user.created', 'admin_user', $id, ['email' => $email, 'role' => $role]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS user creation failed: ' . $error->getMessage());
        return $fail('The account could not be created. Please try again.');
    }
    return ['ok' => true, 'error' => '', 'id' => $id];
}

/** @return array{ok: bool, error: string} */
function team_set_role(array $actor, int $userId, string $role): array
{
    assert_permission($actor, 'users.manage');
    if (!in_array($role, ALL_ROLES, true)) {
        return ['ok' => false, 'error' => 'Choose a valid role.'];
    }
    $target = find_admin_by_id($userId);
    if ($target === null) {
        return ['ok' => false, 'error' => 'That account no longer exists.'];
    }
    if ($target['role'] === $role) {
        return ['ok' => true, 'error' => ''];
    }
    $action = $role === ROLE_SUPER_ADMIN ? 'promote' : 'demote';
    if (is_last_super_admin_guarded($target, $action, admin_user_count_active_super())) {
        return ['ok' => false, 'error' => 'The last active super administrator cannot be demoted. Promote another account first.'];
    }
    try {
        db()->beginTransaction();
        $statement = db()->prepare('UPDATE admin_users SET role = ? WHERE id = ?');
        $statement->execute([$role, $userId]);
        audit_event_as($actor, 'user.role_changed', 'admin_user', $userId, [
            'email' => (string) $target['email'],
            'from' => (string) $target['role'],
            'to' => $role,
        ]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS role change failed: ' . $error->getMessage());
        return ['ok' => false, 'error' => 'The role could not be changed. Please try again.'];
    }
    return ['ok' => true, 'error' => ''];
}

/** @return array{ok: bool, error: string} */
function team_set_active(array $actor, int $userId, bool $active): array
{
    assert_permission($actor, 'users.manage');
    $target = find_admin_by_id($userId);
    if ($target === null) {
        return ['ok' => false, 'error' => 'That account no longer exists.'];
    }
    if (!$active && is_last_super_admin_guarded($target, 'disable', admin_user_count_active_super())) {
        return ['ok' => false, 'error' => 'The last active super administrator cannot be disabled. Activate another super administrator first.'];
    }
    if (!$active && (int) $target['id'] === (int) $actor['id']) {
        return ['ok' => false, 'error' => 'You cannot disable your own account.'];
    }
    $currentActive = (int) $target['is_active'] === 1;
    if ($currentActive === $active) {
        return ['ok' => true, 'error' => ''];
    }
    try {
        db()->beginTransaction();
        $statement = db()->prepare('UPDATE admin_users SET is_active = ? WHERE id = ?');
        $statement->execute([$active ? 1 : 0, $userId]);
        audit_event_as($actor, $active ? 'user.enabled' : 'user.disabled', 'admin_user', $userId, [
            'email' => (string) $target['email'],
        ]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS account toggle failed: ' . $error->getMessage());
        return ['ok' => false, 'error' => 'The account could not be updated. Please try again.'];
    }
    return ['ok' => true, 'error' => ''];
}

/** @return array{ok: bool, error: string} */
function team_reset_password(array $actor, int $userId, string $password): array
{
    assert_permission($actor, 'users.manage');
    $target = find_admin_by_id($userId);
    if ($target === null) {
        return ['ok' => false, 'error' => 'That account no longer exists.'];
    }
    $passwordError = validate_password($password, (string) $target['email']);
    if ($passwordError !== null) {
        return ['ok' => false, 'error' => $passwordError];
    }
    try {
        db()->beginTransaction();
        $statement = db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $statement->execute([hash_password($password), $userId]);
        audit_event_as($actor, 'user.password_reset', 'admin_user', $userId, [
            'email' => (string) $target['email'],
        ]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS password reset failed: ' . $error->getMessage());
        return ['ok' => false, 'error' => 'The password could not be updated. Please try again.'];
    }
    return ['ok' => true, 'error' => ''];
}

/** First super administrator, created only while the admin_users table is empty. */
/** @return array{ok: bool, error: string, id: int|null} */
function bootstrap_first_super_admin(string $name, string $email, string $password): array
{
    $fail = static fn(string $error): array => ['ok' => false, 'error' => $error, 'id' => null];
    $name = trim($name);
    $email = mb_strtolower(trim($email));
    if (admin_user_count() !== 0) {
        return $fail('Accounts already exist. Sign in instead.');
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        return $fail('Enter your name (2 to 80 characters).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        return $fail('Enter a valid email address.');
    }
    $passwordError = validate_password($password, $email);
    if ($passwordError !== null) {
        return $fail($passwordError);
    }
    try {
        db()->beginTransaction();
        // Re-check inside the transaction so two racing setup posts cannot both win.
        if (admin_user_count() !== 0) {
            db()->rollBack();
            return $fail('Accounts already exist. Sign in instead.');
        }
        $statement = db()->prepare('INSERT INTO admin_users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)');
        $statement->execute([$name, $email, hash_password($password), ROLE_SUPER_ADMIN]);
        $id = (int) db()->lastInsertId();
        audit_insert($id, $email, ROLE_SUPER_ADMIN, 'user.bootstrapped', 'admin_user', $id, ['email' => $email]);
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS bootstrap failed: ' . $error->getMessage());
        return $fail('The first account could not be created. Please try again.');
    }
    return ['ok' => true, 'error' => '', 'id' => $id];
}

function audit_recent(int $limit = 200, string $actionFilter = ''): array
{
    $sql = 'SELECT * FROM audit_log';
    $params = [];
    if ($actionFilter !== '') {
        $sql .= ' WHERE action LIKE ?';
        $params[] = $actionFilter . '%';
    }
    $sql .= ' ORDER BY id DESC LIMIT ' . max(1, min(500, $limit));
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}
