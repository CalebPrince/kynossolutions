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
    $currentPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
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
  <link rel="stylesheet" href="assets/css/landing-header.css">
</head>
<body>
  <svg class="svg-sprite" aria-hidden="true"><symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol><symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></symbol><symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M14 7l5 5-5 5"/></symbol><symbol id="i-wa" viewBox="0 0 24 24"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7A8.5 8.5 0 1 1 20.5 11.7z"/><path d="M8.2 7.5c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.7 1.7c.1.3.1.5-.1.7l-.6.8c-.2.2-.2.4 0 .7.7 1.2 1.7 2.1 2.9 2.7.3.2.5.1.7-.1l.8-1c.2-.3.5-.3.8-.2l1.8.9c.3.1.4.3.4.6 0 .7-.3 1.5-.9 2-.6.5-1.4.8-2.2.7-1.4-.2-3.1-.9-4.8-2.4-1.3-1.2-2.4-2.8-2.8-4.2-.4-1.3-.2-2.3.2-2.9z"/></symbol></svg>
  <header class="site-header scrolled">
    <div class="header-wrap">
      <a class="brand" href="index.php" aria-label="KYNOS Solar Systems home"><img src="images/logo.png" alt="KYNOS Solar Systems, Powering Your Future Sustainably"></a>
      <nav class="main-nav" aria-label="Main navigation">
        <a href="index.php">Home</a><a href="about.php"<?= $active === 'about' ? ' class="active"' : '' ?>>About Us</a><a href="solutions.php"<?= $active === 'solutions' && $currentPage !== 'installation.php' ? ' class="active"' : '' ?>>Solutions</a><a href="installation.php"<?= $currentPage === 'installation.php' ? ' class="active"' : '' ?>>Installation</a><a href="projects.php"<?= $active === 'projects' ? ' class="active"' : '' ?>>Projects</a><a href="contact.php"<?= $active === 'contact' ? ' class="active"' : '' ?>>Contact</a>
      </nav>
      <button class="nav-toggle" id="navToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="drawer"><svg class="ico"><use href="#i-menu"/></svg></button>
      <a class="header-quote" href="<?= e(wa('Hello KYNOS Solar Systems, I would like a quote for a solar system.')) ?>" target="_blank" rel="noopener"><svg class="ico wa"><use href="#i-wa"/></svg><span>Get a Quote</span></a>
    </div>
  </header>
  <div class="drawer-backdrop" id="drawerBackdrop"></div>
  <aside class="drawer" id="drawer" aria-label="Menu" inert>
    <div class="drawer-head"><img src="images/logo.png" alt="KYNOS Solar Systems"><button class="drawer-close" type="button" id="drawerClose" aria-label="Close menu"><svg class="ico"><use href="#i-x"/></svg></button></div>
    <nav class="drawer-nav" aria-label="Mobile navigation"><a href="index.php">Home<svg class="ico"><use href="#i-arrow"/></svg></a><a href="about.php">About Us<svg class="ico"><use href="#i-arrow"/></svg></a><a href="solutions.php">Solutions<svg class="ico"><use href="#i-arrow"/></svg></a><a href="installation.php">Installation<svg class="ico"><use href="#i-arrow"/></svg></a><a href="projects.php">Projects<svg class="ico"><use href="#i-arrow"/></svg></a><a href="contact.php">Contact<svg class="ico"><use href="#i-arrow"/></svg></a></nav>
    <div class="drawer-actions"><a class="header-quote" href="<?= e(wa('Hello KYNOS Solar Systems, I would like a quote for a solar system.')) ?>" target="_blank" rel="noopener"><svg class="ico wa"><use href="#i-wa"/></svg>Get a Quote</a><a class="drawer-assessment" href="index.php#assessment">Start Load Assessment</a><a class="drawer-call" href="tel:+<?= KYNOS_PHONE_INTL ?>">Call <?= KYNOS_PHONE ?></a><p>Solar packages from GHC 39,000</p></div>
  </aside>

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
  <script>
  (function(){var body=document.body,toggle=document.getElementById('navToggle'),drawer=document.getElementById('drawer'),close=document.getElementById('drawerClose'),backdrop=document.getElementById('drawerBackdrop');function setDrawer(open){body.classList.toggle('drawer-open',open);drawer.inert=!open;toggle.setAttribute('aria-expanded',String(open));if(open)close.focus()}toggle.addEventListener('click',function(){setDrawer(true)});close.addEventListener('click',function(){setDrawer(false)});backdrop.addEventListener('click',function(){setDrawer(false)});drawer.addEventListener('click',function(event){if(event.target.closest('a'))setDrawer(false)});document.addEventListener('keydown',function(event){if(event.key==='Escape')setDrawer(false)});})();
  </script>
</body>
</html>
<?php
}
