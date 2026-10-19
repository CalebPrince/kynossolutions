<?php
require __DIR__ . '/app/site-layout.php';
render_site_page([
    'title' => 'Reliable solar power for Ghana', 'active' => 'about',
    'description' => 'Learn about KYNOS Solar Systems and its approach to solar solutions for Ghanaian homes and businesses.',
    'intro' => 'KYNOS Solar Systems helps homes, offices and businesses move toward reliable, affordable and sustainable energy.',
    'image' => 'images/hero.jpg', 'image_alt' => 'Modern home with rooftop solar panels',
    'message' => 'Hello KYNOS Solar Systems, I would like to learn more about your company and services.',
    'sections' => [
        ['title' => 'Built around practical energy needs', 'text' => 'A solar system should reflect what a customer needs to power, when that power is needed and how much backup is expected.', 'items' => ['Solar products for homes and businesses', 'Load-based system recommendations', 'Professional installation', 'Maintenance and ongoing support']],
        ['title' => 'From first question to final installation', 'text' => 'KYNOS combines product supply, load assessment and installation support so customers can make one connected decision instead of piecing together separate components.'],
    ],
]);
