<?php
// backend/api/public/initial_data.php
include '../../cors.php';
include '../../db.php';

try {
    // Fetch Settings (as key-value pair)
    $stmt = $pdo->query("SELECT `key`, `value` FROM app_settings");
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['free_mode_enabled' => '1', ...]

    // Fetch Vehicle Types
    $stmt = $pdo->query("SELECT * FROM vehicle_types WHERE is_active = 1");
    $vehicles = $stmt->fetchAll();

    // Fetch Labor Options
    $stmt = $pdo->query("SELECT * FROM labor_options WHERE is_active = 1");
    $laborOptions = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'settings' => $settingsRaw,
            'vehicle_types' => $vehicles,
            'labor_options' => $laborOptions
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
