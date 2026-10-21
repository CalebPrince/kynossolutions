<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/app/site-layout.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
/* sizing limits per advertised package, in the same order as solar_packages() */
$packageLimits = [
    ['maxKva' => 4.2, 'maxBatteryKwh' => 2.4, 'maxPanels' => 5],
    ['maxKva' => 6, 'maxBatteryKwh' => 5.12, 'maxPanels' => 6],
    ['maxKva' => 6, 'maxBatteryKwh' => 10.24, 'maxPanels' => 10],
];
$packages = [];
foreach (solar_packages() as $index => $package) {
    $limit = $packageLimits[$index] ?? $packageLimits[count($packageLimits) - 1];
    $packages[] = [
        'name' => $package['name'] . ' Package',
        'price' => $package['price'],
        'maxKva' => $limit['maxKva'],
        'maxBatteryKwh' => $limit['maxBatteryKwh'],
        'maxPanels' => $limit['maxPanels'],
    ];
}
$settings = [
    'whatsapp' => phone_to_intl(setting('whatsapp')),
    'phoneDisplay' => setting('phone'),
    'packages' => $packages,
    'csrfToken' => $_SESSION['csrf_token'],
];

site_page_start([
    'title' => 'Solar Load Assessment',
    'description' => 'Tell us what you want to power and get a recommended KYNOS solar package for your home, office or business in Ghana.',
    'styles' => ['assets/css/assessment.css'],
]);
?>
    <script type="application/json" id="laSettings"><?= json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <svg class="svg-sprite" aria-hidden="true">
      <symbol id="i-back" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol>
      <symbol id="i-home" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M12 2.500L1.500 12H5v9.500h5.500V15h3v6.500H19V12h3.500z"/></symbol>
      <symbol id="i-office" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M8 7h2M14 7h2M8 11h2M14 11h2M8 15h2M14 15h2M10 21v-3h4v3"/></symbol>
      <symbol id="i-shop" viewBox="0 0 24 24"><path d="M3 9l1.500-5h15L21 9M3 9c0 1.500 1.200 2.500 2.500 2.500S8 10.500 8 9c0 1.500 1.200 2.500 2.500 2.500h3C14.800 11.500 16 10.500 16 9c0 1.500 1.200 2.500 2.500 2.500S21 10.500 21 9M5 11.500V20h14v-8.500M10 20v-5h4v5"/></symbol>
      <symbol id="i-dots" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12h.01M12 12h.01M16 12h.01" stroke-width="2.600"/></symbol>
      <symbol id="i-phone" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M6.600 2.500l3.200 1 1 4.500-2.300 2c1.200 2.400 3 4.200 5.500 5.500l2-2.300 4.500 1 1 3.200c-.8 2-2.300 3.100-4.500 3.100C9.500 20.500 3.500 14.500 3.500 7c0-2.200 1.100-3.700 3.100-4.500z"/></symbol>
    </svg>
    <section class="assess-page">
      <div class="shell">
        <div class="intro">
          <h1>Solar <span class="accent">Load Assessment</span></h1>
          <p>Tell us what you want to power and we'll recommend the right solar package for your home or business.</p>
        </div>
      <div class="la" id="la">
        <div class="la-head">
          <div class="la-tabs" role="tablist" id="laTabs" aria-label="Assessment steps">
            <button type="button" role="tab" id="laTab0" aria-controls="laStep0"><span class="n">1.</span><span class="t">Property</span></button>
            <button type="button" role="tab" id="laTab1" aria-controls="laStep1"><span class="n">2.</span><span class="t">Appliances</span></button>
            <button type="button" role="tab" id="laTab2" aria-controls="laStep2"><span class="n">3.</span><span class="t">Usage</span></button>
            <button type="button" role="tab" id="laTab3" aria-controls="laStep3"><span class="n">4.</span><span class="t">Your details</span></button>
            <button type="button" role="tab" id="laTab4" aria-controls="laStep4"><span class="n">5.</span><span class="t">Recommendation</span></button>
          </div>
        </div>

        <form class="la-body" id="laForm" novalidate>
          <section class="la-step" data-step="0" id="laStep0" role="tabpanel" aria-labelledby="laTab0">
            <h3>What type of property is it?</h3>
            <p class="hint">This helps us understand how the system will be used.</p>
            <div class="choice-grid">
              <label class="choice"><input type="radio" name="property" id="p-home" value="Home"><span class="face"><svg class="ico"><use href="#i-home"/></svg><span><strong>Home</strong><span>House or apartment</span></span></span></label>
              <label class="choice"><input type="radio" name="property" id="p-office" value="Office"><span class="face"><svg class="ico"><use href="#i-office"/></svg><span><strong>Office</strong><span>Workspace or clinic</span></span></span></label>
              <label class="choice"><input type="radio" name="property" id="p-shop" value="Business"><span class="face"><svg class="ico"><use href="#i-shop"/></svg><span><strong>Business</strong><span>Shop, restaurant, salon</span></span></span></label>
              <label class="choice"><input type="radio" name="property" id="p-other" value="Other"><span class="face"><svg class="ico"><use href="#i-dots"/></svg><span><strong>Other</strong><span>School, church, farm</span></span></span></label>
            </div>
          </section>

          <section class="la-step" data-step="1" id="laStep1" role="tabpanel" aria-labelledby="laTab1">
            <h3>What do you want to power?</h3>
            <p class="hint">Set how many of each appliance you have. Wattages are typical values, your installer will confirm the real ones.</p>
            <div class="appl-list" id="applList"></div>
            <div class="custom-add">
              <div class="field"><label for="cName">Something else?</label><input id="cName" type="text" maxlength="40" placeholder="Appliance name"></div>
              <div class="field"><label for="cWatts">Watts</label><input id="cWatts" type="number" min="1" max="20000" inputmode="numeric" placeholder="e.g. 300"></div>
              <button class="btn btn-outline btn-sm" type="button" id="cAdd" style="height:46px">Add</button>
            </div>
          </section>

          <section class="la-step" data-step="2" id="laStep2" role="tabpanel" aria-labelledby="laTab2">
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

          <section class="la-step" data-step="3" id="laStep3" role="tabpanel" aria-labelledby="laTab3">
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

          <section class="la-step" data-step="4" id="laStep4" role="tabpanel" aria-labelledby="laTab4">
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
      </div>
      </div>
    </section>
<?php
site_page_end(['assets/js/assessment.js']);
