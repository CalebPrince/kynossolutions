<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/audit.php';
require __DIR__ . '/app/auth.php';
require __DIR__ . '/app/settings.php';
require __DIR__ . '/app/operations.php';
require __DIR__ . '/app/admin/layout.php';
require __DIR__ . '/app/admin/views.php';

auth_boot_session();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');
header('Referrer-Policy: same-origin');

/** Redirect back to a section after a POST (post/redirect/get). */
function admin_redirect(string $view, ?string $query = ''): never
{
    $target = 'admin.php?view=' . rawurlencode($view);
    if ($query !== '') {
        $target .= '&' . $query;
    }
    header('Location: ' . $target);
    exit;
}

try {
    $accountCount = admin_user_count();
} catch (Throwable $error) {
    error_log('KYNOS admin database unavailable: ' . $error->getMessage());
    view_migration_required();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'logout') {
        try {
            verify_csrf();
        } catch (AuthException) {
            admin_redirect('customers');
        }
        logout_user();
        header('Location: admin.php');
        exit;
    }

    try {
        verify_csrf();

        if ($action === 'bootstrap') {
            if ($accountCount !== 0) {
                view_message(403, 'Setup closed', 'Accounts already exist. Sign in instead.');
                exit;
            }
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password2'] ?? '');
            if ($password !== $confirm) {
                view_auth_screen('setup', 'The passwords do not match.');
                exit;
            }
            $result = bootstrap_first_super_admin(
                (string) ($_POST['name'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                $password
            );
            if (!$result['ok']) {
                view_auth_screen('setup', $result['error']);
                exit;
            }
            admin_flash_set('ok', 'Super administrator account created. Sign in to continue.');
            header('Location: admin.php');
            exit;
        }

        if ($action === 'login') {
            $outcome = attempt_login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
            if (!$outcome['ok']) {
                view_auth_screen('login', $outcome['error']);
                exit;
            }
            login_user($outcome['user']);
            header('Location: admin.php');
            exit;
        }

        $user = current_user();
        if ($user === null) {
            header('Location: admin.php');
            exit;
        }

        switch ($action) {
            case 'customer_status':
                $result = customer_set_status(
                    $user,
                    (int) ($_POST['customer_id'] ?? 0),
                    (string) ($_POST['status'] ?? '')
                );
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Enquiry status updated.' : $result['error']);
                admin_redirect('customers');
                break;

            case 'settings_content':
                $input = [];
                foreach (($_POST['content'] ?? []) as $key => $value) {
                    $input[(string) $key] = (string) $value;
                }
                $packages = [
                    1 => ['name' => (string) ($_POST['pkg_name'][1] ?? ''), 'price' => (string) ($_POST['pkg_price'][1] ?? ''), 'items' => array_filter(array_map('trim', explode("\n", (string) ($_POST['pkg_items'][1] ?? ''))))],
                    2 => ['name' => (string) ($_POST['pkg_name'][2] ?? ''), 'price' => (string) ($_POST['pkg_price'][2] ?? ''), 'items' => array_filter(array_map('trim', explode("\n", (string) ($_POST['pkg_items'][2] ?? ''))))],
                    3 => ['name' => (string) ($_POST['pkg_name'][3] ?? ''), 'price' => (string) ($_POST['pkg_price'][3] ?? ''), 'items' => array_filter(array_map('trim', explode("\n", (string) ($_POST['pkg_items'][3] ?? ''))))],
                ];
                $packagesJson = validate_packages_payload((string) json_encode($packages, JSON_UNESCAPED_UNICODE));
                if ($packagesJson === null) {
                    admin_flash_set('err', 'Check the packages: each needs a name like “Bronze”, a price like “GHC 39,000”, and 1 to 6 item lines.');
                    admin_redirect('content');
                }
                $input['packages_json'] = $packagesJson;
                $result = settings_save($user, $input);
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Site content saved.' : $result['error']);
                admin_redirect('content');
                break;

            case 'settings_contact':
                $input = [];
                foreach (($_POST['contact'] ?? []) as $key => $value) {
                    $input[(string) $key] = (string) $value;
                }
                $result = settings_save($user, $input);
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Contact settings saved.' : $result['error']);
                admin_redirect('contact');
                break;

            case 'team_create':
                $result = team_create_user(
                    $user,
                    (string) ($_POST['name'] ?? ''),
                    (string) ($_POST['email'] ?? ''),
                    (string) ($_POST['role'] ?? ''),
                    (string) ($_POST['password'] ?? '')
                );
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Account created.' : $result['error']);
                admin_redirect('team');
                break;

            case 'team_role':
                $result = team_set_role($user, (int) ($_POST['user_id'] ?? 0), (string) ($_POST['role'] ?? ''));
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Role updated.' : $result['error']);
                admin_redirect('team');
                break;

            case 'team_toggle':
                $result = team_set_active($user, (int) ($_POST['user_id'] ?? 0), (string) ($_POST['enable'] ?? '0') === '1');
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Account updated.' : $result['error']);
                admin_redirect('team');
                break;

            case 'team_password':
                $result = team_reset_password($user, (int) ($_POST['user_id'] ?? 0), (string) ($_POST['password'] ?? ''));
                admin_flash_set($result['ok'] ? 'ok' : 'err', $result['ok'] ? 'Password updated.' : $result['error']);
                admin_redirect('team');
                break;

            default:
                view_message(404, 'Unknown action', 'That action is not recognised.');
                exit;
        }
    } catch (AuthException $error) {
        if ($error->status === 419) {
            view_message(419, 'Session expired', 'Your session expired or the form was invalid. Refresh the page and try again.');
            exit;
        }
        view_message(403, 'Not permitted', $error->getMessage());
        exit;
    }
}

// ----- GET ------------------------------------------------------------------

$user = current_user();

if ($user === null) {
    if ($accountCount === 0) {
        view_auth_screen('setup');
    } else {
        view_auth_screen('login');
    }
    exit;
}

$view = (string) ($_GET['view'] ?? 'customers');
$navPermissions = [];
foreach (admin_nav_items() as $item) {
    $navPermissions[$item['view']] = $item['permission'];
}

try {
    switch ($view) {
        case 'customers':
            view_customers($user, (string) ($_GET['status'] ?? ''), trim((string) ($_GET['q'] ?? '')));
            break;
        case 'content':
            view_content($user);
            break;
        case 'contact':
            view_contact($user);
            break;
        case 'team':
            view_team($user);
            break;
        case 'audit':
            view_audit($user, trim((string) ($_GET['action'] ?? '')));
            break;
        default:
            view_message(404, 'Page not found', 'That administration page does not exist.');
            break;
    }
} catch (AuthException $error) {
    view_message(403, 'Not permitted', $error->getMessage());
} catch (Throwable $error) {
    error_log('KYNOS admin page failed: ' . $error->getMessage());
    view_message(500, 'Something went wrong', 'The page could not be loaded. If this continues, contact the site maintainer.');
}
