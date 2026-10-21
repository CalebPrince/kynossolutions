<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/app/site-data.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$phoneDisplay = setting('phone');
$phoneIntl = phone_to_intl($phoneDisplay);
$whatsappNumber = phone_to_intl(setting('whatsapp'));
$packagesFrom = setting_packages_from();
$sitePackages = solar_packages();
$packageTierClasses = ['tier-bronze', 'tier-silver', 'tier-gold'];
$packageLimits = [
    ['maxKva' => 4.2, 'maxBatteryKwh' => 2.4, 'maxPanels' => 5],
    ['maxKva' => 6, 'maxBatteryKwh' => 5.12, 'maxPanels' => 6],
    ['maxKva' => 6, 'maxBatteryKwh' => 10.24, 'maxPanels' => 10],
];
$jsPackages = [];
foreach ($sitePackages as $index => $package) {
    $limit = $packageLimits[$index] ?? $packageLimits[count($packageLimits) - 1];
    $jsPackages[] = [
        'name' => $package['name'] . ' Package',
        'price' => $package['price'],
        'maxKva' => $limit['maxKva'],
        'maxBatteryKwh' => $limit['maxBatteryKwh'],
        'maxPanels' => $limit['maxPanels'],
    ];
}
$socialLinks = site_social_links();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- light-only design: stop phone browsers from force-darkening the page, which hides the navy logo -->
<meta name="color-scheme" content="only light">
<meta name="theme-color" content="#FFFFFF">
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<script>document.documentElement.className = "js";</script>
<title>KYNOS Solar Systems | Affordable Solar Solutions in Ghana</title>
<meta name="description" content="KYNOS Solar Systems supplies and installs solar panels, inverters and lithium batteries for homes, offices and businesses in Ghana. Solar packages from GHC 39,000.">
<!-- Link preview (WhatsApp, Facebook, X). These URLs must be the full live address of the site. -->
<link rel="canonical" href="https://kynossolutions.com/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="KYNOS Solar Systems">
<meta property="og:title" content="KYNOS Solar Systems | Affordable Solar Solutions for Everyone">
<meta property="og:description" content="Solar panels, inverters and lithium batteries, professionally installed for homes, offices and businesses in Ghana. Packages from GHC 39,000.">
<meta property="og:url" content="https://kynossolutions.com/">
<meta property="og:image" content="https://kynossolutions.com/images/share.jpg">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="KYNOS Solar Systems logo beside a home with rooftop solar panels">
<meta property="og:locale" content="en_GH">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="KYNOS Solar Systems | Affordable Solar Solutions for Everyone">
<meta name="twitter:description" content="Solar panels, inverters and lithium batteries, professionally installed for homes, offices and businesses in Ghana. Packages from GHC 39,000.">
<meta name="twitter:image" content="https://kynossolutions.com/images/share.jpg">
<link rel="icon" href="images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Mulish:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --navy: #0B2A40;
    --navy-deep: #071E2F;
    --orange: #F4581C;
    --orange-dark: #D94810;
    --amber: #FDB022;
    --ink: #14283A;
    --muted: #55677A;
    --line: #E3E8EE;
    --mist: #F4F6F9;
    --white: #FFFFFF;
    --display: "Montserrat", "Segoe UI", sans-serif;
    --body: "Mulish", "Segoe UI", sans-serif;
  }
  :root { color-scheme: only light; }
  * { box-sizing: border-box; }
  html { scroll-behavior: smooth; scroll-padding-top: 72px; }
  body { margin: 0; font-family: var(--body); color: var(--ink); background: var(--white); line-height: 1.5; -webkit-font-smoothing: antialiased; }
  img { max-width: 100%; display: block; }
  a { color: inherit; text-decoration: none; }
  h1, h2, h3, h4 { font-family: var(--display); margin: 0; color: var(--navy); }
  p { margin: 0; }
  ul, ol { margin: 0; padding: 0; list-style: none; }
  button { font: inherit; cursor: pointer; }
  .wrap { width: min(1180px, 100% - 40px); margin-inline: auto; }
  .ico { width: 1em; height: 1em; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex: none; }
  .ico.solid { fill: currentColor; stroke: none; }
  .accent { color: var(--orange); }

  /* Buttons */
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-family: var(--display); font-weight: 700; font-size: 13px; padding: 14px 22px; border-radius: 4px; border: 1.5px solid transparent; transition: background .2s, color .2s, border-color .2s, transform .2s; white-space: nowrap; }
  .btn:hover { transform: translateY(-1px); }
  .btn-primary { background: var(--orange); color: var(--white); }
  .btn-primary:hover { background: var(--orange-dark); }
  .btn-wa { background: #25D366; color: var(--white); }
  .btn-wa:hover { background: #1DA851; }
  .btn-outline { background: var(--white); color: var(--orange); border-color: var(--orange); }
  .btn-outline:hover { background: var(--orange); color: var(--white); }
  .btn-ghost { background: transparent; color: var(--white); border-color: rgba(255,255,255,.75); }
  .btn-ghost:hover { background: var(--white); color: var(--navy); }
  .btn-sm { padding: 10px 16px; font-size: 12px; }
  .btn .ico { font-size: 16px; }
  .btn .ico.wa { font-size: 24px; }
  .btn:focus-visible, a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible { outline: 3px solid var(--amber); outline-offset: 2px; }

  /* Header */
  .site-header { position: fixed; top: 0; right: 0; left: 0; z-index: 20; transition: background .3s, box-shadow .3s; }
  .site-header .wrap { transition: padding .3s; }
  .site-header .brand img { transition: height .3s; }
  .site-header.scrolled { background: rgba(255,255,255,.96); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); box-shadow: 0 6px 24px rgba(11,42,64,.12); }
  .site-header.scrolled .wrap { padding-block: 8px; }
  .site-header.scrolled .brand img { height: 52px; }
  .site-header .wrap { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding-block: 14px; }
  .brand img { height: 72px; width: auto; }
  /* white halo: invisible on the light header, keeps the navy logo readable if a browser still forces a dark page */
  .site-header .brand img, .drawer-head img { filter: drop-shadow(0 0 1px rgba(255,255,255,.95)) drop-shadow(0 0 5px rgba(255,255,255,.75)); }
  .nav { display: flex; gap: 34px; }
  .nav a { font-family: var(--display); font-weight: 600; font-size: 13px; color: var(--navy); padding: 8px 0; border-bottom: 2px solid transparent; }
  .nav a:hover, .nav a.active { color: var(--orange); border-bottom-color: var(--orange); }
  .nav-toggle { display: none; background: var(--white); border: 1.5px solid var(--line); border-radius: 4px; padding: 9px; color: var(--navy); font-size: 22px; line-height: 0; }

  /* Hero */
  .hero { position: relative; min-height: 640px; padding: 138px 0 36px; background: #F6FAFD url("images/hero.jpg") left 15vw center / cover no-repeat; }
  .hero::before { content: ""; position: absolute; top: 0; right: 0; bottom: 0; left: 0; background: linear-gradient(90deg, #F6FAFD 0%, #F6FAFD 16%, rgba(246,250,253,.9) 30%, rgba(246,250,253,.4) 46%, rgba(246,250,253,0) 58%), linear-gradient(180deg, rgba(255,255,255,.8) 0, rgba(255,255,255,0) 130px); }
  .hero .wrap { position: relative; }
  .hero-copy { max-width: 500px; }
  .eyebrow { font-family: var(--display); font-weight: 700; font-size: 12px; letter-spacing: .14em; color: var(--navy); }
  .hero h1 { margin-top: 18px; line-height: .98; }
  .hero h1 .l1 { display: block; color: var(--orange); font-size: clamp(34px, 5vw, 50px); font-weight: 800; }
  .hero h1 .l2 { display: block; font-size: clamp(84px, 13vw, 132px); font-weight: 900; letter-spacing: -.035em; line-height: .86; margin-left: -4px; }
  .hero h1 .l3 { display: block; font-size: clamp(30px, 4.6vw, 46px); font-weight: 600; line-height: 1.06; margin-top: 8px; }
  .hero p { margin-top: 22px; max-width: 440px; font-size: 16px; color: var(--ink); }
  .hero-actions { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 26px; }
  .hero-actions .btn { padding: 16px 24px; }
  .hero-actions .btn-wa { text-align: left; line-height: 1.25; }

  /* Benefits strip */
  .benefits { border-bottom: 1px solid var(--line); }
  .benefits ul { display: grid; grid-template-columns: repeat(4, 1fr); }
  .benefits li { display: flex; align-items: center; gap: 16px; padding: 30px 20px; font-family: var(--display); font-weight: 600; font-size: 14px; line-height: 1.4; color: var(--navy); position: relative; }
  .benefits li + li::before { content: ""; position: absolute; left: 0; top: 28%; height: 44%; width: 1px; background: var(--line); }
  .benefits .ico { font-size: 38px; color: var(--orange); }

  /* Sections */
  .section { padding: 56px 0; }
  .section.mist { background: var(--mist); }
  .section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 30px; }
  .section-head h2 { font-size: clamp(30px, 4vw, 42px); font-weight: 800; letter-spacing: -.02em; line-height: 1.1; }
  .section-head p { margin-top: 12px; max-width: 620px; color: var(--muted); font-size: 16px; }

  /* Solutions */
  .solutions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
  .solution { background: var(--white); border: 1px solid var(--line); padding: 8px 8px 22px; display: flex; flex-direction: column; }
  .solution img { aspect-ratio: 4 / 3; width: 100%; object-fit: cover; }
  .solution h3 { font-size: 18px; font-weight: 700; margin: 20px 12px 8px; }
  .solution p { margin: 0 12px 20px; color: var(--muted); font-size: 15px; flex: 1; }
  .solution .btn { align-self: flex-start; margin-left: 12px; }

  /* Why */
  .why { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; text-align: center; }
  .why .badge { width: 62px; height: 62px; border-radius: 50%; background: var(--orange); color: var(--white); display: grid; place-items: center; font-size: 28px; margin: 0 auto 14px; box-shadow: 0 0 0 5px rgba(244,88,28,.14); }
  .why h3 { font-size: 16px; font-weight: 700; line-height: 1.3; }
  .why p { color: var(--muted); font-size: 15px; margin-top: 4px; }

  /* Package + Assessment */
  .split { display: grid; grid-template-columns: 45fr 55fr; }
  .package { background: var(--navy); color: var(--white); padding: 44px 40px 44px max(20px, calc((100vw - 1180px) / 2)); }
  .tag { display: inline-block; background: var(--orange); color: var(--white); font-family: var(--display); font-weight: 700; font-size: 11px; letter-spacing: .08em; padding: 6px 14px; border-radius: 3px; }
  .package h2 { color: var(--white); font-size: clamp(30px, 3.6vw, 38px); font-weight: 800; margin-top: 16px; }
  .package .lede { color: #D5DEE6; max-width: 330px; line-height: 1.3; margin-top: 4px; }
  .package .price { font-family: var(--display); font-weight: 900; font-size: clamp(40px, 5vw, 54px); color: var(--orange); line-height: 1.1; margin-top: 6px; }
  .package ul { margin: 16px 0 26px; display: grid; gap: 11px; }
  .package li { display: flex; align-items: center; gap: 12px; font-size: 16px; }
  .package li .ico { font-size: 22px; color: var(--orange); }
  .package-actions { display: flex; flex-wrap: wrap; gap: 14px; }
  .pricing-section { background: var(--navy); }
  .pricing-section .section-head h2 { color: var(--white); }
  .pricing-section .section-head p { color: #D5DEE6; }
  .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .tier { background: var(--white); display: flex; flex-direction: column; min-height: 100%; border-top: 7px solid var(--tier); }
  .tier-head { padding: 24px 24px 18px; border-bottom: 1px solid var(--line); }
  .tier h3 { font-size: 27px; font-weight: 900; }
  .tier .price { color: var(--tier); font-family: var(--display); font-size: clamp(31px, 4vw, 43px); font-weight: 900; line-height: 1; margin-top: 10px; }
  .tier ul { display: grid; gap: 14px; padding: 22px 24px; flex: 1; }
  .tier li { display: flex; gap: 11px; align-items: flex-start; color: var(--ink); }
  .tier li .ico { color: var(--tier); font-size: 21px; margin-top: 2px; }
  .tier .btn { margin: 0 24px 24px; background: var(--tier); color: var(--white); }
  .tier-bronze { --tier: #C75B08; }
  .tier-silver { --tier: #647786; }
  .tier-gold { --tier: #E5A900; }
  .assessment-band { background: var(--mist); }
  .assessment-band .assess { max-width: 1180px; margin-inline: auto; padding: 56px 20px; border: 0; }
  .assess { padding: 40px max(20px, calc((100vw - 1180px) / 2)) 40px 40px; border-block: 1px solid var(--line); }
  .assess h2 { font-size: clamp(26px, 3.2vw, 34px); font-weight: 800; }
  .assess .lede { color: var(--muted); max-width: 440px; margin-top: 8px; }
  .assess-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 24px; align-items: stretch; }
  .assess-steps { display: grid; gap: 20px; }
  .assess-steps li { display: flex; gap: 18px; align-items: center; }
  .assess-steps .ico { font-size: 30px; color: var(--orange); }
  .assess-steps strong { display: block; font-family: var(--display); font-size: 14px; color: var(--navy); }
  .assess-steps span { font-size: 13px; color: var(--muted); }
  .assess-grid img { width: 100%; height: 100%; min-height: 220px; object-fit: cover; object-position: 30% center; }
  .assess .btn { margin-top: 24px; min-width: 300px; padding-block: 16px; }

  /* Projects */
  .projects { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
  .project img { aspect-ratio: 2 / 1; width: 100%; object-fit: cover; }
  .project h3 { font-size: 14px; font-weight: 700; margin-top: 12px; }
  .project p { display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 13px; margin-top: 6px; }
  .project p .ico { color: var(--orange); font-size: 16px; }

  /* CTA band */
  .cta { background: var(--navy); color: var(--white); padding: 34px 0; }
  .cta .wrap { display: flex; align-items: center; justify-content: space-between; gap: 28px; flex-wrap: wrap; }
  .cta h2 { color: var(--white); font-size: clamp(26px, 3.4vw, 34px); font-weight: 800; }
  .cta p { color: #D5DEE6; max-width: 540px; margin-top: 6px; }
  .cta-actions { display: flex; flex-wrap: wrap; gap: 16px; }
  .cta-actions .btn { padding: 18px 26px; font-size: 14px; }

  /* Footer */
  .site-footer .wrap.cols { display: grid; grid-template-columns: 1.7fr 1fr 1.3fr 1.2fr; gap: 32px; padding-block: 36px 40px; }
  .site-footer .brand img { height: 76px; }
  .site-footer h4 { font-size: 13px; font-weight: 700; margin-bottom: 10px; }
  .site-footer li { font-size: 13px; color: var(--muted); line-height: 1.75; }
  .site-footer li a:hover { color: var(--orange); }
  .contact-list li { display: flex; align-items: center; gap: 10px; }
  .contact-list .ico { color: var(--navy); font-size: 15px; }
  .socials { display: flex; gap: 16px; margin-top: 14px; font-size: 22px; color: var(--navy); }
  .socials a:hover { color: var(--orange); }
  .legal { background: var(--navy); color: var(--white); font-size: 12px; padding: 18px 0; }
  .legal .wrap { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .legal a + a { margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.5); }

  /* Motion: first-load and scroll reveals (only when JS is running) */
  @keyframes rise { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: none; } }
  @keyframes drop { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: none; } }
  @keyframes wipe { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }
  @keyframes heroFade { from { opacity: 1; } to { opacity: 0; } }
  @keyframes pop { from { opacity: 0; transform: translateY(18px) scale(.97); } to { opacity: 1; transform: none; } }
  @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
  .js .site-header { animation: drop .7s cubic-bezier(.2,.7,.2,1) both; }
  .js .hero::after { content: ""; position: absolute; top: 0; right: 0; bottom: 0; left: 0; background: #F6FAFD; pointer-events: none; animation: heroFade 1.2s ease-out forwards; }
  .js .hero-copy > *, .js .hero h1 span { animation: rise .8s cubic-bezier(.2,.7,.2,1) both; }
  .js .hero-copy > .eyebrow { animation-delay: .15s; }
  .js .hero-copy > h1 { animation: none; }
  .js .hero h1 .l1 { animation-delay: .28s; }
  .js .hero h1 .l2 { animation: wipe .9s cubic-bezier(.6,0,.2,1) .4s both; }
  .js .hero h1 .l3 { animation-delay: .62s; }
  .js .hero-copy > p { animation-delay: .76s; }
  .js .hero-copy > .hero-actions { animation-delay: .9s; }
  .js [data-reveal] { opacity: 0; transform: translateY(30px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1), box-shadow .25s; transition-delay: var(--d, 0ms); }
  .js [data-reveal="zoom"] { transform: scale(.9); }
  .js [data-reveal].in { opacity: 1; transform: none; }
  .solution img, .project img { transition: transform .5s cubic-bezier(.2,.7,.2,1); }
  .solution .shot, .project .shot { overflow: hidden; display: block; }
  .solution:hover { box-shadow: 0 14px 34px rgba(11,42,64,.12); }
  .solution:hover img, .project:hover img { transform: scale(1.05); }
  .why .badge { transition: transform .3s cubic-bezier(.2,.7,.2,1); }
  .why > div:hover .badge { transform: translateY(-4px) scale(1.06); }
  dialog.la[open] { animation: pop .35s cubic-bezier(.2,.7,.2,1); }
  dialog.la[open]::backdrop { animation: fade .3s ease-out; }
  .la-step.on { animation: rise .35s cubic-bezier(.2,.7,.2,1); }

  /* Mobile drawer */
  .drawer-backdrop { position: fixed; top: 0; right: 0; bottom: 0; left: 0; z-index: 40; background: rgba(7,30,47,.6); opacity: 0; visibility: hidden; transition: opacity .3s, visibility .3s; }
  .drawer { position: fixed; top: 0; right: 0; bottom: 0; z-index: 50; width: min(340px, 88vw); background: var(--white); display: flex; flex-direction: column; transform: translateX(100%); visibility: hidden; transition: transform .35s cubic-bezier(.2,.7,.2,1), visibility .35s; box-shadow: -18px 0 50px rgba(7,30,47,.25); overflow-y: auto; }
  .drawer-open .drawer { transform: none; visibility: visible; }
  .drawer-open .drawer-backdrop { opacity: 1; visibility: visible; }
  body.drawer-open { overflow: hidden; }
  .drawer-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--line); }
  .drawer-head img { height: 48px; width: auto; }
  .drawer-close { background: var(--mist); border: 0; border-radius: 4px; padding: 9px; color: var(--navy); font-size: 22px; line-height: 0; }
  .drawer-close:hover { color: var(--orange); }
  .drawer-nav { padding: 8px 20px; }
  .drawer-nav a { display: flex; align-items: center; justify-content: space-between; font-family: var(--display); font-weight: 700; font-size: 16px; color: var(--navy); padding: 15px 0; border-bottom: 1px solid var(--line); opacity: 0; transform: translateX(18px); transition: opacity .35s, transform .35s cubic-bezier(.2,.7,.2,1), color .2s; }
  .drawer-nav a .ico { font-size: 16px; color: var(--orange); }
  .drawer-nav a:hover { color: var(--orange); }
  .drawer-open .drawer-nav a { opacity: 1; transform: none; transition-delay: calc(.08s + var(--i) * .045s); }
  .drawer-actions { display: grid; gap: 10px; padding: 18px 20px 24px; margin-top: auto; }
  .drawer-actions .btn { width: 100%; padding-block: 15px; font-size: 14px; }
  .drawer-actions p { font-size: 13px; color: var(--muted); text-align: center; margin-top: 4px; }

  /* Assessment dialog */
  dialog.la { border: 0; padding: 0; width: min(760px, 100% - 24px); max-height: min(92vh, 860px); max-height: min(92dvh, 860px); border-radius: 6px; color: var(--ink); background: var(--white); box-shadow: 0 30px 80px rgba(7,30,47,.45); overflow: hidden; }
  dialog.la:not([open]) { display: none; }
  dialog.la[open] { display: flex; flex-direction: column; }
  dialog.la::backdrop { background: rgba(7,30,47,.72); }
  /* older iPhones (Safari before 15.4) have no <dialog>: the script positions it by hand */
  dialog.la.la-polyfill[open] { position: fixed; top: 50%; left: 50%; margin: 0; z-index: 10001; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%); animation: fade .3s ease-out; }
  .la-backdrop { position: fixed; top: 0; right: 0; bottom: 0; left: 0; z-index: 10000; background: rgba(7,30,47,.72); }
  html.la-lock, html.la-lock body { overflow: hidden; }
  .la-head { background: var(--navy); color: var(--white); padding: 20px 26px 0; }
  .la-head .row { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
  .la-head h2 { color: var(--white); font-size: 22px; font-weight: 800; }
  .la-close { background: transparent; border: 0; color: var(--white); font-size: 24px; line-height: 0; padding: 6px; border-radius: 4px; }
  .la-close:hover { background: rgba(255,255,255,.14); }
  .la-progress { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-top: 16px; }
  .la-progress li { font-family: var(--display); font-size: 11px; font-weight: 600; color: #8FA3B4; padding-bottom: 12px; border-bottom: 3px solid rgba(255,255,255,.16); }
  .la-progress li.on { color: var(--white); border-bottom-color: var(--orange); }
  .la-body { padding: 26px; overflow-y: auto; flex: 1; min-height: 0; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
  .la-step { display: none; }
  .la-step.on { display: block; }
  .la-step h3 { font-size: 20px; font-weight: 800; }
  .la-step > p.hint { color: var(--muted); margin: 6px 0 20px; font-size: 15px; }
  .la-foot { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 26px; border-top: 1px solid var(--line); background: var(--mist); flex-wrap: wrap; }
  .la-live { font-family: var(--display); font-size: 13px; font-weight: 600; color: var(--navy); }
  .la-live b { color: var(--orange); font-weight: 800; }
  .la-nav { display: flex; gap: 10px; margin-left: auto; }
  /* lives in the footer, beside the Next button, so it is always in view on a phone */
  .la-error { flex: 0 0 100%; color: #B42318; font-size: 14px; font-weight: 600; background: #FEF3F2; border: 1px solid #FDA29B; border-radius: 4px; padding: 9px 12px; }
  .la-error:empty { display: none; }

  .choice-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .choice { position: relative; display: block; cursor: pointer; }
  /* longhands, not inset: Safari before iOS 14.1 ignores inset and the radio shrank to a dot */
  .choice input { position: absolute; opacity: 0; top: 0; left: 0; width: 100%; height: 100%; margin: 0; cursor: pointer; }
  .choice label, .choice .face { display: flex; align-items: center; gap: 14px; padding: 16px; border: 1.5px solid var(--line); border-radius: 4px; background: var(--white); transition: border-color .15s, background .15s; }
  .choice .ico { font-size: 28px; color: var(--orange); }
  .choice strong { display: block; font-family: var(--display); font-size: 15px; color: var(--navy); }
  .choice strong + span { font-size: 13px; color: var(--muted); }
  .choice input:checked + .face { border-color: var(--orange); background: #FFF4EE; }
  .choice input:focus-visible + .face { outline: 3px solid var(--amber); outline-offset: 2px; }

  .appl-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .appl { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1.5px solid var(--line); border-radius: 4px; }
  .appl.has { border-color: var(--orange); background: #FFF4EE; }
  .appl .name { font-weight: 700; font-size: 14px; color: var(--navy); }
  .appl .watts { font-size: 12px; color: var(--muted); }
  .stepper { display: flex; align-items: center; gap: 4px; flex: none; }
  .stepper button { width: 30px; height: 30px; border-radius: 4px; border: 1.5px solid var(--line); background: var(--white); color: var(--navy); font-weight: 800; font-size: 16px; line-height: 1; }
  .stepper button:hover { border-color: var(--orange); color: var(--orange); }
  .stepper output { width: 26px; text-align: center; font-family: var(--display); font-weight: 700; font-size: 14px; }
  .custom-add { display: grid; grid-template-columns: 1.6fr 1fr auto; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--line); align-items: end; }

  .field { display: grid; gap: 6px; }
  .field label { font-family: var(--display); font-weight: 600; font-size: 13px; color: var(--navy); }
  .field input, .field select { width: 100%; padding: 12px 12px; border: 1.5px solid var(--line); border-radius: 4px; font: inherit; color: var(--ink); background: var(--white); }
  .field input:focus, .field select:focus { border-color: var(--orange); }
  .field-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .field-grid .full { grid-column: 1 / -1; }

  .hours { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
  .hours th { font-family: var(--display); font-size: 12px; text-align: left; color: var(--muted); padding: 0 8px 8px 0; font-weight: 600; }
  .hours td { padding: 8px 8px 8px 0; border-top: 1px solid var(--line); font-size: 14px; }
  .hours td:first-child { font-weight: 700; color: var(--navy); }
  .hours input { width: 84px; padding: 9px 10px; border: 1.5px solid var(--line); border-radius: 4px; font: inherit; }

  .result-pkg { background: var(--navy); color: var(--white); padding: 22px; border-radius: 4px; }
  .result-pkg h3 { color: var(--white); font-size: 26px; margin-top: 12px; }
  .result-pkg .price { font-family: var(--display); font-weight: 900; font-size: clamp(22px, 5vw, 32px); line-height: 1.15; color: var(--orange); margin-top: 2px; }
  .result-pkg p { color: #D5DEE6; font-size: 14px; margin-top: 8px; }
  .metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 16px 0; }
  .metric { border: 1px solid var(--line); border-radius: 4px; padding: 12px; }
  .metric span { display: block; font-size: 12px; color: var(--muted); }
  .metric b { font-family: var(--display); font-size: 18px; color: var(--navy); font-weight: 800; }
  .result-list { font-size: 14px; color: var(--muted); columns: 2; column-gap: 24px; margin-bottom: 14px; }
  .result-list li { break-inside: avoid; padding: 2px 0; }
  .result-note { font-size: 13px; color: var(--muted); border-left: 3px solid var(--amber); padding-left: 12px; }
  .result-next { margin-top: 14px; padding: 12px 14px; background: #FFF4EE; border: 1px solid #F9C9B3; border-radius: 4px; font-size: 14px; font-weight: 600; color: var(--navy); }
  .result-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }

  @media (max-width: 1000px) {
    .nav { display: none; }
    .nav-toggle { display: inline-block; margin-left: auto; }
    .site-header .wrap { position: relative; }
    .solutions, .projects { grid-template-columns: repeat(2, 1fr); }
    .benefits ul { grid-template-columns: repeat(2, 1fr); }
    .benefits li:nth-child(3)::before { display: none; }
    .why { grid-template-columns: repeat(3, 1fr); row-gap: 30px; }
    .split { grid-template-columns: 1fr; }
    .package, .assess { padding: 40px 20px; }
    .pricing-grid { grid-template-columns: 1fr; }
    .site-footer .wrap.cols { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 640px) {
    .brand img, .site-header.scrolled .brand img { height: 44px; }
    .site-header .btn { display: none; }
    .hero { padding-top: 108px; min-height: 0; background-position: 78% center; background-size: cover; }
    .hero::before { background: linear-gradient(180deg, rgba(255,255,255,.93) 0%, rgba(255,255,255,.86) 62%, rgba(255,255,255,.55) 100%); }
    .section-head { flex-direction: column; }
    .solutions, .projects, .benefits ul { grid-template-columns: 1fr; }
    .benefits li { padding: 18px 4px; }
    .benefits li + li::before { top: 0; left: 0; width: 100%; height: 1px; display: block !important; }
    .why { grid-template-columns: repeat(2, 1fr); }
    .assess-grid { grid-template-columns: 1fr; }
    .assess .btn { min-width: 0; width: 100%; }
    .site-footer .wrap.cols { grid-template-columns: 1fr; }
    .site-footer .wrap.cols { text-align: center; justify-items: center; }
    .site-footer .brand img { margin-inline: auto; }
    .contact-list li, .socials { justify-content: center; }
    .legal .wrap { flex-direction: column; align-items: center; text-align: center; gap: 8px; }
    .choice-grid, .appl-list, .field-grid { grid-template-columns: 1fr; }
    .custom-add { grid-template-columns: 1fr 1fr; }
    .custom-add .btn { grid-column: 1 / -1; }
    .metrics { grid-template-columns: repeat(2, 1fr); }
    .result-list { columns: 1; }
    .la-progress li { font-size: 0; }
    .la-body { padding: 20px 16px; }
    /* iPhones zoom the page into any input under 16px */
    .hours input { font-size: 16px; }
    .la-foot { padding: 14px 16px; }
    .la-nav { width: 100%; }
    .la-nav .btn { flex: 0 1 auto; min-width: 0; padding-inline: 10px; gap: 6px; white-space: normal; }
    .la-nav #laNext { flex-grow: 1; }
  }
  @media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .btn { transition: none; }
    *, *::before, *::after { animation: none !important; transition: none !important; }
    .js [data-reveal] { opacity: 1; transform: none; }
    .js .hero::after { display: none; }
  }
</style>
</head>
<body>

<!-- Icon sprite -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-wa" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.7" d="M3.4 20.6l1.2-4.3A8.6 8.6 0 1 1 8 19.5z"/><path fill="currentColor" stroke="none" d="M9.3 7.6c-.2-.5-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.300s1 2.700 1.200 2.900c.1.2 2 3.200 5 4.300 2.500 1 3 .8 3.500.7.500 0 1.700-.7 1.900-1.400.2-.7.2-1.200.2-1.400-.1-.1-.3-.2-.6-.4l-2-1c-.3-.1-.5-.1-.7.2l-.9 1.100c-.2.2-.3.2-.6.1-.3-.2-1.200-.5-2.300-1.400-.9-.8-1.400-1.700-1.600-2-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5z"/></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
  <symbol id="i-back" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol>
  <symbol id="i-bolt" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M13.5 2L4 13.500h6L8.500 22 20 9.500h-6.500z"/></symbol>
  <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2.500l8 3v6c0 5-3.400 8.600-8 10-4.600-1.400-8-5-8-10v-6z"/><path d="M8.500 12l2.500 2.500 4.500-5"/></symbol>
  <symbol id="i-gear" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" fill-rule="evenodd" d="M10.300 2h3.400l.5 2.600 1.700.7 2.200-1.500 2.400 2.400-1.500 2.200.7 1.700 2.600.5v3.400l-2.600.5-.7 1.700 1.500 2.200-2.400 2.400-2.200-1.500-1.700.7-.5 2.600h-3.400l-.5-2.600-1.700-.7-2.200 1.500-2.400-2.400 1.500-2.200-.7-1.700L2 13.700v-3.400l2.600-.5.7-1.700-1.500-2.200 2.400-2.400 2.200 1.500 1.700-.7zM12 8.500a3.500 3.500 0 1 0 0 7 3.500 3.500 0 0 0 0-7z"/></symbol>
  <symbol id="i-leaf" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M20.500 3C11 3 4.500 7.500 4.500 14.500c0 1.200.3 2.300.8 3.200L3 20l1.400 1.400 2.300-2.300c1 .6 2.100.9 3.300.9 7 0 10.500-7 10.500-17zM8 16c2-4 5-6.500 9-8-3 2.500-5.500 5-7.500 9z"/></symbol>
  <symbol id="i-diamond" viewBox="0 0 24 24"><path d="M6 4h12l4 5.500L12 21 2 9.500z"/><path d="M2 9.500h20M9 4l-2 5.500L12 21l5-11.500L15 4"/></symbol>
  <symbol id="i-team" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"/><path d="M6 20c0-3.300 2.700-5.500 6-5.500s6 2.200 6 5.500"/><circle cx="5" cy="10" r="2"/><circle cx="19" cy="10" r="2"/><path d="M1.500 18c0-2 1.500-3.500 3.500-3.500M22.500 18c0-2-1.500-3.500-3.500-3.500"/></symbol>
  <symbol id="i-wrench" viewBox="0 0 24 24"><path d="M14.500 6.500a4.500 4.500 0 0 0-5.800 5.800L3 18l3 3 5.700-5.700a4.500 4.500 0 0 0 5.800-5.800l-3 3-2.500-2.500z"/></symbol>
  <symbol id="i-headset" viewBox="0 0 24 24"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.500" y="13" width="4" height="6" rx="1.500"/><rect x="17.500" y="13" width="4" height="6" rx="1.500"/><path d="M19.500 19c0 1.700-2 2.500-5 2.500"/></symbol>
  <symbol id="i-chart" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M4 13h4v8H4zM10 8h4v13h-4zM16 3h4v18h-4z"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="currentColor" stroke="none"/><path d="M7 12.500l3.300 3.300L17 9" stroke="#fff" stroke-width="2.600"/></symbol>
  <symbol id="i-home" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M12 2.500L1.500 12H5v9.500h5.500V15h3v6.500H19V12h3.500z"/></symbol>
  <symbol id="i-office" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M8 7h2M14 7h2M8 11h2M14 11h2M8 15h2M14 15h2M10 21v-3h4v3"/></symbol>
  <symbol id="i-shop" viewBox="0 0 24 24"><path d="M3 9l1.500-5h15L21 9M3 9c0 1.500 1.200 2.500 2.500 2.500S8 10.500 8 9c0 1.500 1.200 2.500 2.500 2.500h3C14.800 11.500 16 10.500 16 9c0 1.500 1.200 2.500 2.500 2.500S21 10.500 21 9M5 11.500V20h14v-8.500M10 20v-5h4v5"/></symbol>
  <symbol id="i-dots" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12h.01M12 12h.01M16 12h.01" stroke-width="2.600"/></symbol>
  <symbol id="i-plug" viewBox="0 0 24 24"><path d="M9 2v6M15 2v6M6 8h12v4a6 6 0 0 1-12 0zM12 18v4"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9.500"/><path d="M12 6.500V12l3.500 2.500"/></symbol>
  <symbol id="i-calc" viewBox="0 0 24 24"><rect x="4.500" y="2.500" width="15" height="19" rx="2"/><path d="M8 7h8M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01" stroke-width="2.400"/></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" fill-rule="evenodd" d="M12 2a7.500 7.500 0 0 0-7.500 7.500C4.500 15 12 22 12 22s7.500-7 7.500-12.500A7.500 7.500 0 0 0 12 2zm0 4.800a2.700 2.700 0 1 1 0 5.400 2.700 2.700 0 0 1 0-5.400z"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M6.600 2.500l3.200 1 1 4.500-2.300 2c1.200 2.400 3 4.200 5.500 5.500l2-2.300 4.500 1 1 3.200c-.8 2-2.300 3.100-4.500 3.100C9.500 20.500 3.500 14.500 3.500 7c0-2.200 1.100-3.700 3.100-4.500z"/></symbol>
  <symbol id="i-mail" viewBox="0 0 24 24"><rect x="2.500" y="5" width="19" height="14" rx="1.500"/><path d="M3 6.500l9 6.500 9-6.500"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></symbol>
  <symbol id="i-fb" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M12 2a10 10 0 0 0-1.600 19.900v-7H7.900V12h2.500V9.800c0-2.500 1.500-3.900 3.800-3.900 1.100 0 2.200.2 2.200.2v2.500h-1.200c-1.200 0-1.600.8-1.600 1.600V12h2.800l-.4 2.900h-2.400v7A10 10 0 0 0 12 2z"/></symbol>
  <symbol id="i-ig" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.500 6.500h.01" stroke-width="2.600"/></symbol>
  <symbol id="i-yt" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" fill-rule="evenodd" d="M4.500 5h15A2.500 2.500 0 0 1 22 7.500v9a2.500 2.500 0 0 1-2.500 2.500h-15A2.500 2.500 0 0 1 2 16.500v-9A2.500 2.500 0 0 1 4.500 5zM10 8.500v7l6-3.500z"/></symbol>
  <symbol id="i-xcom" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></symbol>
  <symbol id="i-li" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S.1 4.88.1 3.5 1.22 1 2.6 1s2.38 1.12 2.38 2.5zM.4 8h4.2v15.5H.4V8zm7.6 0h4v2.1h.06c.56-1.06 1.92-2.18 3.96-2.18 4.23 0 5.01 2.78 5.01 6.4v9.18h-4.2v-8.14c0-1.94-.04-4.44-2.7-4.44-2.7 0-3.12 2.11-3.12 4.3V23.5H8V8z"/></symbol>
  <symbol id="i-tt" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 1 1-2.59-2.59c.27 0 .53.04.78.12v-3.16a5.75 5.75 0 0 0-.78-.05 5.69 5.69 0 1 0 5.69 5.69V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3s-1.88.09-3.24-1.48z"/></symbol>
</svg>

<header class="site-header">
  <div class="wrap">
    <a class="brand" href="index.php" aria-label="KYNOS Solar Systems home"><img src="images/logo.png" alt="KYNOS Solar Systems, Powering Your Future Sustainably"></a>
    <nav class="nav" id="nav" aria-label="Main">
      <a href="index.php" class="active">Home</a>
      <a href="about.php">About Us</a>
      <a href="solutions.php">Solutions</a>
      <a href="installation.php">Installation</a>
      <a href="projects.php">Projects</a>
      <a href="contact.php">Contact</a>
    </nav>
    <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="drawer"><svg class="ico"><use href="#i-menu"/></svg></button>
    <a class="btn btn-wa" data-wa="Hello KYNOS Solar Systems, I would like a quote for a solar system." href="#contact"><svg class="ico wa"><use href="#i-wa"/></svg><span>Get a Quote</span></a>
  </div>
</header>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="drawer" id="drawer" aria-label="Menu" inert>
  <div class="drawer-head">
    <img src="images/logo.png" alt="KYNOS Solar Systems">
    <button class="drawer-close" type="button" id="drawerClose" aria-label="Close menu"><svg class="ico"><use href="#i-x"/></svg></button>
  </div>
  <nav class="drawer-nav" aria-label="Mobile">
    <a href="index.php" style="--i:0">Home<svg class="ico"><use href="#i-arrow"/></svg></a>
    <a href="about.php" style="--i:1">About Us<svg class="ico"><use href="#i-arrow"/></svg></a>
    <a href="solutions.php" style="--i:2">Solutions<svg class="ico"><use href="#i-arrow"/></svg></a>
    <a href="installation.php" style="--i:3">Installation<svg class="ico"><use href="#i-arrow"/></svg></a>
    <a href="projects.php" style="--i:4">Projects<svg class="ico"><use href="#i-arrow"/></svg></a>
    <a href="contact.php" style="--i:5">Contact<svg class="ico"><use href="#i-arrow"/></svg></a>
  </nav>
  <div class="drawer-actions">
    <a class="btn btn-wa" data-wa="Hello KYNOS Solar Systems, I would like a quote for a solar system." href="#contact"><svg class="ico wa"><use href="#i-wa"/></svg>Get a Quote</a>
    <button class="btn btn-primary" type="button" data-open-assessment>Start Load Assessment <svg class="ico"><use href="#i-arrow"/></svg></button>
    <a class="btn btn-outline" href="tel:+<?= htmlspecialchars($phoneIntl, ENT_QUOTES, 'UTF-8') ?>"><svg class="ico" style="font-size:18px"><use href="#i-phone"/></svg>Call <?= htmlspecialchars($phoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>
    <p>Solar packages from <?= htmlspecialchars($packagesFrom, ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</aside>

<main>
  <section class="hero" id="home">
    <div class="wrap">
      <div class="hero-copy">
        <p class="eyebrow">CLEAN ENERGY. LOWER BILLS. A BRIGHTER TOMORROW.</p>
        <h1><span class="l1">AFFORDABLE</span><span class="l2">SOLAR</span><span class="l3">SOLUTIONS FOR EVERYONE</span></h1>
        <p><?= htmlspecialchars(setting('page_home_hero_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="hero-actions">
          <a class="btn btn-wa" data-wa="Hello KYNOS Solar Systems, I am interested in a solar package from <?= htmlspecialchars($packagesFrom, ENT_QUOTES, 'UTF-8') ?>." href="#package"><svg class="ico wa"><use href="#i-wa"/></svg><span>Get a Solar Package<br>from <?= htmlspecialchars($packagesFrom, ENT_QUOTES, 'UTF-8') ?></span><svg class="ico"><use href="#i-arrow"/></svg></a>
          <button class="btn btn-outline" type="button" data-open-assessment>Request a Free Assessment <svg class="ico"><use href="#i-arrow"/></svg></button>
        </div>
      </div>
    </div>
  </section>

  <section class="benefits" aria-label="Benefits">
    <div class="wrap">
      <ul>
        <li><svg class="ico"><use href="#i-bolt"/></svg><span>Up to 70%<br>savings on your bills</span></li>
        <li><svg class="ico"><use href="#i-shield"/></svg><span>Premium quality<br>solar products</span></li>
        <li><svg class="ico"><use href="#i-gear"/></svg><span>Expert installation<br>&amp; lifetime support</span></li>
        <li><svg class="ico"><use href="#i-leaf"/></svg><span>Clean, reliable<br>and sustainable energy</span></li>
      </ul>
    </div>
  </section>

  <section class="section mist" id="solutions">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Our <span class="accent">Solutions</span></h2>
          <p><?= htmlspecialchars(setting('page_home_solutions_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a class="btn btn-outline btn-sm" href="solutions.php">View All Solutions <svg class="ico"><use href="#i-arrow"/></svg></a>
      </div>
      <div class="solutions">
        <article class="solution">
          <span class="shot"><img src="images/solar-panels.jpg" alt="Rooftop solar panels at sunset" loading="lazy"></span>
          <h3>Solar Panels</h3>
          <p>High efficiency solar panels for maximum energy production.</p>
          <a class="btn btn-outline btn-sm" href="solar-panels.php">View Panels <svg class="ico"><use href="#i-arrow"/></svg></a>
        </article>
        <article class="solution">
          <span class="shot"><img src="images/inverters.jpg" alt="KYNOS inverters beside a solar array" loading="lazy"></span>
          <h3>Inverters</h3>
          <p>Reliable and efficient inverters for homes, offices and businesses.</p>
          <a class="btn btn-outline btn-sm" href="inverters.php">View Inverters <svg class="ico"><use href="#i-arrow"/></svg></a>
        </article>
        <article class="solution">
          <span class="shot"><img src="images/batteries.jpg" alt="KYNOS lithium battery stacks" loading="lazy"></span>
          <h3>Lithium Batteries</h3>
          <p>Long-lasting lithium batteries for reliable backup power.</p>
          <a class="btn btn-outline btn-sm" href="lithium-batteries.php">View Batteries <svg class="ico"><use href="#i-arrow"/></svg></a>
        </article>
        <article class="solution">
          <span class="shot"><img src="images/installation.jpg" alt="KYNOS technicians installing solar panels on a roof" loading="lazy"></span>
          <h3>Installation Services</h3>
          <p>Professional solar installation tailored to your needs.</p>
          <a class="btn btn-outline btn-sm" href="installation.php">Learn More <svg class="ico"><use href="#i-arrow"/></svg></a>
        </article>
      </div>
    </div>
  </section>

  <section class="section" id="why">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Why Choose <span class="accent">KYNOS?</span></h2>
          <p><?= htmlspecialchars(setting('page_home_why_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
      <div class="why">
        <div><div class="badge"><svg class="ico"><use href="#i-diamond"/></svg></div><h3>Premium Quality Products</h3><p>Built to last</p></div>
        <div><div class="badge"><svg class="ico"><use href="#i-team"/></svg></div><h3>Certified Technicians</h3><p>Expert installation<br>&amp; maintenance</p></div>
        <div><div class="badge"><svg class="ico"><use href="#i-wrench"/></svg></div><h3>Custom Solutions</h3><p>Tailored to your<br>budget &amp; needs</p></div>
        <div><div class="badge"><svg class="ico"><use href="#i-headset"/></svg></div><h3>After-Sales Support</h3><p>We're here for you,<br>always!</p></div>
        <div><div class="badge"><svg class="ico"><use href="#i-chart"/></svg></div><h3>Save Up to 70%</h3><p>Reduce your<br>electricity bills</p></div>
      </div>
    </div>
  </section>

  <section class="section pricing-section" id="package">
    <div class="wrap">
      <div class="section-head"><div><h2>Choose a package that fits you</h2><p>Three reliable solar packages for Ghanaian homes and businesses.</p></div></div>
      <div class="pricing-grid">
        <?php foreach ($sitePackages as $index => $package): $tierClass = $packageTierClasses[$index] ?? 'tier-gold'; ?>
        <article class="tier <?= $tierClass ?>"><div class="tier-head"><h3><?= htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8') ?></h3><p class="price"><?= htmlspecialchars($package['price'], ENT_QUOTES, 'UTF-8') ?></p></div><ul><?php foreach ($package['items'] as $item): ?><li><svg class="ico"><use href="#i-check"/></svg><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><a class="btn" data-wa="Hello KYNOS Solar Systems, I would like the <?= htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8') ?> Package (<?= htmlspecialchars($package['price'], ENT_QUOTES, 'UTF-8') ?>)." href="#contact">Choose <?= htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8') ?></a></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="assessment-band" id="assessment">
    <div class="assess">
      <h2>Solar <span class="accent">Load Assessment</span></h2>
      <p class="lede">Tell us what you want to power and we'll recommend the right solar package for your home or business.</p>
      <div class="assess-grid">
        <div>
          <ul class="assess-steps">
            <li><svg class="ico"><use href="#i-home"/></svg><div><strong>Type of property</strong><span>Home, office or business</span></div></li>
            <li><svg class="ico"><use href="#i-plug"/></svg><div><strong>List your appliances</strong><span>What you want to power</span></div></li>
            <li><svg class="ico"><use href="#i-clock"/></svg><div><strong>Usage details</strong><span>Quantity and hours of use</span></div></li>
            <li><svg class="ico"><use href="#i-calc"/></svg><div><strong>Get a recommendation</strong><span>We'll suggest the right package</span></div></li>
          </ul>
          <button class="btn btn-primary" type="button" data-open-assessment>Start Load Assessment <svg class="ico"><use href="#i-arrow"/></svg></button>
        </div>
        <img src="images/solar-panels.jpg" alt="Solar panels catching the evening sun" loading="lazy">
      </div>
    </div>
  </section>

  <section class="section" id="projects">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Recent <span class="accent">Projects</span></h2>
          <p><?= htmlspecialchars(setting('page_home_projects_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a class="btn btn-outline btn-sm" href="projects.php">View All Projects <svg class="ico"><use href="#i-arrow"/></svg></a>
      </div>
      <div class="projects">
        <article class="project"><span class="shot"><img src="images/hero.jpg" alt="Home with a rooftop solar array" loading="lazy" style="object-position: 70% 30%"></span><h3>Residential Installation</h3><p><svg class="ico"><use href="#i-pin"/></svg>Accra, Ghana</p></article>
        <article class="project"><span class="shot"><img src="images/solar-panels.jpg" alt="Solar panels on a flat office roof" loading="lazy" style="object-position: 80% 60%"></span><h3>Office Solar Solution</h3><p><svg class="ico"><use href="#i-pin"/></svg>Accra, Ghana</p></article>
        <article class="project"><span class="shot"><img src="images/inverters.jpg" alt="Inverter and battery installation" loading="lazy" style="object-position: 70% 60%"></span><h3>Business Installation</h3><p><svg class="ico"><use href="#i-pin"/></svg>Accra, Ghana</p></article>
        <article class="project"><span class="shot"><img src="images/installation.jpg" alt="Technicians fitting panels on a commercial roof" loading="lazy" style="object-position: 80% 50%"></span><h3>Commercial Project</h3><p><svg class="ico"><use href="#i-pin"/></svg>Accra, Ghana</p></article>
      </div>
    </div>
  </section>

  <section class="cta" id="contact">
    <div class="wrap">
      <div>
        <h2><?= htmlspecialchars(setting('page_home_cta_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars(setting('page_home_cta_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-wa" data-wa="Hello KYNOS Solar Systems, I am ready to go solar. Please get in touch." href="#contact"><svg class="ico wa"><use href="#i-wa"/></svg>Chat on WhatsApp</a>
        <a class="btn btn-ghost" href="tel:+<?= htmlspecialchars($phoneIntl, ENT_QUOTES, 'UTF-8') ?>"><svg class="ico" style="font-size:20px"><use href="#i-phone"/></svg>Call <?= htmlspecialchars($phoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="wrap cols">
    <a class="brand" href="index.php"><img src="images/logo.png" alt="KYNOS Solar Systems, Powering Your Future Sustainably" loading="lazy"></a>
    <p class="muted" style="font-size:13px;color:var(--muted);margin-top:10px"><?= htmlspecialchars(setting('footer_blurb'), ENT_QUOTES, 'UTF-8') ?></p>
    <div>
      <h4>Quick Links</h4>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="solutions.php">Solutions</a></li>
        <li><a href="installation.php">Installation</a></li>
        <li><a href="projects.php">Projects</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="admin.php">Staff Login</a></li>
      </ul>
    </div>
    <div>
      <h4>Our Services</h4>
      <ul>
        <li><a href="solar-panels.php">Solar Panel Sales</a></li>
        <li><a href="inverters.php">Inverter Sales</a></li>
        <li><a href="lithium-batteries.php">Lithium Batteries</a></li>
        <li><a href="installation.php">Solar Installation</a></li>
        <li>Maintenance &amp; Support</li>
        <li>Custom Solar Solutions</li>
      </ul>
    </div>
    <div>
      <h4>Contact Us</h4>
      <ul class="contact-list">
        <li><svg class="ico"><use href="#i-pin"/></svg><?= htmlspecialchars(site_address(), ENT_QUOTES, 'UTF-8') ?></li>
        <li><svg class="ico"><use href="#i-phone"/></svg><a href="tel:+<?= htmlspecialchars($phoneIntl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($phoneDisplay, ENT_QUOTES, 'UTF-8') ?></a></li>
        <li><svg class="ico"><use href="#i-mail"/></svg><a href="mailto:<?= htmlspecialchars(site_email(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(site_email(), ENT_QUOTES, 'UTF-8') ?></a></li>
      </ul>
      <?php if ($socialLinks): ?>
      <div class="socials">
        <?php foreach ($socialLinks as $link): ?>
        <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="<?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>"><svg class="ico"><use href="#<?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?>"/></svg></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="legal">
    <div class="wrap">
      <span>&copy; <span id="year">2026</span> KYNOS Solar Systems. All rights reserved.</span>
      <span><a href="#">Privacy Policy</a><a href="#">Terms of Service</a><a href="https://princecaleb.dev" target="_blank" rel="noopener">Built by princecaleb.dev</a></span>
    </div>
  </div>
</footer>

<!-- Load assessment -->
<dialog class="la" id="la" aria-labelledby="laTitle">
  <div class="la-head">
    <div class="row">
      <h2 id="laTitle">Solar Load Assessment</h2>
      <button class="la-close" type="button" id="laClose" aria-label="Close assessment"><svg class="ico"><use href="#i-x"/></svg></button>
    </div>
    <ol class="la-progress" id="laProgress">
      <li>1. Property</li><li>2. Appliances</li><li>3. Usage</li><li>4. Your details</li><li>5. Recommendation</li>
    </ol>
  </div>

  <form class="la-body" id="laForm" novalidate>
    <section class="la-step" data-step="0">
      <h3>What type of property is it?</h3>
      <p class="hint">This helps us understand how the system will be used.</p>
      <div class="choice-grid">
        <label class="choice"><input type="radio" name="property" id="p-home" value="Home"><span class="face"><svg class="ico"><use href="#i-home"/></svg><span><strong>Home</strong><span>House or apartment</span></span></span></label>
        <label class="choice"><input type="radio" name="property" id="p-office" value="Office"><span class="face"><svg class="ico"><use href="#i-office"/></svg><span><strong>Office</strong><span>Workspace or clinic</span></span></span></label>
        <label class="choice"><input type="radio" name="property" id="p-shop" value="Business"><span class="face"><svg class="ico"><use href="#i-shop"/></svg><span><strong>Business</strong><span>Shop, restaurant, salon</span></span></span></label>
        <label class="choice"><input type="radio" name="property" id="p-other" value="Other"><span class="face"><svg class="ico"><use href="#i-dots"/></svg><span><strong>Other</strong><span>School, church, farm</span></span></span></label>
      </div>
    </section>

    <section class="la-step" data-step="1">
      <h3>What do you want to power?</h3>
      <p class="hint">Set how many of each appliance you have. Wattages are typical values, your installer will confirm the real ones.</p>
      <div class="appl-list" id="applList"></div>
      <div class="custom-add">
        <div class="field"><label for="cName">Something else?</label><input id="cName" type="text" maxlength="40" placeholder="Appliance name"></div>
        <div class="field"><label for="cWatts">Watts</label><input id="cWatts" type="number" min="1" max="20000" inputmode="numeric" placeholder="e.g. 300"></div>
        <button class="btn btn-outline btn-sm" type="button" id="cAdd" style="height:46px">Add</button>
      </div>
    </section>

    <section class="la-step" data-step="2">
      <h3>How do you use them?</h3>
      <p class="hint">Adjust the hours each appliance runs on a normal day.</p>
      <table class="hours">
        <thead><tr><th>Appliance</th><th>Qty</th><th>Hours per day</th></tr></thead>
        <tbody id="hoursBody"></tbody>
      </table>
      <div class="field">
        <label for="mode">How do you want to use solar?</label>
        <select id="mode" name="mode">
          <option value="backup">Backup when the power goes off</option>
          <option value="hybrid" selected>Solar by day, battery through the evening</option>
          <option value="offgrid">Fully off-grid, day and night</option>
        </select>
      </div>
    </section>

    <section class="la-step" data-step="3">
      <h3>Your details</h3>
      <p class="hint">So our team can confirm your recommendation and arrange a site visit.</p>
      <div class="field-grid">
        <div class="field"><label for="fName">Full name *</label><input id="fName" type="text" maxlength="60" autocomplete="name" required></div>
        <div class="field"><label for="fPhone">Telephone *</label><input id="fPhone" type="tel" maxlength="16" autocomplete="tel" placeholder="e.g. 053 040 9118" required></div>
        <div class="field"><label for="fLocation">Location *</label><input id="fLocation" type="text" maxlength="60" placeholder="Town or area, e.g. East Legon" required></div>
        <div class="field"><label for="fEmail">Email (optional)</label><input id="fEmail" type="email" maxlength="80" autocomplete="email"></div>
        <div class="field full"><label for="fNotes">Anything else we should know? (optional)</label><input id="fNotes" type="text" maxlength="200" placeholder="Roof type, budget, timeline"></div>
      </div>
    </section>

    <section class="la-step" data-step="4">
      <div id="resultBox"></div>
    </section>
  </form>

  <div class="la-foot">
    <p class="la-error" id="laError" role="alert"></p>
    <span class="la-live" id="laLive"></span>
    <div class="la-nav">
      <button class="btn btn-outline btn-sm" type="button" id="laBack"><svg class="ico"><use href="#i-back"/></svg>Back</button>
      <button class="btn btn-primary btn-sm" type="button" id="laNext">Next <svg class="ico"><use href="#i-arrow"/></svg></button>
    </div>
  </div>
</dialog>

<script>
(function () {
  "use strict";

  /* ---- Business settings ----
     Contact numbers and package names/prices come from the admin dashboard.
     The sizing limits below stay in code because they drive the calculator. */
  var CONFIG = {
    whatsapp: <?= json_encode($whatsappNumber) ?>,
    phoneDisplay: <?= json_encode($phoneDisplay) ?>,
    sunHours: 4.5,          // average peak sun hours per day in Ghana
    systemLoss: 0.75,       // panel-to-socket efficiency
    panelWatts: 620,
    batteryUsable: 0.81,    // depth of discharge x inverter efficiency
    inverterSizes: [1.5, 3, 4.2, 6, 8, 10, 12, 16, 20],
    // share of daily energy the battery must carry, per usage mode
    batteryShare: { backup: 0.35, hybrid: 0.5, offgrid: 1 },
    packages: <?= json_encode($jsPackages, JSON_UNESCAPED_SLASHES) ?>
  };
  var leadSaved = false;

  function saveAssessment(c, property, appliances) {
    if (leadSaved) return;
    var payload = {
      csrf_token: document.querySelector('meta[name="csrf-token"]').content,
      website: "",
      name: $("fName").value.trim(), phone: $("fPhone").value.trim(),
      location: $("fLocation").value.trim(), email: $("fEmail").value.trim(),
      notes: $("fNotes").value.trim(), property_type: property,
      usage_mode: c.mode, running_load_w: c.running,
      daily_energy_kwh: Number((c.daily / 1000).toFixed(2)),
      inverter_kva: c.kva, battery_kwh: c.battery, panel_count: c.panels,
      recommended_package: c.package ? c.package.name : "Custom Solar Package",
      appliances: appliances
    };
    fetch("submit-assessment.php", {
      method: "POST", headers: {"Content-Type": "application/json", "Accept": "application/json"},
      body: JSON.stringify(payload), credentials: "same-origin"
    }).then(function (response) {
      if (!response.ok) throw new Error("Save failed");
      leadSaved = true;
    }).catch(function () {
      /* WhatsApp remains available if the database is temporarily unavailable. */
    });
  }

  /* name, watts, default hours per day, duty cycle (share of time actually drawing power) */
  var APPLIANCES = [
    ["LED bulb", 10, 6, 1],
    ["Ceiling fan", 75, 8, 1],
    ["Standing fan", 60, 6, 1],
    ["Television", 100, 5, 1],
    ["Decoder / Wi-Fi router", 30, 10, 1],
    ["Phone charger", 10, 3, 1],
    ["Laptop", 65, 6, 1],
    ["Desktop computer", 200, 6, 1],
    ["Printer", 50, 1, 1],
    ["Fridge", 150, 24, 0.4],
    ["Deep freezer", 200, 24, 0.4],
    ["Air conditioner (1 HP)", 750, 6, 0.6],
    ["Air conditioner (1.5 HP)", 1100, 6, 0.6],
    ["Washing machine", 500, 1, 1],
    ["Microwave", 1000, 0.5, 1],
    ["Electric kettle", 1500, 0.25, 1],
    ["Electric iron", 1000, 0.5, 1],
    ["Blender", 400, 0.25, 1],
    ["Water pump (1 HP)", 750, 1, 1]
  ].map(function (a) { return { name: a[0], watts: a[1], hours: a[2], duty: a[3], qty: 0 }; });

  var $ = function (id) { return document.getElementById(id); };
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }
  function icon(id) {
    var s = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    s.setAttribute("class", "ico");
    var u = document.createElementNS("http://www.w3.org/2000/svg", "use");
    u.setAttribute("href", "#" + id);
    s.appendChild(u);
    return s;
  }
  function waLink(text) { return "https://wa.me/" + CONFIG.whatsapp + "?text=" + encodeURIComponent(text); }
  function num(n) { return Math.round(n).toLocaleString("en-GB"); }

  /* ---- Page chrome ---- */
  $("year").textContent = new Date().getFullYear();
  document.querySelectorAll("[data-wa]").forEach(function (a) {
    a.href = waLink(a.getAttribute("data-wa"));
    a.target = "_blank";
    a.rel = "noopener";
  });
  /* sticky header: solid background once scrolled */
  var header = document.querySelector(".site-header");
  function onScroll() {
    header.classList.toggle("scrolled", window.scrollY > 20);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  var drawer = $("drawer"), navToggle = $("navToggle");
  function setDrawer(open) {
    document.body.classList.toggle("drawer-open", open);
    drawer.inert = !open;
    navToggle.setAttribute("aria-expanded", open);
    if (open) $("drawerClose").focus(); else if (document.activeElement && drawer.contains(document.activeElement)) navToggle.focus();
  }
  navToggle.addEventListener("click", function () { setDrawer(true); });
  $("drawerClose").addEventListener("click", function () { setDrawer(false); });
  $("drawerBackdrop").addEventListener("click", function () { setDrawer(false); });
  drawer.addEventListener("click", function (e) { if (e.target.closest("a, [data-open-assessment]")) setDrawer(false); });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") setDrawer(false); });
  var wide = window.matchMedia("(min-width: 1001px)");
  function onWide(m) { if (m.matches) setDrawer(false); }
  // Safari before iOS 14 only has addListener; calling addEventListener there threw and stopped this whole script
  if (wide.addEventListener) wide.addEventListener("change", onWide); else if (wide.addListener) wide.addListener(onWide);

  /* ---- Scroll reveals ---- */
  (function () {
    var groups = [
      [".benefits li", 90], [".section-head", 0], [".solution", 110], [".why > div", 90, "zoom"],
      [".package > *", 70], [".assess > h2, .assess > .lede, .assess-steps li, .assess-grid > img, .assess-grid .btn", 80],
      [".project", 110], [".cta .wrap > *", 120], [".site-footer .cols > *", 90]
    ];
    var items = [];
    groups.forEach(function (g) {
      var parents = new Map();
      document.querySelectorAll(g[0]).forEach(function (n) {
        var key = n.closest("section, footer") || document.body;
        var i = parents.get(key) || 0;
        parents.set(key, i + 1);
        n.setAttribute("data-reveal", g[2] || "up");
        n.style.setProperty("--d", (i * g[1]) + "ms");
        items.push(n);
      });
    });
    function reveal(n) {
      n.classList.add("in");
      // drop the stagger delay once shown so hover effects respond instantly
      setTimeout(function () { n.style.removeProperty("--d"); }, 1600);
    }
    if (!("IntersectionObserver" in window)) { items.forEach(reveal); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { reveal(e.target); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    items.forEach(function (n) { io.observe(n); });
  })();

  /* ---- Load assessment ---- */
  var dlg = $("la"), step = 0, LAST = 4;
  var steps = dlg.querySelectorAll(".la-step");
  var progress = $("laProgress").children;
  var errorBox = $("laError"), live = $("laLive");
  var backBtn = $("laBack"), nextBtn = $("laNext");

  function selected() { return APPLIANCES.filter(function (a) { return a.qty > 0; }); }

  function calc() {
    var mode = $("mode").value;
    var running = 0, daily = 0;
    selected().forEach(function (a) {
      running += a.qty * a.watts;
      daily += a.qty * a.watts * a.hours * a.duty;
    });
    var needKva = running * 1.25 / 0.8 / 1000;
    var kva = null;
    for (var i = 0; i < CONFIG.inverterSizes.length; i++) {
      if (CONFIG.inverterSizes[i] >= needKva) { kva = CONFIG.inverterSizes[i]; break; }
    }
    var batteryNeed = daily / 1000 * CONFIG.batteryShare[mode] / CONFIG.batteryUsable;
    var battery = Math.max(2.4, Math.ceil(batteryNeed * 10) / 10);
    var panels = Math.max(2, Math.ceil(daily / (CONFIG.sunHours * CONFIG.systemLoss) / CONFIG.panelWatts));
    var matchedPackage = null;
    for (var p = 0; p < CONFIG.packages.length; p++) {
      var candidate = CONFIG.packages[p];
      if (kva !== null && kva <= candidate.maxKva && battery <= candidate.maxBatteryKwh && panels <= candidate.maxPanels) {
        matchedPackage = candidate;
        break;
      }
    }
    return {
      mode: mode, running: running, daily: daily, kva: kva, battery: battery, panels: panels,
      arrayKw: panels * CONFIG.panelWatts / 1000,
      package: matchedPackage
    };
  }

  function updateLive() {
    if (step === 1 || step === 2) {
      var c = calc();
      live.textContent = "";
      live.append("Running load: ", el("b", "", num(c.running) + " W"), "  |  Daily use: ", el("b", "", (c.daily / 1000).toFixed(1) + " kWh"));
    } else {
      live.textContent = "";
    }
  }

  function renderAppliances() {
    var list = $("applList");
    list.textContent = "";
    APPLIANCES.forEach(function (a) {
      var row = el("div", "appl" + (a.qty > 0 ? " has" : ""));
      var info = el("div");
      info.append(el("div", "name", a.name), el("div", "watts", num(a.watts) + " W each"));
      var st = el("div", "stepper");
      var minus = el("button", "", "−"), out = el("output", "", String(a.qty)), plus = el("button", "", "+");
      minus.type = plus.type = "button";
      minus.setAttribute("aria-label", "Fewer: " + a.name);
      plus.setAttribute("aria-label", "More: " + a.name);
      function set(q) {
        a.qty = Math.min(99, Math.max(0, q));
        out.textContent = a.qty;
        row.classList.toggle("has", a.qty > 0);
        errorBox.textContent = "";
        updateLive();
      }
      minus.addEventListener("click", function () { set(a.qty - 1); });
      plus.addEventListener("click", function () { set(a.qty + 1); });
      st.append(minus, out, plus);
      row.append(info, st);
      list.appendChild(row);
    });
  }

  function renderHours() {
    var body = $("hoursBody");
    body.textContent = "";
    selected().forEach(function (a) {
      var tr = el("tr");
      var input = el("input");
      input.type = "number"; input.min = "0"; input.max = "24"; input.step = "0.25";
      input.value = a.hours;
      input.setAttribute("aria-label", "Hours per day: " + a.name);
      input.addEventListener("input", function () {
        var v = parseFloat(input.value);
        a.hours = isNaN(v) ? 0 : Math.min(24, Math.max(0, v));
        updateLive();
      });
      var td = el("td"); td.appendChild(input);
      tr.append(el("td", "", a.name), el("td", "", String(a.qty)), td);
      body.appendChild(tr);
    });
  }

  var MODE_LABEL = { backup: "Backup when the power goes off", hybrid: "Solar by day, battery through the evening", offgrid: "Fully off-grid, day and night" };

  function renderResult() {
    var c = calc(), box = $("resultBox"), matched = c.package;
    var property = (dlg.querySelector("input[name=property]:checked") || {}).value || "";
    var name = $("fName").value.trim(), phone = $("fPhone").value.trim();
    var locationText = $("fLocation").value.trim(), email = $("fEmail").value.trim(), notes = $("fNotes").value.trim();
    var oversize = c.kva === null;
    var pkgName = matched ? matched.name : "Custom Solar Package";
    var kvaText = oversize ? "Above 20 kVA" : c.kva + " kVA";

    box.textContent = "";
    box.appendChild(el("h3", "", name ? name.split(" ")[0] + ", here is your recommendation" : "Your recommendation"));

    var pkg = el("div", "result-pkg");
    pkg.style.marginTop = "14px";
    pkg.append(el("span", "tag", "RECOMMENDED FOR YOU"), el("h3", "", pkgName));
    if (matched) {
      pkg.append(el("div", "price", matched.price), el("p", "", "Your estimated load fits this advertised package. Final sizing is confirmed after a free site assessment."));
    } else if (oversize) {
      pkg.append(el("div", "price", "Price sent after your message"), el("p", "", "Your load is larger than a standard package, so there is no fixed price to show here. Send us this assessment on WhatsApp and we will reply with your price once we receive your message."));
    } else {
      pkg.append(el("div", "price", "Price sent after your message"), el("p", "", "Your load needs a custom system beyond the advertised packages. Send us this assessment on WhatsApp and we will reply with your price once we receive your message."));
    }
    box.appendChild(pkg);

    var metrics = el("div", "metrics");
    [["Running load", num(c.running) + " W"], ["Daily energy", (c.daily / 1000).toFixed(1) + " kWh"],
     ["Inverter", kvaText], ["Battery", c.battery + " kWh"],
     ["Solar panels", c.panels + " x " + CONFIG.panelWatts + " W"], ["Array size", c.arrayKw.toFixed(1) + " kWp"]]
      .forEach(function (m) { var d = el("div", "metric"); d.append(el("span", "", m[0]), el("b", "", m[1])); metrics.appendChild(d); });
    box.appendChild(metrics);

    var ul = el("ul", "result-list");
    selected().forEach(function (a) { ul.appendChild(el("li", "", a.qty + " x " + a.name + ", " + a.hours + " h/day")); });
    box.appendChild(ul);
    box.appendChild(el("p", "result-note", "This is an estimate based on typical appliance wattages. Final sizing and price are confirmed after a free site assessment."));

    var msg = [
      "Hello KYNOS Solar Systems, I completed the Solar Load Assessment on your website.",
      "",
      "Name: " + name,
      "Telephone: " + phone,
      "Location: " + locationText
    ];
    if (email) msg.push("Email: " + email);
    msg.push("Property: " + property, "Usage: " + MODE_LABEL[c.mode], "", "Appliances:");
    selected().forEach(function (a) { msg.push("- " + a.qty + " x " + a.name + " (" + a.watts + " W), " + a.hours + " h/day"); });
    msg.push("", "Running load: " + num(c.running) + " W", "Daily energy: " + (c.daily / 1000).toFixed(1) + " kWh",
      "Suggested system: " + kvaText + " inverter, " + c.battery + " kWh battery, " + c.panels + " x " + CONFIG.panelWatts + " W panels",
      "Suggested package: " + pkgName + (matched ? " (" + matched.price + ")" : ""));
    if (notes) msg.push("", "Notes: " + notes);
    if (!matched) msg.push("", "Please send me the price for this system.");

    saveAssessment(c, property, selected().map(function (a) {
      return { name: a.name, quantity: a.qty, watts: a.watts, hours_per_day: a.hours };
    }));

    if (!matched) box.appendChild(el("p", "result-next", "Next step: tap the green button to send this to KYNOS. Your price comes back to you on WhatsApp after we receive it."));
    var actions = el("div", "result-actions");
    var send = el("a", "btn btn-wa");
    send.href = waLink(msg.join("\n")); send.target = "_blank"; send.rel = "noopener";
    var wa = icon("i-wa"); wa.classList.add("wa");
    send.append(wa, "Send to KYNOS on WhatsApp");
    var call = el("a", "btn btn-outline");
    call.href = "tel:+" + CONFIG.whatsapp;
    call.append(icon("i-phone"), "Call " + CONFIG.phoneDisplay);
    actions.append(send, call);
    box.appendChild(actions);
  }

  function validate() {
    if (step === 0 && !dlg.querySelector("input[name=property]:checked")) return "Please choose a property type.";
    if (step === 1 && !selected().length) return "Add at least one appliance to continue.";
    if (step === 2 && calc().daily <= 0) return "Enter the hours of use for at least one appliance.";
    if (step === 3) {
      if ($("fName").value.trim().length < 2) return "Please enter your full name.";
      if (!/^(\+?233|0)\d{9}$/.test($("fPhone").value.replace(/[\s-]/g, ""))) return "Please enter a valid Ghana phone number, e.g. 053 040 9118.";
      if ($("fLocation").value.trim().length < 2) return "Please enter your location.";
      var email = $("fEmail").value.trim();
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return "That email address does not look right.";
    }
    return "";
  }

  function show(n) {
    step = n;
    steps.forEach(function (s, i) { s.classList.toggle("on", i === n); });
    for (var i = 0; i < progress.length; i++) progress[i].classList.toggle("on", i <= n);
    if (n === 2) renderHours();
    if (n === LAST) renderResult();
    backBtn.style.visibility = n === 0 ? "hidden" : "visible";
    nextBtn.textContent = "";
    if (n === LAST) nextBtn.append("Start again");
    else nextBtn.append(n === 3 ? "See my recommendation " : "Next ", icon("i-arrow"));
    errorBox.textContent = "";
    updateLive();
    $("laForm").scrollTop = 0;
  }

  function reset() {
    leadSaved = false;
    APPLIANCES = APPLIANCES.filter(function (a) { return !a.custom; });
    APPLIANCES.forEach(function (a) { a.qty = 0; });
    $("laForm").reset();
    renderAppliances();
    show(0);
  }

  nextBtn.addEventListener("click", function () {
    if (step === LAST) { reset(); return; }
    var err = validate();
    if (err) { errorBox.textContent = err; return; }
    show(step + 1);
  });
  backBtn.addEventListener("click", function () { if (step > 0) show(step - 1); });
  $("laForm").addEventListener("submit", function (e) { e.preventDefault(); nextBtn.click(); });
  $("laForm").addEventListener("change", function () { errorBox.textContent = ""; updateLive(); });

  $("cAdd").addEventListener("click", function () {
    var name = $("cName").value.trim(), watts = parseInt($("cWatts").value, 10);
    if (!name || !(watts > 0) || watts > 20000) { errorBox.textContent = "Enter an appliance name and its wattage (1 to 20,000 W)."; return; }
    APPLIANCES.push({ name: name, watts: watts, hours: 4, duty: 1, qty: 1, custom: true });
    $("cName").value = ""; $("cWatts").value = "";
    renderAppliances();
    errorBox.textContent = "";
    updateLive();
    $("applList").lastElementChild.scrollIntoView({ block: "nearest" });
  });

  /* Safari before iOS 15.4 has no <dialog>, so fall back to a fixed panel and backdrop */
  var nativeDialog = typeof dlg.showModal === "function", laBackdrop = null;
  function openDialog() {
    document.documentElement.classList.add("la-lock");
    if (nativeDialog) { dlg.showModal(); return; }
    if (!laBackdrop) {
      laBackdrop = el("div", "la-backdrop");
      laBackdrop.addEventListener("click", closeDialog);
      document.body.appendChild(laBackdrop);
    }
    laBackdrop.hidden = false;
    dlg.classList.add("la-polyfill");
    dlg.setAttribute("open", "");
    $("laClose").focus();
  }
  function closeDialog() {
    document.documentElement.classList.remove("la-lock");
    if (nativeDialog) { if (dlg.open) dlg.close(); return; }
    dlg.removeAttribute("open");
    if (laBackdrop) laBackdrop.hidden = true;
  }
  document.querySelectorAll("[data-open-assessment]").forEach(function (btn) {
    btn.addEventListener("click", openDialog);
  });
  $("laClose").addEventListener("click", closeDialog);
  dlg.addEventListener("click", function (e) { if (e.target === dlg) closeDialog(); });
  // native dialogs also close on Escape, which skips closeDialog
  dlg.addEventListener("close", function () { document.documentElement.classList.remove("la-lock"); });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape" && !nativeDialog && dlg.hasAttribute("open")) closeDialog(); });

  renderAppliances();
  show(0);
})();
</script>
</body>
</html>
