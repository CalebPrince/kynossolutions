<?php
declare(strict_types=1);

function view_auth_screen(string $mode, string $error = ''): void
{
    // mode: login | setup
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= $mode === 'setup' ? 'Set up administration' : 'Sign in' ?> | KYNOS Admin</title>
<style>
body{margin:0;font-family:"Segoe UI",system-ui,sans-serif;background:#071E2F;color:#14283A}
.wrap{min-height:100vh;display:grid;place-items:center;padding:24px}
.card{background:#fff;width:min(440px,100%);border-radius:12px;padding:34px 32px;border-top:5px solid #F4581C}
.card img{height:52px;margin-bottom:20px}
h1{margin:0;font-size:22px;color:#0B2A40}
.sub{color:#55677A;font-size:14px;margin:6px 0 20px}
.err{background:#FEF3F2;color:#B42318;border:1px solid #FDA29B;border-radius:8px;padding:10px 12px;font-size:14px;font-weight:600;margin-bottom:16px}
.notice{background:#FFFAEB;color:#B54708;border:1px solid #FEC84B;border-radius:8px;padding:10px 12px;font-size:14px;margin-bottom:16px}
label{display:block;font-weight:600;font-size:13px;color:#0B2A40;margin:14px 0 5px}
input{width:100%;padding:11px 12px;border:1.5px solid #E3E8EE;border-radius:6px;font:inherit;box-sizing:border-box}
input:focus{outline:none;border-color:#F4581C}
button{margin-top:20px;width:100%;background:#F4581C;color:#fff;border:0;border-radius:6px;padding:12px;font:inherit;font-weight:700;cursor:pointer}
button:hover{background:#D94810}
p.foot{margin-top:16px;font-size:13px;color:#55677A;text-align:center}
</style>
</head>
<body>
<div class="wrap">
  <form method="post" class="card" autocomplete="on">
    <img src="images/logo.png" alt="KYNOS Solar Systems">
    <?php if ($mode === 'setup'): ?>
      <h1>Set up the first account</h1>
      <p class="sub">No accounts exist yet. Create the first super administrator to secure the dashboard. This form disappears once the account is created.</p>
    <?php else: ?>
      <h1>KYNOS administration</h1>
      <p class="sub">Sign in with your individual account to manage customers, content and settings.</p>
    <?php endif; ?>
    <?php if ($error !== ''): ?><p class="err" role="alert"><?= admin_h($error) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $mode === 'setup' ? 'bootstrap' : 'login' ?>">
    <?php if ($mode === 'setup'): ?>
      <label for="name">Full name</label>
      <input id="name" name="name" type="text" maxlength="80" required autocomplete="name">
    <?php endif; ?>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" maxlength="190" required autocomplete="username">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" maxlength="200" required <?= $mode === 'setup' ? 'autocomplete="new-password"' : 'autocomplete="current-password"' ?>>
    <?php if ($mode === 'setup'): ?>
      <label for="password2">Confirm password</label>
      <input id="password2" name="password2" type="password" maxlength="200" required autocomplete="new-password">
      <p class="sub" style="margin-top:10px">Use at least 10 characters. You can create admin and staff accounts after signing in.</p>
    <?php endif; ?>
    <button type="submit"><?= $mode === 'setup' ? 'Create super administrator' : 'Sign in' ?></button>
    <p class="foot">KYNOS Solar Systems</p>
  </form>
</div>
</body>
</html>
<?php
}

function view_migration_required(): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Administration unavailable | KYNOS</title>
<style>
body{margin:0;font-family:"Segoe UI",system-ui,sans-serif;background:#071E2F;color:#14283A;display:grid;place-items:center;min-height:100vh;padding:24px}
.card{background:#fff;width:min(520px,100%);border-radius:12px;padding:34px 32px;border-top:5px solid #F4581C}
h1{margin:0 0 10px;font-size:22px;color:#0B2A40}
p{color:#55677A;line-height:1.6}
code{background:#F4F6F9;padding:2px 6px;border-radius:4px}
</style>
</head>
<body>
  <div class="card">
    <h1>Administration is not available yet</h1>
    <p>The database migration for the new dashboard has not been applied. Run <code>database-migration-v1.sql</code> in phpMyAdmin, then reload this page. If you were signed in before, sign in again.</p>
  </div>
</body>
</html>
<?php
}

function view_message(int $status, string $title, string $text): void
{
    http_response_code($status);
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= admin_h($title) ?> | KYNOS Admin</title>
<style>
body{margin:0;font-family:"Segoe UI",system-ui,sans-serif;background:#071E2F;display:grid;place-items:center;min-height:100vh;padding:24px;color:#14283A}
.card{background:#fff;width:min(480px,100%);border-radius:12px;padding:34px 32px;border-top:5px solid #F4581C}
h1{margin:0 0 10px;font-size:22px;color:#0B2A40}
p{color:#55677A}
a{color:#F4581C;font-weight:700}
</style>
</head>
<body>
  <div class="card">
    <h1><?= admin_h($title) ?></h1>
    <p><?= admin_h($text) ?></p>
    <p><a href="admin.php">Back to administration</a></p>
  </div>
</body>
</html>
<?php
}

function view_dashboard(array $user): void
{
    assert_permission($user, 'customers.view');
    $canCustomers = user_can($user, 'customers.view');
    $counts = $canCustomers ? customer_status_counts() : null;
    $recent = $canCustomers ? customer_list('', '', 5) : [];
    $openCount = $counts !== null ? $counts['new'] + $counts['contacted'] + $counts['quoted'] : 0;
    $teamCount = user_can($user, 'users.manage') ? count(team_list()) : null;
    $events = user_can($user, 'audit.view') ? audit_recent(8) : [];
    admin_page_start('Dashboard', 'dashboard', $user);
    ?>
    <div class="page-head">
      <div><h1>Dashboard</h1><p>Welcome back, <?= admin_h((string) $user['name']) ?>. Here is the current state of the KYNOS admin.</p></div>
    </div>

    <?php if ($counts !== null): ?>
    <div class="stats">
      <div class="stat"><b><?= $counts['total'] ?></b><span>Total enquiries</span></div>
      <div class="stat"><b><?= $counts['new'] ?></b><span>New, unhandled</span></div>
      <div class="stat"><b><?= $openCount ?></b><span>Open (new, contacted, quoted)</span></div>
      <div class="stat"><b><?= $counts['won'] ?></b><span>Won</span></div>
      <div class="stat"><b><?= $counts['closed'] ?></b><span>Closed</span></div>
      <?php if ($teamCount !== null): ?><div class="stat"><b><?= $teamCount ?></b><span>Team accounts</span></div><?php endif; ?>
    </div>
    <?php else: ?>
    <div class="stats"><div class="stat"><b><?= admin_h(str_replace('_', ' ', (string) $user['role'])) ?></b><span>Your role</span></div></div>
    <?php endif; ?>

    <?php if ($canCustomers): ?>
    <div class="card">
      <h2>Latest enquiries</h2>
      <p class="sub">The 5 most recent records. <a href="?view=customers" style="color:var(--orange);font-weight:700">Open the full customer list</a>.</p>
      <?php if (!$recent): ?>
        <div class="empty"><h2>No enquiries yet</h2><p>Assessments submitted on the website will appear here.</p></div>
      <?php else: ?>
        <div style="overflow-x:auto">
        <table>
          <thead><tr><th>Customer</th><th>Location</th><th>Package</th><th>Status</th><th>Received</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $customer): ?>
            <tr>
              <td><strong><?= admin_h((string) $customer['name']) ?></strong><br><span class="muted"><?= admin_h((string) $customer['phone']) ?></span></td>
              <td><?= admin_h((string) $customer['location']) ?></td>
              <td><?= admin_h((string) $customer['recommended_package']) ?></td>
              <td><span class="pill st"><?= admin_h((string) $customer['status']) ?></span></td>
              <td class="muted"><?= admin_h((string) $customer['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($events): ?>
    <div class="card">
      <h2>Recent activity</h2>
      <p class="sub">Latest audited actions. <a href="?view=audit" style="color:var(--orange);font-weight:700">Open the audit log</a>.</p>
      <div style="overflow-x:auto">
      <table>
        <thead><tr><th>When</th><th>Who</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td class="muted"><?= admin_h((string) $event['created_at']) ?></td>
            <td><?= admin_h((string) $event['actor_email']) ?></td>
            <td class="mono"><?= admin_h((string) $event['action']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <h2>Go to section</h2>
      <p class="sub">Only sections your role can access are listed.</p>
      <div class="quick">
        <?php foreach (admin_nav_items() as $item): if ($item['view'] === 'dashboard' || !user_can($user, $item['permission'])) continue; ?>
          <a href="?view=<?= $item['view'] ?>"><?= admin_h($item['label']) ?><span><?= admin_h(match ($item['view']) {
            'customers' => 'Enquiries, statuses and contact details',
            'content' => 'Page copy and solar packages',
            'contact' => 'Phone, email, address and social links',
            'team' => 'Accounts, roles and passwords',
            'audit' => 'Append-only history of admin actions',
            default => 'Open section',
          }) ?></span></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
    admin_page_end();
}

function view_customers(array $user, string $statusFilter, string $search): void
{
    assert_permission($user, 'customers.view');
    $customers = customer_list($statusFilter, $search);
    admin_page_start('Customers', 'customers', $user);
    ?>
    <div class="page-head">
      <div><h1>Customers &amp; enquiries</h1><p><?= count($customers) ?> record<?= count($customers) === 1 ? '' : 's' ?> shown. Website assessments appear here automatically.</p></div>
    </div>
    <div class="card">
      <form method="get" class="filters">
        <input type="hidden" name="view" value="customers">
        <div class="field"><label for="status">Status</label>
          <select id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach (CUSTOMER_STATUSES as $status): ?>
              <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="search">Search</label>
          <input id="search" name="q" type="text" value="<?= admin_h($search) ?>" placeholder="Name, phone or location">
        </div>
        <button class="btn ghost" type="submit">Filter</button>
      </form>

      <?php if (!$customers): ?>
        <div class="empty"><h2>No enquiries found</h2><p>New assessments from the website will appear here. Adjust the filters if you expect existing records.</p></div>
      <?php else: ?>
        <div style="overflow-x:auto">
        <table>
          <thead><tr><th>Customer</th><th>Contact</th><th>System</th><th>Status</th><th>Received</th></tr></thead>
          <tbody>
          <?php foreach ($customers as $customer): ?>
            <tr>
              <td>
                <strong><?= admin_h((string) $customer['name']) ?></strong><br>
                <span class="muted"><?= admin_h((string) $customer['location']) ?> · <?= admin_h((string) $customer['property_type']) ?></span>
                <?php if ($customer['notes']): ?><div class="muted" style="margin-top:6px"><?= admin_h((string) $customer['notes']) ?></div><?php endif; ?>
              </td>
              <td class="row-actions" style="flex-direction:column;align-items:flex-start">
                <a href="https://wa.me/<?= admin_h(phone_to_intl((string) $customer['phone'])) ?>" target="_blank" rel="noopener"><?= admin_h((string) $customer['phone']) ?></a>
                <?php if (!empty($customer['email'])): ?><a href="mailto:<?= admin_h((string) $customer['email']) ?>"><?= admin_h((string) $customer['email']) ?></a><?php endif; ?>
              </td>
              <td>
                <strong><?= admin_h((string) $customer['recommended_package']) ?></strong><br>
                <span class="muted"><?= admin_h((string) $customer['running_load_w']) ?> W · <?= admin_h((string) $customer['daily_energy_kwh']) ?> kWh/day · <?= admin_h((string) $customer['inverter_kva']) ?> kVA · <?= admin_h((string) $customer['battery_kwh']) ?> kWh · <?= admin_h((string) $customer['panel_count']) ?> panels</span>
              </td>
              <td>
                <?php if (user_can($user, 'customers.update_status')): ?>
                  <form method="post" class="row-actions">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="customer_status">
                    <input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>">
                    <select name="status" aria-label="Status for <?= admin_h((string) $customer['name']) ?>">
                      <?php foreach (CUSTOMER_STATUSES as $status): ?>
                        <option value="<?= $status ?>" <?= $customer['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn sm" type="submit">Save</button>
                  </form>
                <?php else: ?>
                  <span class="pill st"><?= admin_h((string) $customer['status']) ?></span>
                <?php endif; ?>
              </td>
              <td class="muted"><?= admin_h((string) $customer['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
    <?php
    admin_page_end();
}

function view_content(array $user): void
{
    assert_permission($user, 'content.manage');
    $packages = setting_packages();
    if ($packages === []) {
        $packages = json_decode(SETTING_DEFAULTS['packages_json'], true);
        $packages = [
            ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => []],
            ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => []],
            ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => []],
        ];
    }
    $copyKeys = array_filter(array_keys(SETTING_RULES), static fn(string $key) => SETTING_RULES[$key]['group'] === 'content');
    admin_page_start('Site content', 'content', $user);
    ?>
    <div class="page-head">
      <div><h1>Site content</h1><p>Plain-text copy published on the public website. Changes take effect immediately and are recorded in the audit log.</p></div>
    </div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="settings_content">
      <div class="card">
        <h2>Page copy</h2>
        <p class="sub">Only these fields are editable. HTML is not accepted.</p>
        <div class="copy-grid">
        <?php foreach ($copyKeys as $key): $rule = SETTING_RULES[$key]; ?>
          <div class="field">
            <label for="<?= admin_h($key) ?>"><?= admin_h($rule['label']) ?> <span class="muted">(max <?= (int) $rule['max'] ?> characters)</span></label>
            <textarea id="<?= admin_h($key) ?>" name="content[<?= admin_h($key) ?>]" maxlength="<?= (int) $rule['max'] ?>"><?= admin_h(setting($key)) ?></textarea>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <h2>Solar packages</h2>
        <p class="sub">Names, prices and item lines shown in the pricing sections. Package names and prices must include “GHC”.</p>
        <?php foreach ([1, 2, 3] as $slot): $package = $packages[$slot - 1]; ?>
          <div class="pkg-editor">
            <h3>Package <?= $slot ?></h3>
            <div class="grid2">
              <div class="field"><label for="pkg_name_<?= $slot ?>">Name</label>
                <input id="pkg_name_<?= $slot ?>" name="pkg_name[<?= $slot ?>]" type="text" maxlength="40" value="<?= admin_h((string) $package['name']) ?>" required></div>
              <div class="field"><label for="pkg_price_<?= $slot ?>">Price</label>
                <input id="pkg_price_<?= $slot ?>" name="pkg_price[<?= $slot ?>]" type="text" maxlength="40" value="<?= admin_h((string) $package['price']) ?>" placeholder="GHC 39,000" required></div>
            </div>
            <div class="field"><label for="pkg_items_<?= $slot ?>">Items (one per line, up to 6)</label>
              <textarea id="pkg_items_<?= $slot ?>" name="pkg_items[<?= $slot ?>]" required><?= admin_h(implode("\n", (array) $package['items'])) ?></textarea></div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="btn" type="submit">Save content</button>
    </form>
    <?php
    admin_page_end();
}

function view_contact(array $user): void
{
    assert_permission($user, 'settings.manage');
    $contactKeys = array_filter(array_keys(SETTING_RULES), static fn(string $key) => SETTING_RULES[$key]['group'] === 'contact');
    $socialKeys = array_filter(array_keys(SETTING_RULES), static fn(string $key) => SETTING_RULES[$key]['group'] === 'social');
    admin_page_start('Contact & social', 'contact', $user);
    ?>
    <div class="page-head">
      <div><h1>Contact &amp; social links</h1><p>Published contact details for the website header, footer and WhatsApp buttons.</p></div>
    </div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="settings_contact">
      <div class="card">
        <h2>Company contact details</h2>
        <div class="grid2">
        <?php foreach ($contactKeys as $key): $rule = SETTING_RULES[$key]; ?>
          <div class="field">
            <label for="<?= admin_h($key) ?>"><?= admin_h($rule['label']) ?></label>
            <input id="<?= admin_h($key) ?>" name="contact[<?= admin_h($key) ?>]" type="text" maxlength="<?= (int) $rule['max'] ?>" value="<?= admin_h(setting($key)) ?>">
            <?php if ($rule['type'] === 'whatsapp'): ?><span class="muted">International format without “+”, e.g. 233530409118</span><?php endif; ?>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <h2>Social links</h2>
        <p class="sub">Optional. Must be full https:// URLs. Leave blank to hide a link.</p>
        <div class="grid2">
        <?php foreach ($socialKeys as $key): $rule = SETTING_RULES[$key]; ?>
          <div class="field">
            <label for="<?= admin_h($key) ?>"><?= admin_h($rule['label']) ?></label>
            <input id="<?= admin_h($key) ?>" name="contact[<?= admin_h($key) ?>]" type="text" maxlength="<?= (int) $rule['max'] ?>" value="<?= admin_h(setting($key)) ?>" placeholder="https://...">
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <button class="btn" type="submit">Save contact settings</button>
    </form>
    <?php
    admin_page_end();
}

function view_team(array $user): void
{
    assert_permission($user, 'users.manage');
    $team = team_list();
    $activeSuper = admin_user_count_active_super();
    admin_page_start('Team', 'team', $user);
    ?>
    <div class="page-head">
      <div><h1>Team accounts</h1><p>Individual accounts with fixed roles. The final active super administrator cannot be demoted or disabled.</p></div>
    </div>
    <div class="card">
      <h2>Accounts</h2>
      <p class="sub"><?= count($team) ?> account<?= count($team) === 1 ? '' : 's' ?> · <?= $activeSuper ?> active super administrator<?= $activeSuper === 1 ? '' : 's' ?></p>
      <div style="overflow-x:auto">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last sign-in</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($team as $member): $isSelf = (int) $member['id'] === (int) $user['id']; ?>
          <tr>
            <td><strong><?= admin_h((string) $member['name']) ?></strong><?= $isSelf ? ' <span class="muted">(you)</span>' : '' ?></td>
            <td><?= admin_h((string) $member['email']) ?></td>
            <td>
              <form method="post" class="row-actions">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="team_role">
                <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                <select name="role" aria-label="Role for <?= admin_h((string) $member['name']) ?>">
                  <?php foreach (ALL_ROLES as $role): ?>
                    <option value="<?= $role ?>" <?= $member['role'] === $role ? 'selected' : '' ?>><?= admin_h(str_replace('_', ' ', $role)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn sm ghost" type="submit">Set role</button>
              </form>
            </td>
            <td><span class="pill <?= (int) $member['is_active'] === 1 ? 'on' : 'off' ?>"><?= (int) $member['is_active'] === 1 ? 'Active' : 'Disabled' ?></span></td>
            <td class="muted"><?= admin_h((string) ($member['last_login_at'] ?? 'Never')) ?></td>
            <td>
              <div class="row-actions">
                <form method="post"><?= csrf_field() ?>
                  <input type="hidden" name="action" value="team_toggle">
                  <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                  <input type="hidden" name="enable" value="<?= (int) $member['is_active'] === 1 ? 0 : 1 ?>">
                  <button class="btn sm ghost" type="submit"><?= (int) $member['is_active'] === 1 ? 'Disable' : 'Enable' ?></button>
                </form>
                <form method="post" class="row-actions" onsubmit="return confirm('Set a new password for this account?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="team_password">
                  <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                  <input name="password" type="password" maxlength="200" placeholder="New password" required autocomplete="new-password" style="width:160px">
                  <button class="btn sm ghost" type="submit">Reset</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
    <div class="card">
      <h2>Add a team member</h2>
      <p class="sub">Super administrators manage accounts and publish content. Administrators manage customers and content. Staff view customers and update enquiry status only.</p>
      <form method="post" class="grid2">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="team_create">
        <div class="field"><label for="new_name">Full name</label><input id="new_name" name="name" type="text" maxlength="80" required></div>
        <div class="field"><label for="new_email">Email</label><input id="new_email" name="email" type="email" maxlength="190" required></div>
        <div class="field"><label for="new_role">Role</label>
          <select id="new_role" name="role">
            <?php foreach (ALL_ROLES as $role): ?><option value="<?= $role ?>"><?= admin_h(str_replace('_', ' ', $role)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="new_password">Password (min 10 characters)</label><input id="new_password" name="password" type="password" maxlength="200" required autocomplete="new-password"></div>
        <div><button class="btn" type="submit">Create account</button></div>
      </form>
    </div>
    <?php
    admin_page_end();
}

function view_audit(array $user, string $actionFilter): void
{
    assert_permission($user, 'audit.view');
    $events = audit_recent(200, $actionFilter);
    admin_page_start('Audit log', 'audit', $user);
    ?>
    <div class="page-head">
      <div><h1>Audit log</h1><p>Sign-ins, account changes, role changes, content updates and settings changes. Records are append-only.</p></div>
    </div>
    <div class="card">
      <form method="get" class="filters">
        <input type="hidden" name="view" value="audit">
        <div class="field"><label for="action">Action starts with</label>
          <input id="action" name="action" type="text" value="<?= admin_h($actionFilter) ?>" placeholder="auth., user., settings., customer.">
        </div>
        <button class="btn ghost" type="submit">Filter</button>
      </form>
      <?php if (!$events): ?>
        <div class="empty"><h2>No audit events yet</h2><p>Sign-ins and privileged changes will be recorded here.</p></div>
      <?php else: ?>
        <div style="overflow-x:auto">
        <table>
          <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Details</th></tr></thead>
          <tbody>
          <?php foreach ($events as $event): ?>
            <tr>
              <td class="muted"><?= admin_h((string) $event['created_at']) ?></td>
              <td><?= admin_h((string) $event['actor_email']) ?><br><span class="muted"><?= admin_h((string) $event['actor_role']) ?></span></td>
              <td class="mono"><?= admin_h((string) $event['action']) ?><br><span class="muted"><?= admin_h((string) $event['entity']) ?><?= $event['entity_id'] !== '' ? ' #' . admin_h((string) $event['entity_id']) : '' ?></span></td>
              <td>
                <?php if (!empty($event['details'])): ?>
                  <details class="audit-details"><summary>View</summary><pre><?= admin_h(pretty_json((string) $event['details'])) ?></pre></details>
                <?php else: ?><span class="muted">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
    <?php
    admin_page_end();
}

function pretty_json(string $json): string
{
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return $json;
    }
    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $json;
}
