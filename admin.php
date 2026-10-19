<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/app/bootstrap.php';

if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
$error = '';
$configuredHash = env_value('ADMIN_PASSWORD_HASH', '');

if (isset($_GET['logout'])) {
    unset($_SESSION['kynos_admin']);
    header('Location: admin.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($configuredHash && password_verify((string) $_POST['password'], $configuredHash)) {
        session_regenerate_id(true);
        $_SESSION['kynos_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'The password is incorrect.';
}

$authenticated = !empty($_SESSION['kynos_admin']);
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'], $_POST['status'])) {
    if (!hash_equals($_SESSION['admin_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expired.');
    }
    $allowed = ['new', 'contacted', 'quoted', 'won', 'closed'];
    if (in_array($_POST['status'], $allowed, true)) {
        $update = db()->prepare('UPDATE solar_assessments SET status = ? WHERE id = ?');
        $update->execute([$_POST['status'], (int) $_POST['assessment_id']]);
    }
    header('Location: admin.php');
    exit;
}

$assessments = [];
if ($authenticated) {
    $assessments = db()->query('SELECT * FROM solar_assessments ORDER BY created_at DESC LIMIT 250')->fetchAll();
}
function h(?string $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function whatsapp_number(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    return str_starts_with($digits, '0') ? '233' . substr($digits, 1) : $digits;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>KYNOS enquiries</title>
  <script>tailwind.config={theme:{extend:{colors:{kynos:{navy:'#0B2A40',orange:'#F4581C',mist:'#F4F6F9'}}}}}</script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
<?php if (!$authenticated): ?>
  <main class="grid min-h-screen place-items-center px-5">
    <form method="post" class="w-full max-w-sm border-t-4 border-kynos-orange bg-white p-8 shadow-xl">
      <img src="images/logo.png" alt="KYNOS Solar Systems" class="mb-8 h-14 w-auto">
      <h1 class="text-2xl font-extrabold text-kynos-navy">Enquiry dashboard</h1>
      <p class="mt-2 text-sm text-slate-500">Sign in to review solar assessments.</p>
      <?php if ($error): ?><p class="mt-5 rounded bg-red-50 p-3 text-sm font-semibold text-red-700"><?= h($error) ?></p><?php endif; ?>
      <?php if (!$configuredHash): ?><p class="mt-5 rounded bg-amber-50 p-3 text-sm text-amber-800">Set ADMIN_PASSWORD_HASH in config.php before signing in.</p><?php endif; ?>
      <label for="password" class="mt-6 block text-sm font-bold text-kynos-navy">Password</label>
      <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 w-full border border-slate-300 px-4 py-3 outline-none focus:border-kynos-orange focus:ring-2 focus:ring-orange-100">
      <button class="mt-5 w-full bg-kynos-orange px-4 py-3 font-bold text-white hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200">Sign in</button>
    </form>
  </main>
<?php else: ?>
  <header class="bg-kynos-navy text-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5">
      <div><h1 class="text-xl font-extrabold">Solar assessments</h1><p class="text-sm text-slate-300"><?= count($assessments) ?> recent enquiries</p></div>
      <a href="?logout=1" class="border border-white/40 px-4 py-2 text-sm font-bold hover:bg-white hover:text-kynos-navy">Sign out</a>
    </div>
  </header>
  <main class="mx-auto max-w-7xl px-5 py-8">
    <?php if (!$assessments): ?>
      <div class="border border-dashed border-slate-300 bg-white p-12 text-center"><h2 class="font-bold text-kynos-navy">No assessments yet</h2><p class="mt-2 text-sm text-slate-500">New completed website assessments will appear here.</p></div>
    <?php else: ?>
      <div class="grid gap-5">
      <?php foreach ($assessments as $lead): ?>
        <article class="border-l-4 border-kynos-orange bg-white p-5 shadow-sm">
          <div class="flex flex-col justify-between gap-4 md:flex-row">
            <div>
              <div class="flex flex-wrap items-center gap-3"><h2 class="text-lg font-extrabold text-kynos-navy"><?= h($lead['name']) ?></h2><span class="bg-slate-100 px-2 py-1 text-xs font-bold"><?= h($lead['status']) ?></span></div>
              <p class="mt-1 text-sm text-slate-500"><?= h($lead['created_at']) ?> · <?= h($lead['location']) ?> · <?= h($lead['property_type']) ?></p>
              <div class="mt-4 flex flex-wrap gap-2"><a class="bg-green-600 px-3 py-2 text-sm font-bold text-white" href="https://wa.me/<?= h(whatsapp_number($lead['phone'])) ?>" target="_blank" rel="noopener">WhatsApp</a><a class="border border-slate-300 px-3 py-2 text-sm font-bold" href="tel:<?= h($lead['phone']) ?>"><?= h($lead['phone']) ?></a><?php if ($lead['email']): ?><a class="border border-slate-300 px-3 py-2 text-sm font-bold" href="mailto:<?= h($lead['email']) ?>"><?= h($lead['email']) ?></a><?php endif; ?></div>
            </div>
            <form method="post" class="flex items-end gap-2">
              <input type="hidden" name="csrf" value="<?= h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="assessment_id" value="<?= (int) $lead['id'] ?>">
              <label class="text-xs font-bold text-slate-500">Status<select name="status" class="mt-1 block border border-slate-300 px-3 py-2 text-sm text-slate-800"><?php foreach (['new','contacted','quoted','won','closed'] as $status): ?><option value="<?= $status ?>" <?= $lead['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></label>
              <button class="bg-kynos-navy px-3 py-2 text-sm font-bold text-white">Save</button>
            </form>
          </div>
          <dl class="mt-5 grid grid-cols-2 gap-px bg-slate-200 sm:grid-cols-3 lg:grid-cols-6">
            <?php foreach ([['Package',$lead['recommended_package']],['Load',$lead['running_load_w'].' W'],['Daily use',$lead['daily_energy_kwh'].' kWh'],['Inverter',$lead['inverter_kva'].' kVA'],['Battery',$lead['battery_kwh'].' kWh'],['Panels',$lead['panel_count']]] as $item): ?><div class="bg-slate-50 p-3"><dt class="text-xs text-slate-500"><?= h($item[0]) ?></dt><dd class="mt-1 font-bold text-kynos-navy"><?= h((string) $item[1]) ?></dd></div><?php endforeach; ?>
          </dl>
          <?php if ($lead['notes']): ?><p class="mt-4 text-sm"><strong>Notes:</strong> <?= h($lead['notes']) ?></p><?php endif; ?>
        </article>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
<?php endif; ?>
</body>
</html>
