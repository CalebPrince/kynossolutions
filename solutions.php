<?php
require __DIR__ . '/app/site-layout.php';
require __DIR__ . '/app/site-data.php';
render_site_page([
    'title' => 'Solar solutions built around your property',
    'description' => 'Explore KYNOS solar panels, hybrid inverters, lithium batteries and professional solar installation services in Ghana.',
    'active' => 'solutions',
    'intro' => 'Panels, storage, power control and installation work together as one system. Explore each part, then use our load assessment to find the right fit.',
    'image' => 'images/hero.jpg',
    'image_alt' => 'Ghanaian home powered by rooftop solar panels',
    'message' => 'Hello KYNOS Solar Systems, I would like help choosing the right solar solution.',
    'cards' => solution_cards(),
    'cards_title' => 'Everything your solar system needs',
    'cards_intro' => 'Learn what each component does and how it supports reliable power for your property.',
    'sections' => [[
        'title' => 'Start with what you need to power',
        'text' => 'A useful solar recommendation begins with your actual appliances, daily usage and backup expectations—not a package name alone.',
        'items' => ['List the appliances you want to run', 'Estimate how many hours each appliance is used', 'Choose backup, hybrid or off-grid use', 'Confirm the final design during a site assessment'],
    ]],
    'packages' => solar_packages(),
]);
