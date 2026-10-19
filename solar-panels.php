<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Solar panels', 'active' => 'solutions',
    'description' => 'High-efficiency solar panels supplied and installed by KYNOS Solar Systems for homes, offices and businesses in Ghana.',
    'intro' => 'Capture Ghana’s abundant sunlight with a panel array sized for the energy your property actually uses.',
    'image' => 'images/solar-panels.jpg', 'image_alt' => 'Solar panel array in evening sunlight',
    'parent' => ['label' => 'All solutions', 'url' => 'solutions.php'],
    'message' => 'Hello KYNOS Solar Systems, I would like to know more about your 620W solar panels.',
    'sections' => [
        ['title' => 'The generation side of your system', 'text' => 'Solar panels produce the energy that powers your appliances and charges your battery. The right array size depends on daily energy use, available roof space and sunlight exposure.', 'items' => ['620W panels used in current advertised packages', 'Suitable for residential and business systems', 'Array sizing based on estimated daily consumption', 'Roof and placement confirmed during assessment']],
        ['title' => 'Designed as part of a complete system', 'text' => 'Panels work best when matched correctly to the inverter and battery. KYNOS assesses the complete load before confirming final quantities and installation details.'],
    ],
]);
