<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Solar project capabilities', 'active' => 'projects',
    'description' => 'Explore residential, office, business and commercial solar installation capabilities from KYNOS Solar Systems.',
    'intro' => 'KYNOS designs solar solutions around the different demands of residential, office, business and commercial properties.',
    'image' => 'images/installation.jpg', 'image_alt' => 'Solar installation team working on a rooftop',
    'message' => 'Hello KYNOS Solar Systems, I would like to discuss a solar project.',
    'cards_title' => 'Systems for different properties',
    'cards_intro' => 'Each project begins with the loads, usage pattern and physical site.',
    'cards' => [
        ['title' => 'Residential systems', 'text' => 'Solar and backup planning for houses and apartments.', 'image' => 'images/hero.jpg', 'url' => 'contact.php'],
        ['title' => 'Office systems', 'text' => 'Power planning for workstations, connectivity and daily operations.', 'image' => 'images/solar-panels.jpg', 'url' => 'contact.php'],
        ['title' => 'Business systems', 'text' => 'Solutions shaped around operating hours and essential equipment.', 'image' => 'images/inverters.jpg', 'url' => 'contact.php'],
        ['title' => 'Commercial installations', 'text' => 'Assessment-led installations for larger and more complex loads.', 'image' => 'images/installation.jpg', 'url' => 'contact.php'],
    ],
    'sections' => [['title' => 'Discuss your site with KYNOS', 'text' => 'Share the property type, location and appliances you need to power. KYNOS can then arrange the next assessment step.', 'items' => ['Home or apartment', 'Office or clinic', 'Shop, restaurant or salon', 'School, church, farm or another property type']]],
]);
