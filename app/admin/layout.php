<?php
declare(strict_types=1);

function admin_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** @return array<int, array{view: string, label: string, permission: string}> */
function admin_nav_items(): array
{
    return [
        ['view' => 'dashboard', 'label' => 'Dashboard', 'permission' => 'customers.view'],
        ['view' => 'customers', 'label' => 'Customers', 'permission' => 'customers.view'],
        ['view' => 'content', 'label' => 'Site content', 'permission' => 'content.manage'],
        ['view' => 'contact', 'label' => 'Contact & social', 'permission' => 'settings.manage'],
        ['view' => 'team', 'label' => 'Team', 'permission' => 'users.manage'],
        ['view' => 'audit', 'label' => 'Audit log', 'permission' => 'audit.view'],
    ];
}

function admin_page_start(string $title, string $active, ?array $user): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= admin_h($title) ?> | KYNOS Admin</title>
<style>
:root{--navy:#0B2A40;--navy-deep:#071E2F;--orange:#F4581C;--orange-dark:#D94810;--ink:#14283A;--muted:#55677A;--line:#E3E8EE;--mist:#F4F6F9;--white:#fff;--ok:#067647;--ok-bg:#ECFDF3;--err:#B42318;--err-bg:#FEF3F2;--warn-bg:#FFFAEB;--warn:#B54708}
*{box-sizing:border-box}
body{margin:0;font-family:"Segoe UI",system-ui,-apple-system,sans-serif;background:var(--mist);color:var(--ink);font-size:15px;line-height:1.5}
a{color:inherit}
h1,h2,h3{font-family:"Segoe UI",system-ui,sans-serif;color:var(--navy);margin:0}
.shell{display:flex;min-height:100vh}
.side{width:232px;flex:none;background:var(--navy);color:#fff;display:flex;flex-direction:column;padding:22px 0}
.side .brand{padding:0 20px 18px;border-bottom:1px solid rgba(255,255,255,.12)}
.side .brand img{height:44px;display:block}
.side .brand p{margin:8px 0 0;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#9FB3C2}
.side nav{display:flex;flex-direction:column;padding:12px 10px;gap:2px}
.side nav a{display:block;padding:10px 12px;border-radius:6px;color:#D7E2EA;text-decoration:none;font-weight:600;font-size:14px}
.side nav a:hover{background:rgba(255,255,255,.08);color:#fff}
.side nav a.on{background:var(--orange);color:#fff}
.side .who{margin-top:auto;padding:16px 20px 0;border-top:1px solid rgba(255,255,255,.12);font-size:13px;color:#B7C6D2}
.side .who strong{display:block;color:#fff;font-size:14px}
.side .who .role{display:inline-block;margin-top:6px;background:rgba(255,255,255,.12);padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.04em}
.side .who form{margin-top:12px}
.side .who button{width:100%;background:transparent;border:1px solid rgba(255,255,255,.35);color:#fff;padding:8px;border-radius:6px;font-weight:600;cursor:pointer}
.side .who button:hover{background:rgba(255,255,255,.1)}
main{flex:1;padding:32px 36px 64px;max-width:1120px}
.page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.page-head h1{font-size:26px}
.page-head p{margin:4px 0 0;color:var(--muted)}
.card{background:var(--white);border:1px solid var(--line);border-radius:10px;padding:22px 24px;margin-bottom:20px}
.card h2{font-size:17px;margin-bottom:4px}
.card .sub{color:var(--muted);font-size:14px;margin:0 0 16px}
table{width:100%;border-collapse:collapse;background:var(--white)}
th{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);text-align:left;padding:10px 12px;border-bottom:2px solid var(--line)}
td{padding:11px 12px;border-bottom:1px solid var(--line);vertical-align:top;font-size:14px}
tr:last-child td{border-bottom:0}
.pill{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:700;background:var(--mist);color:var(--navy);text-transform:capitalize}
.pill.on{background:var(--ok-bg);color:var(--ok)}
.pill.off{background:var(--err-bg);color:var(--err)}
.pill.st{background:#EFF4FF;color:#3538CD}
label{display:block;font-weight:600;font-size:13px;color:var(--navy);margin-bottom:5px}
input[type=text],input[type=email],input[type=password],select,textarea{width:100%;padding:10px 12px;border:1.5px solid var(--line);border-radius:6px;font:inherit;color:var(--ink);background:var(--white)}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--orange)}
textarea{min-height:88px;resize:vertical}
.field{margin-bottom:14px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.btn{display:inline-flex;align-items:center;gap:8px;background:var(--orange);color:#fff;border:0;border-radius:6px;padding:10px 18px;font:inherit;font-weight:700;font-size:14px;cursor:pointer}
.btn:hover{background:var(--orange-dark)}
.btn.ghost{background:var(--white);color:var(--navy);border:1.5px solid var(--line)}
.btn.ghost:hover{border-color:var(--navy)}
.btn.sm{padding:6px 12px;font-size:13px}
.btn.danger{background:var(--err)}
.row-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.flash{border-radius:8px;padding:12px 16px;margin-bottom:18px;font-weight:600;font-size:14px}
.flash.ok{background:var(--ok-bg);color:var(--ok);border:1px solid #A6F4C5}
.flash.err{background:var(--err-bg);color:var(--err);border:1px solid #FDA29B}
.empty{border:1.5px dashed var(--line);background:var(--white);border-radius:10px;padding:48px 24px;text-align:center;color:var(--muted)}
.empty h2{margin-bottom:6px}
.auth-wrap{min-height:100vh;display:grid;place-items:center;padding:24px;background:var(--navy-deep)}
.auth-card{background:var(--white);width:min(440px,100%);border-radius:12px;padding:34px 32px;border-top:5px solid var(--orange)}
.auth-card img{height:52px;margin-bottom:20px}
.auth-card h1{font-size:22px}
.auth-card .sub{color:var(--muted);font-size:14px;margin:6px 0 20px}
.notice{background:var(--warn-bg);color:var(--warn);border:1px solid #FEC84B;border-radius:8px;padding:12px 14px;font-size:14px;margin-bottom:16px}
details.audit-details{font-size:13px;color:var(--muted)}
details.audit-details pre{margin:6px 0 0;background:var(--mist);padding:8px;border-radius:6px;overflow-x:auto;white-space:pre-wrap;word-break:break-all}
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:16px}
.filters .field{margin:0;min-width:180px}
.muted{color:var(--muted);font-size:13px}
.copy-grid{display:grid;gap:16px}
.pkg-editor{border:1px solid var(--line);border-radius:8px;padding:16px;margin-bottom:14px}
.pkg-editor h3{font-size:15px;margin-bottom:10px}
.mono{font-family:ui-monospace,Consolas,monospace;font-size:12.5px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.stat{background:var(--white);border:1px solid var(--line);border-radius:10px;padding:16px 18px}
.stat b{display:block;font-size:28px;color:var(--navy);line-height:1.2}
.stat span{color:var(--muted);font-size:13px}
.stat a{color:var(--orange);font-weight:700;font-size:13px;text-decoration:none}
.stat a:hover{text-decoration:underline}
.quick{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}
.quick a{display:block;background:var(--white);border:1px solid var(--line);border-radius:10px;padding:16px 18px;text-decoration:none;font-weight:700;color:var(--navy)}
.quick a:hover{border-color:var(--orange);color:var(--orange)}
.quick a span{display:block;font-weight:400;color:var(--muted);font-size:13px;margin-top:4px}
@media(max-width:860px){.shell{flex-direction:column}.side{width:100%;flex-direction:row;flex-wrap:wrap;align-items:center;padding:12px}.side .brand{border:0;padding:0 12px}.side nav{flex-direction:row;flex-wrap:wrap;padding:8px 12px}.side .who{margin:0 0 0 auto;border:0;padding:0 12px;display:flex;gap:10px;align-items:center}.side .who form{margin:0}.side .who button{width:auto}main{padding:20px 16px 48px}.grid2,.grid3{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="shell">
<aside class="side">
  <div class="brand"><img src="images/logo.png" alt="KYNOS Solar Systems"><p>Administration</p></div>
  <nav aria-label="Admin sections">
  <?php foreach (admin_nav_items() as $item): if ($user === null || !user_can($user, $item['permission'])) continue; ?>
    <a href="?view=<?= $item['view'] ?>" class="<?= $active === $item['view'] ? 'on' : '' ?>"><?= admin_h($item['label']) ?></a>
  <?php endforeach; ?>
  </nav>
  <?php if ($user !== null): ?>
  <div class="who">
    <strong><?= admin_h((string) $user['name']) ?></strong>
    <?= admin_h((string) $user['email']) ?><br>
    <span class="role"><?= admin_h(str_replace('_', ' ', (string) $user['role'])) ?></span>
    <form method="post" action="admin.php"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button type="submit">Sign out</button></form>
  </div>
  <?php endif; ?>
</aside>
<main>
<?php admin_flash_render(); ?>
<?php
}

function admin_page_end(): void
{
    ?>
</main>
</div>
</body>
</html>
<?php
}

function admin_flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function admin_flash_render(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $flash['type'] === 'ok' ? 'ok' : 'err';
    echo '<p class="flash ' . $type . '" role="status">' . admin_h((string) $flash['message']) . '</p>';
}
