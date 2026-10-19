<?php
declare(strict_types=1);

/**
 * Allowlisted, plain-text public site settings.
 * Only keys present in SETTING_RULES can be read or written; everything else
 * is rejected server-side. Values are plain text or validated URLs; packages
 * are a validated structured record. No arbitrary HTML or uploads.
 */

const SETTING_RULES = [
    'company_name' => ['group' => 'contact', 'label' => 'Company name', 'type' => 'text', 'max' => 60],
    'phone' => ['group' => 'contact', 'label' => 'Phone (display)', 'type' => 'phone', 'max' => 24],
    'whatsapp' => ['group' => 'contact', 'label' => 'WhatsApp number (international)', 'type' => 'whatsapp', 'max' => 24],
    'email' => ['group' => 'contact', 'label' => 'Email address', 'type' => 'email', 'max' => 120],
    'address' => ['group' => 'contact', 'label' => 'Address / service area', 'type' => 'text', 'max' => 160],
    'social_facebook' => ['group' => 'social', 'label' => 'Facebook URL', 'type' => 'url', 'max' => 200],
    'social_instagram' => ['group' => 'social', 'label' => 'Instagram URL', 'type' => 'url', 'max' => 200],
    'social_youtube' => ['group' => 'social', 'label' => 'YouTube URL', 'type' => 'url', 'max' => 200],
    'social_x' => ['group' => 'social', 'label' => 'X (Twitter) URL', 'type' => 'url', 'max' => 200],
    'social_linkedin' => ['group' => 'social', 'label' => 'LinkedIn URL', 'type' => 'url', 'max' => 200],
    'social_tiktok' => ['group' => 'social', 'label' => 'TikTok URL', 'type' => 'url', 'max' => 200],
    'page_home_hero_intro' => ['group' => 'content', 'label' => 'Home: hero introduction', 'type' => 'text', 'max' => 240],
    'page_home_solutions_intro' => ['group' => 'content', 'label' => 'Home: solutions introduction', 'type' => 'text', 'max' => 200],
    'page_home_why_intro' => ['group' => 'content', 'label' => 'Home: why KYNOS introduction', 'type' => 'text', 'max' => 200],
    'page_home_projects_intro' => ['group' => 'content', 'label' => 'Home: projects introduction', 'type' => 'text', 'max' => 200],
    'page_home_cta_heading' => ['group' => 'content', 'label' => 'Home: closing call heading', 'type' => 'text', 'max' => 80],
    'page_home_cta_text' => ['group' => 'content', 'label' => 'Home: closing call text', 'type' => 'text', 'max' => 200],
    'page_about_intro' => ['group' => 'content', 'label' => 'About page introduction', 'type' => 'text', 'max' => 240],
    'page_contact_intro' => ['group' => 'content', 'label' => 'Contact page introduction', 'type' => 'text', 'max' => 240],
    'footer_blurb' => ['group' => 'content', 'label' => 'Footer summary line', 'type' => 'text', 'max' => 160],
    'packages_json' => ['group' => 'packages', 'label' => 'Solar packages', 'type' => 'packages', 'max' => 4000],
];

const SETTING_DEFAULTS = [
    'company_name' => 'KYNOS Solar Systems',
    'phone' => '053 040 9118',
    'whatsapp' => '233530409118',
    'email' => 'info@kynossolutions.com',
    'address' => 'Accra & Tema, Ghana',
    'social_facebook' => '',
    'social_instagram' => '',
    'social_youtube' => '',
    'social_x' => '',
    'social_linkedin' => '',
    'social_tiktok' => '',
    'page_home_hero_intro' => 'Say goodbye to sky-high electricity bills and hello to clean, reliable energy with KYNOS SOLAR SYSTEMS. We provide premium solar solutions for homes, offices and businesses.',
    'page_home_solutions_intro' => 'Everything you need to go solar: high quality products, professional installation and ongoing support.',
    'page_home_why_intro' => 'We are committed to providing reliable, affordable and long-lasting solar solutions for homes, offices and businesses.',
    'page_home_projects_intro' => 'Take a look at some of our recent solar installations for homes, offices and businesses.',
    'page_home_cta_heading' => 'Ready to Go Solar?',
    'page_home_cta_text' => 'Contact us today for a free assessment and get the right solar solution for your home, office or business.',
    'page_about_intro' => 'KYNOS Solar Systems helps homes, offices and businesses move toward reliable, affordable and sustainable energy.',
    'page_contact_intro' => 'Tell us what you want to power, where the property is and when you would like to get started.',
    'footer_blurb' => 'Reliable solar solutions for homes, offices and businesses in Ghana.',
    'packages_json' => '{"1":{"name":"Bronze","price":"GHC 39,000","items":["5 × 620W solar panels","1 × 24V 100Ah lithium battery","1 × 4.2kW hybrid inverter"]},"2":{"name":"Silver","price":"GHC 55,400","items":["6 × 620W solar panels","1 × 5.12kWh lithium battery","1 × 6kW hybrid inverter"]},"3":{"name":"Gold","price":"GHC 82,000","items":["10 × 620W solar panels","1 × 10.24kWh lithium battery","1 × 6kW hybrid inverter"]}}',
];

function setting_default(string $key): string
{
    return SETTING_DEFAULTS[$key] ?? '';
}

/** All stored settings merged over defaults. Falls back to defaults on any database failure. */
function settings_all(): array
{
    if (isset($GLOBALS['kynos_settings_cache']) && is_array($GLOBALS['kynos_settings_cache'])) {
        return $GLOBALS['kynos_settings_cache'];
    }
    $cache = SETTING_DEFAULTS;
    try {
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $key = (string) $row['setting_key'];
            if (array_key_exists($key, SETTING_RULES)) {
                $cache[$key] = (string) $row['setting_value'];
            }
        }
    } catch (Throwable $error) {
        error_log('KYNOS settings load failed: ' . $error->getMessage());
    }
    $GLOBALS['kynos_settings_cache'] = $cache;
    return $cache;
}

function setting(string $key, ?string $default = null): string
{
    $all = settings_all();
    if (array_key_exists($key, $all)) {
        return $all[$key];
    }
    return $default ?? setting_default($key);
}

function phone_to_intl(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone) ?? '';
    if (str_starts_with($digits, '0')) {
        return '233' . substr($digits, 1);
    }
    return $digits;
}

function setting_whatsapp_link(string $message = ''): string
{
    $url = 'https://wa.me/' . phone_to_intl(setting('whatsapp'));
    return $message === '' ? $url : $url . '?text=' . rawurlencode($message);
}

/**
 * @return array<int, array{name: string, price: string, items: string[]}>
 */
function setting_packages(): array
{
    $decoded = json_decode(setting('packages_json'), true);
    if (!is_array($decoded)) {
        return [];
    }
    $packages = [];
    foreach ([1, 2, 3] as $slot) {
        $package = $decoded[$slot] ?? $decoded[(string) $slot] ?? null;
        if (!is_array($package)) {
            return [];
        }
        $packages[] = [
            'name' => (string) ($package['name'] ?? ''),
            'price' => (string) ($package['price'] ?? ''),
            'items' => array_values(array_map('strval', (array) ($package['items'] ?? []))),
        ];
    }
    return $packages;
}

function setting_packages_from(): string
{
    $cheapest = null;
    foreach (setting_packages() as $package) {
        preg_match('/[\d,]+/', str_replace(' ', '', $package['price']), $match);
        $value = isset($match[0]) ? (int) str_replace(',', '', $match[0]) : null;
        if ($value !== null && ($cheapest === null || $value < $cheapest[0])) {
            $cheapest = [$value, $package['price']];
        }
    }
    return $cheapest !== null ? $cheapest[1] : 'GHC 39,000';
}

/**
 * @return array{ok: bool, value: string, error: string}
 */
function validate_setting(string $key, string $value): array
{
    $rule = SETTING_RULES[$key] ?? null;
    if ($rule === null) {
        return ['ok' => false, 'value' => '', 'error' => 'That setting cannot be edited.'];
    }
    $value = trim($value);
    // Plain text only: strip control characters; angle brackets are never accepted.
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';
    if (str_contains($value, '<') || str_contains($value, '>')) {
        return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be plain text without HTML.'];
    }
    if (mb_strlen($value) > (int) $rule['max']) {
        return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be at most ' . $rule['max'] . ' characters.'];
    }

    switch ($rule['type']) {
        case 'phone':
            $compact = preg_replace('/\s+/', '', $value) ?? '';
            if ($value === '' || !preg_match('/^(?:\+?233|0)\d{9}$/', $compact)) {
                return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be a valid Ghana phone number.'];
            }
            break;
        case 'whatsapp':
            $digits = preg_replace('/\D/', '', $value) ?? '';
            if (!preg_match('/^(?:233|0)\d{9}$/', $digits)) {
                return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be a valid Ghana WhatsApp number.'];
            }
            $value = str_starts_with($digits, '0') ? '233' . substr($digits, 1) : $digits;
            break;
        case 'email':
            if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be a valid email address.'];
            }
            break;
        case 'url':
            if ($value === '') {
                break;
            }
            if (!filter_var($value, FILTER_VALIDATE_URL) || !str_starts_with($value, 'https://')) {
                return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' must be a full https:// URL.'];
            }
            break;
        case 'packages':
            $normalized = validate_packages_payload($value);
            if ($normalized === null) {
                return ['ok' => false, 'value' => '', 'error' => 'Packages must include three packages, each with a name, price and one item per line.'];
            }
            $value = $normalized;
            break;
        case 'text':
        default:
            if ($value === '' && $rule['group'] === 'contact' && in_array($key, ['company_name', 'phone', 'email'], true)) {
                return ['ok' => false, 'value' => '', 'error' => $rule['label'] . ' is required.'];
            }
            break;
    }
    return ['ok' => true, 'value' => $value, 'error' => ''];
}

/**
 * Build the canonical packages JSON from posted form fields
 * (name/price per package plus one item per line). Returns null when invalid.
 */
function validate_packages_payload(string $value): ?string
{
    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return null;
    }
    $out = [];
    foreach ([1, 2, 3] as $slot) {
        $package = $decoded[$slot] ?? $decoded[(string) $slot] ?? null;
        if (!is_array($package)) {
            return null;
        }
        $name = trim((string) ($package['name'] ?? ''));
        $price = trim((string) ($package['price'] ?? ''));
        $items = array_values(array_filter(array_map(
            static fn($item) => trim((string) $item),
            is_array($package['items'] ?? null) ? $package['items'] : []
        ), static fn(string $item) => $item !== ''));
        if ($name === '' || mb_strlen($name) > 40) {
            return null;
        }
        if ($price === '' || mb_strlen($price) > 40 || !preg_match('/GHC\s*[\d,]+/', $price)) {
            return null;
        }
        if (count($items) < 1 || count($items) > 6) {
            return null;
        }
        foreach ($items as $item) {
            if (mb_strlen($item) > 80 || str_contains($item, '<') || str_contains($item, '>')) {
                return null;
            }
        }
        $out[(string) $slot] = ['name' => $name, 'price' => $price, 'items' => $items];
    }
    return json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * Validate and persist a set of allowlisted settings in one transaction.
 * Each changed key is recorded in the audit trail with old and new values.
 *
 * @return array{ok: bool, error: string, changed: string[]}
 */
function settings_save(array $actor, array $input): array
{
    assert_permission($actor, 'settings.manage');

    $changes = [];
    foreach (array_keys($input) as $key) {
        $key = (string) $key;
        if (!array_key_exists($key, SETTING_RULES)) {
            return ['ok' => false, 'error' => 'That setting cannot be edited.', 'changed' => []];
        }
    }

    $validated = [];
    foreach ($input as $key => $value) {
        $result = validate_setting((string) $key, is_string($value) ? $value : '');
        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'], 'changed' => []];
        }
        $validated[(string) $key] = $result['value'];
    }

    $current = settings_all();
    foreach ($validated as $key => $value) {
        if (($current[$key] ?? setting_default($key)) !== $value) {
            $changes[$key] = ['from' => (string) ($current[$key] ?? setting_default($key)), 'to' => $value];
        }
    }
    if ($changes === []) {
        return ['ok' => true, 'error' => '', 'changed' => []];
    }

    try {
        db()->beginTransaction();
        $statement = db()->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_by, updated_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()'
        );
        foreach ($changes as $key => $change) {
            $statement->execute([$key, $change['to'], (string) $actor['email']]);
            audit_event_as($actor, 'settings.updated', 'setting', null, [
                'key' => $key,
                'from' => mb_substr($change['from'], 0, 300),
                'to' => mb_substr($change['to'], 0, 300),
            ]);
        }
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log('KYNOS settings save failed: ' . $error->getMessage());
        return ['ok' => false, 'error' => 'Settings could not be saved. Please try again.', 'changed' => []];
    }

    // Refresh the per-request cache so the confirmation shows current values.
    settings_all_reset();
    return ['ok' => true, 'error' => '', 'changed' => array_keys($changes)];
}

function settings_all_reset(): void
{
    unset($GLOBALS['kynos_settings_cache']);
}
