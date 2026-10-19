<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Lithium batteries', 'active' => 'solutions',
    'description' => 'Lithium solar batteries for dependable backup and evening power from KYNOS Solar Systems in Ghana.',
    'intro' => 'Store solar energy for evening use and power interruptions with battery capacity chosen around your priorities.',
    'image' => 'images/batteries.jpg', 'image_alt' => 'Lithium solar battery units',
    'parent' => ['label' => 'All solutions', 'url' => 'solutions.php'],
    'message' => 'Hello KYNOS Solar Systems, I would like to know more about your lithium batteries.',
    'sections' => [
        ['title' => 'Power when the panels are not producing', 'text' => 'Your battery carries selected loads when sunlight is low or grid power is unavailable. Required capacity depends on energy use and the duration of backup you expect.', 'items' => ['24V 100Ah battery in the Bronze package', '5.12kWh battery in the Silver package', '10.24kWh battery in the Gold package', 'Capacity confirmed against actual usage']],
        ['title' => 'Choose by usable energy, not guesswork', 'text' => 'Two properties with similar appliances can need different storage because operating hours differ. The website assessment provides an estimate before the site visit.'],
    ],
]);
