<?php
declare(strict_types=1);

function solution_cards(): array
{
    return [
        ['title' => 'Solar panels', 'text' => 'High-efficiency panels that turn Ghana’s abundant sunlight into useful power.', 'image' => 'images/solar-panels.jpg', 'url' => 'solar-panels.php'],
        ['title' => 'Hybrid inverters', 'text' => 'Reliable control between solar generation, batteries, grid power and your appliances.', 'image' => 'images/inverters.jpg', 'url' => 'inverters.php'],
        ['title' => 'Lithium batteries', 'text' => 'Long-lasting energy storage for evening use and dependable backup power.', 'image' => 'images/batteries.jpg', 'url' => 'lithium-batteries.php'],
        ['title' => 'Installation services', 'text' => 'Site assessment, system sizing and professional installation for your property.', 'image' => 'images/installation.jpg', 'url' => 'installation.php'],
    ];
}

function solar_packages(): array
{
    return [
        ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => ['5 × 620W solar panels', '1 × 24V 100Ah lithium battery', '1 × 4.2kW hybrid inverter']],
        ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => ['6 × 620W solar panels', '1 × 5.12kWh lithium battery', '1 × 6kW hybrid inverter']],
        ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => ['10 × 620W solar panels', '1 × 10.24kWh lithium battery', '1 × 6kW hybrid inverter']],
    ];
}
