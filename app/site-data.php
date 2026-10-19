<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/settings.php';

function site_phone(): string
{
    return setting('phone', '053 040 9118');
}

function site_phone_intl(): string
{
    return phone_to_intl(site_phone());
}

function site_whatsapp(): string
{
    return phone_to_intl(setting('whatsapp', '233530409118'));
}

function site_email(): string
{
    return setting('email', 'info@kynossolutions.com');
}

function site_address(): string
{
    return setting('address', 'Accra & Tema, Ghana');
}

function wa(string $message): string
{
    return 'https://wa.me/' . site_whatsapp() . '?text=' . rawurlencode($message);
}

/**
 * @return array<int, array{key: string, label: string, icon: string, href: string}>
 */
function site_social_links(): array
{
    $networks = [
        'social_facebook' => ['label' => 'Facebook', 'icon' => 'i-fb'],
        'social_instagram' => ['label' => 'Instagram', 'icon' => 'i-ig'],
        'social_youtube' => ['label' => 'YouTube', 'icon' => 'i-yt'],
        'social_x' => ['label' => 'X', 'icon' => 'i-xcom'],
        'social_linkedin' => ['label' => 'LinkedIn', 'icon' => 'i-li'],
        'social_tiktok' => ['label' => 'TikTok', 'icon' => 'i-tt'],
    ];
    $links = [];
    foreach ($networks as $key => $meta) {
        $href = setting($key, '');
        if ($href !== '') {
            $links[] = ['key' => $key, 'label' => $meta['label'], 'icon' => $meta['icon'], 'href' => $href];
        }
    }
    return $links;
}

function solution_cards(): array
{
    return [
        ['title' => 'Solar panels', 'text' => 'High-efficiency panels that turn Ghana’s abundant sunlight into useful power.', 'image' => 'images/solar-panels.jpg', 'url' => 'solar-panels.php'],
        ['title' => 'Hybrid inverters', 'text' => 'Reliable control between solar generation, batteries, grid power and your appliances.', 'image' => 'images/inverters.jpg', 'url' => 'inverters.php'],
        ['title' => 'Lithium batteries', 'text' => 'Long-lasting energy storage for evening use and dependable backup power.', 'image' => 'images/batteries.jpg', 'url' => 'lithium-batteries.php'],
        ['title' => 'Installation services', 'text' => 'Site assessment, system sizing and professional installation for your property.', 'image' => 'images/installation.jpg', 'url' => 'installation.php'],
    ];
}

/** Published solar packages from admin settings, with hard-coded fallbacks. */
function solar_packages(): array
{
    $packages = setting_packages();
    return $packages !== [] ? $packages : [
        ['name' => 'Bronze', 'price' => 'GHC 39,000', 'items' => ['5 × 620W solar panels', '1 × 24V 100Ah lithium battery', '1 × 4.2kW hybrid inverter']],
        ['name' => 'Silver', 'price' => 'GHC 55,400', 'items' => ['6 × 620W solar panels', '1 × 5.12kWh lithium battery', '1 × 6kW hybrid inverter']],
        ['name' => 'Gold', 'price' => 'GHC 82,000', 'items' => ['10 × 620W solar panels', '1 × 10.24kWh lithium battery', '1 × 6kW hybrid inverter']],
    ];
}
