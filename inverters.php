<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Hybrid inverters', 'active' => 'solutions',
    'description' => 'Reliable hybrid solar inverters for Ghanaian homes, offices and businesses from KYNOS Solar Systems.',
    'intro' => 'Manage energy from your solar panels, battery and the grid with an inverter matched to your running load.',
    'image' => 'images/inverters.jpg', 'image_alt' => 'Hybrid solar inverters',
    'parent' => ['label' => 'All solutions', 'url' => 'solutions.php'],
    'message' => 'Hello KYNOS Solar Systems, I would like help choosing a hybrid inverter.',
    'sections' => [
        ['title' => 'The control centre of the system', 'text' => 'A hybrid inverter converts panel and battery power for your appliances while coordinating charging and available grid supply.', 'items' => ['4.2kW inverter in the Bronze package', '6kW inverter in the Silver and Gold packages', 'Sized against appliance running load', 'Integrated with compatible panels and lithium storage']],
        ['title' => 'Sizing matters', 'text' => 'Choosing only by price can leave a system underpowered or unnecessarily large. Use the load assessment to estimate demand, then confirm the design with KYNOS.'],
    ],
]);
