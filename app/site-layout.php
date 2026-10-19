<?php
declare(strict_types=1);

require_once __DIR__ . '/site-data.php';

function render_site_page(array $page): void
{
    $active = $page['active'] ?? '';
    $introKey = 'page_' . $active . '_intro';
    if (is_string($active) && array_key_exists($introKey, SETTING_RULES)) {
        $page['intro'] = setting($introKey, (string) $page['intro']);
    }

    $title = $page['title'] . ' | KYNOS Solar Systems';
    $description = $page['description'];
    $image = $page['image'] ?? 'images/hero.jpg';
    $sections = $page['sections'] ?? [];
    $cards = $page['cards'] ?? [];
    $currentPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $socialLinks = site_social_links();
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
  <svg class="svg-sprite" aria-hidden="true"><symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol><symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></symbol><symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M14 7l5 5-5 5"/></symbol><symbol id="i-wa" viewBox="0 0 24 24"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7A8.5 8.5 0 1 1 20.5 11.7z"/><path d="M8.2 7.5c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.7 1.7c.1.3.1.5-.1.7l-.6.8c-.2.2-.2.4 0 .7.7 1.2 1.7 2.1 2.9 2.7.3.2.5.1.7-.1l.8-1c.2-.3.5-.3.8-.2l1.8.9c.3.1.4.3.4.6 0 .7-.3 1.5-.9 2-.6.5-1.4.8-2.2.7-1.4-.2-3.1-.9-4.8-2.4-1.3-1.2-2.4-2.8-2.8-4.2-.4-1.3-.2-2.3.2-2.9z"/></symbol><symbol id="i-fb" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M12 2a10 10 0 0 0-1.6 19.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 12 2z"/></symbol><symbol id="i-ig" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01" stroke-width="2.6"/></symbol><symbol id="i-yt" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" fill-rule="evenodd" d="M4.5 5h15A2.5 2.5 0 0 1 22 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 16.5v-9A2.5 2.5 0 0 1 4.5 5zM10 8.5v7l6-3.5z"/></symbol><symbol id="i-xcom" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></symbol><symbol id="i-li" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S.1 4.88.1 3.5 1.22 1 2.6 1s2.38 1.12 2.38 2.5zM.4 8h4.2v15.5H.4V8zm7.6 0h4v2.1h.06c.56-1.06 1.92-2.18 3.96-2.18 4.23 0 5.01 2.78 5.01 6.4v9.18h-4.2v-8.14c0-1.94-.04-4.44-2.7-4.44-2.7 0-3.12 2.11-3.12 4.3V23.5H8V8z"/></symbol><symbol id="i-tt" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 1 1-2.59-2.59c.27 0 .53.04.78.12v-3.16a5.75 5.75 0 0 0-.78-.05 5.69 5.69 0 1 0 5.69 5.69V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3s-1.88.09-3.24-1.48z"/></symbol></svg>
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
    <div class="drawer-actions"><a class="header-quote" href="<?= e(wa('Hello KYNOS Solar Systems, I would like a quote for a solar system.')) ?>" target="_blank" rel="noopener"><svg class="ico wa"><use href="#i-wa"/></svg>Get a Quote</a><a class="drawer-assessment" href="index.php#assessment">Start Load Assessment</a><a class="drawer-call" href="tel:+<?= e(site_phone_intl()) ?>">Call <?= e(site_phone()) ?></a><p>Solar packages from <?= e(setting_packages_from()) ?></p></div>
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

    <section class="final-cta"><div class="shell"><div><h2>Ready to plan your solar system?</h2><p>Tell us what you need to power and arrange a free site assessment.</p></div><div class="actions"><a class="button primary" href="<?= e(wa('Hello KYNOS Solar Systems, I would like to arrange a site assessment.')) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a><a class="button outline" href="tel:+<?= e(site_phone_intl()) ?>">Call <?= e(site_phone()) ?></a></div></div></section>
  </main>

  <footer><div class="shell footer-grid"><div><img src="images/logo.png" alt="KYNOS Solar Systems"><p><?= e(setting('footer_blurb', 'Reliable solar solutions for homes, offices and businesses in Ghana.')) ?></p></div><div><h2>Solutions</h2><a href="solar-panels.php">Solar panels</a><a href="inverters.php">Inverters</a><a href="lithium-batteries.php">Lithium batteries</a><a href="installation.php">Installation</a></div><div><h2>Contact</h2><a href="tel:+<?= e(site_phone_intl()) ?>"><?= e(site_phone()) ?></a><span><?= e(site_address()) ?></span><?php if ($socialLinks): ?><span class="footer-socials"><?php foreach ($socialLinks as $link): ?><a href="<?= e($link['href']) ?>" target="_blank" rel="noopener" aria-label="<?= e($link['label']) ?>"><svg class="ico"><use href="#<?= e($link['icon']) ?>"/></svg></a><?php endforeach; ?></span><?php endif; ?></div></div><div class="copyright"><div class="shell">&copy; <?= date('Y') ?> KYNOS Solar Systems. <a href="admin.php">Staff Login</a> &middot; <a href="https://princecaleb.dev" target="_blank" rel="noopener">Built by princecaleb.dev</a></div></div></footer>
  <script>
  (function(){var body=document.body,toggle=document.getElementById('navToggle'),drawer=document.getElementById('drawer'),close=document.getElementById('drawerClose'),backdrop=document.getElementById('drawerBackdrop');function setDrawer(open){body.classList.toggle('drawer-open',open);drawer.inert=!open;toggle.setAttribute('aria-expanded',String(open));if(open)close.focus()}toggle.addEventListener('click',function(){setDrawer(true)});close.addEventListener('click',function(){setDrawer(false)});backdrop.addEventListener('click',function(){setDrawer(false)});drawer.addEventListener('click',function(event){if(event.target.closest('a'))setDrawer(false)});document.addEventListener('keydown',function(event){if(event.key==='Escape')setDrawer(false)});})();
  </script>
</body>
</html>
<?php
}
