<?php
require __DIR__ . '/app/site-layout.php';
$phone = site_phone();
$email = site_email();
render_site_page([
    'title' => 'Talk to KYNOS', 'active' => 'contact',
    'description' => 'Contact KYNOS Solar Systems by WhatsApp, phone or email for solar products, assessments and installation in Ghana.',
    'intro' => setting('page_contact_intro', 'Tell us what you want to power, where the property is and when you would like to get started.'),
    'image' => 'images/solar-panels.jpg', 'image_alt' => 'Solar panels ready to provide clean energy',
    'message' => 'Hello KYNOS Solar Systems, I would like to discuss a solar system for my property.',
    'sections' => [
        ['title' => 'Contact details', 'text' => 'Call ' . $phone . ' or email ' . $email . '. Site inspections are available in ' . site_address() . '.', 'items' => ['WhatsApp: ' . $phone, 'Telephone: ' . $phone, 'Email: ' . $email, 'Service area: ' . site_address()]],
        ['title' => 'Help us prepare for your enquiry', 'text' => 'Include your property type, location, important appliances and whether you need backup during outages or broader daily solar use.'],
    ],
]);
