<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Solar installation', 'active' => 'solutions',
    'description' => 'Professional solar assessment and installation services for homes, offices and businesses in Accra and Tema.',
    'intro' => 'Move from an appliance list to a complete installed solar system with assessment, sizing and installation support.',
    'image' => 'images/installation.jpg', 'image_alt' => 'Technicians installing rooftop solar panels',
    'parent' => ['label' => 'All solutions', 'url' => 'solutions.php'],
    'message' => 'Hello KYNOS Solar Systems, I would like to arrange a solar installation assessment.',
    'sections' => [
        ['title' => 'A practical path to installation', 'text' => 'KYNOS starts with what you need to power, reviews the property and confirms the system before installation.', 'items' => ['Initial appliance and usage assessment', 'Site inspection in Accra and Tema', 'System and package confirmation', 'Professional installation and handover']],
        ['title' => 'Final sizing happens on site', 'text' => 'Online results are estimates. Roof condition, cable routes, equipment location and actual appliance ratings are checked before the final design and price are confirmed.'],
    ],
]);
