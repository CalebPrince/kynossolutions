<?php
declare(strict_types=1);

const KYNOS_PHONE = '053 040 9118';
const KYNOS_PHONE_INTL = '233530409118';

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function wa(string $message): string { return 'https://wa.me/' . KYNOS_PHONE_INTL . '?text=' . rawurlencode($message); }

function render_site_page(array $page): void
{
    $title = $page['title'] . ' | KYNOS Solar Systems';
    $description = $page['description'];
    $active = $page['active'] ?? '';
    $image = $page['image'] ?? 'images/hero.jpg';
    $sections = $page['sections'] ?? [];
    $cards = $page['cards'] ?? [];
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="only light">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="icon" href="images/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Mulish:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/pages.css">
  <link rel="stylesheet" href="assets/css/mobile-nav.css">
</head>
<body>
  <header class="topbar">
    <div class="shell navrow">
      <a class="logo" href="index.php"><img src="images/logo.png" alt="KYNOS Solar Systems"></a>
      <nav aria-label="Main navigation">
        <a href="index.php">Home</a><a href="about.php"<?= $active === 'about' ? ' class="active"' : '' ?>>About</a><a href="solutions.php"<?= $active === 'solutions' ? ' class="active"' : '' ?>>Solutions</a><a href="projects.php"<?= $active === 'projects' ? ' class="active"' : '' ?>>Projects</a><a href="contact.php"<?= $active === 'contact' ? ' class="active"' : '' ?>>Contact</a>
      </nav>
      <a class="button whatsapp" href="<?= e(wa('Hello KYNOS Solar Systems, I would like a solar quote.')) ?>" target="_blank" rel="noopener">Get a quote</a>
    </div>
  </header>

  <main>
    <section class="page-hero">
      <div class="shell hero-grid">
        <div class="hero-copy">
          <?php if (!empty($page['parent'])): ?><a class="breadcrumb" href="<?= e($page['parent']['url']) ?>"><?= e($page['parent']['label']) ?></a><?php endif; ?>
          <h1><?= e($page['title']) ?></h1>
          <p><?= e($page['intro']) ?></p>
          <div class="actions"><a class="button primary" href="<?= e(wa($page['message'] ?? 'Hello KYNOS Solar Systems, I would like more information.')) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a><a class="text-link" href="index.php#assessment">Start load assessment</a></div>
        </div>
        <figure><img src="<?= e($image) ?>" alt="<?= e($page['image_alt'] ?? $page['title']) ?>"></figure>
      </div>
    </section>

    <?php if ($cards): ?>
    <section class="content-section"><div class="shell"><div class="section-heading"><h2><?= e($page['cards_title'] ?? 'Explore our solutions') ?></h2><p><?= e($page['cards_intro'] ?? 'Choose the solution you want to learn more about.') ?></p></div><div class="card-grid">
      <?php foreach ($cards as $card): ?><article class="card"><img src="<?= e($card['image']) ?>" alt="<?= e($card['title']) ?>"><div><h3><?= e($card['title']) ?></h3><p><?= e($card['text']) ?></p><a href="<?= e($card['url']) ?>">View solution</a></div></article><?php endforeach; ?>
    </div></div></section>
    <?php endif; ?>

    <?php foreach ($sections as $index => $section): ?>
    <section class="content-section<?= $index % 2 ? ' tinted' : '' ?>"><div class="shell content-grid">
      <div><h2><?= e($section['title']) ?></h2><p><?= e($section['text']) ?></p></div>
      <?php if (!empty($section['items'])): ?><ul class="check-list"><?php foreach ($section['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div></section>
    <?php endforeach; ?>

    <?php if (!empty($page['packages'])): ?>
    <section class="content-section package-area"><div class="shell"><div class="section-heading"><h2>Current solar packages</h2><p>Package specifications and pricing supplied by KYNOS Solar Systems.</p></div><div class="package-grid">
      <?php foreach ($page['packages'] as $package): ?><article class="package"><h3><?= e($package['name']) ?></h3><strong><?= e($package['price']) ?></strong><ul><?php foreach ($package['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><a class="button primary" href="<?= e(wa('Hello KYNOS Solar Systems, I would like the ' . $package['name'] . ' (' . $package['price'] . ').')) ?>" target="_blank" rel="noopener">Choose <?= e($package['name']) ?></a></article><?php endforeach; ?>
    </div></div></section>
    <?php endif; ?>

    <section class="final-cta"><div class="shell"><div><h2>Ready to plan your solar system?</h2><p>Tell us what you need to power and arrange a free site assessment.</p></div><div class="actions"><a class="button primary" href="<?= e(wa('Hello KYNOS Solar Systems, I would like to arrange a site assessment.')) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a><a class="button outline" href="tel:+<?= KYNOS_PHONE_INTL ?>">Call <?= KYNOS_PHONE ?></a></div></div></section>
  </main>

  <footer><div class="shell footer-grid"><div><img src="images/logo.png" alt="KYNOS Solar Systems"><p>Reliable solar solutions for homes, offices and businesses in Ghana.</p></div><div><h2>Solutions</h2><a href="solar-panels.php">Solar panels</a><a href="inverters.php">Inverters</a><a href="lithium-batteries.php">Lithium batteries</a><a href="installation.php">Installation</a></div><div><h2>Contact</h2><a href="tel:+<?= KYNOS_PHONE_INTL ?>"><?= KYNOS_PHONE ?></a><span>Accra &amp; Tema, Ghana</span></div></div><div class="copyright"><div class="shell">&copy; <?= date('Y') ?> KYNOS Solar Systems.</div></div></footer>
</body>
</html>
<?php
}
