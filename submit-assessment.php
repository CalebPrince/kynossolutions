<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    json_response(['ok' => false, 'message' => 'Invalid request.'], 400);
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($data['csrf_token'] ?? ''))) {
    json_response(['ok' => false, 'message' => 'Session expired. Refresh and try again.'], 419);
}
if (!empty($data['website'])) {
    json_response(['ok' => true]);
}

$text = static fn(string $key, int $max = 255): string => mb_substr(trim((string) ($data[$key] ?? '')), 0, $max);
$name = $text('name', 80);
$phone = preg_replace('/[^0-9+]/', '', $text('phone', 24));
$location = $text('location', 100);
$email = $text('email', 120);

if (mb_strlen($name) < 2 || mb_strlen($location) < 2 || !preg_match('/^(?:\+?233|0)\d{9}$/', $phone)) {
    json_response(['ok' => false, 'message' => 'Please check the required contact details.'], 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'message' => 'Please enter a valid email address.'], 422);
}

$number = static function (string $key, float $min, float $max) use ($data): ?float {
    $value = $data[$key] ?? null;
    if ($value === null || !is_numeric($value)) return null;
    $value = (float) $value;
    return ($value >= $min && $value <= $max) ? $value : null;
};
$appliances = is_array($data['appliances'] ?? null) ? array_slice($data['appliances'], 0, 100) : [];

try {
    $statement = db()->prepare(
        'INSERT INTO customer_enquiries
        (name, phone, email, location, property_type, usage_mode, notes, running_load_w,
         daily_energy_kwh, inverter_kva, battery_kwh, panel_count, recommended_package,
         appliances_json, ip_hash, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $ipHash = request_ip_hash();
    $statement->execute([
        $name, $phone, $email ?: null, $location, $text('property_type', 40),
        $text('usage_mode', 40), $text('notes', 500), $number('running_load_w', 0, 1000000),
        $number('daily_energy_kwh', 0, 10000), $number('inverter_kva', 0, 1000),
        $number('battery_kwh', 0, 10000), $number('panel_count', 0, 10000),
        $text('recommended_package', 80), json_encode($appliances, JSON_UNESCAPED_SLASHES),
        $ipHash, mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
    json_response(['ok' => true, 'id' => (int) db()->lastInsertId()], 201);
} catch (Throwable $error) {
    error_log('KYNOS assessment save failed: ' . $error->getMessage());
    json_response(['ok' => false, 'message' => 'We could not save the assessment right now.'], 500);
}
