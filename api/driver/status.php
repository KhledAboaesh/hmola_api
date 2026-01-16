<?php
// backend/api/driver/status.php
include '../../cors.php';
include '../../db.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

try {
    // Check if driver exists in driver_details
    $stmt = $pdo->prepare("SELECT dd.*, vt.name_ar as vehicle_type 
                           FROM driver_details dd 
                           LEFT JOIN vehicle_types vt ON dd.vehicle_type_id = vt.id 
                           WHERE dd.user_id = ?");
    $stmt->execute([$userId]);
    $driver = $stmt->fetch();

    if (!$driver) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'verification_status' => 'unregistered',
                'description' => 'لم يتم إرسال بيانات التسجيل بعد'
            ]
        ]);
        exit;
    }

    // Map status from is_verified
    // is_verified: 0 = pending, 1 = approved, 2 = rejected (or similar logic)
    $statusText = 'pending';
    if ($driver['is_verified'] == 1) {
        $statusText = 'verified';
    } elseif ($driver['is_verified'] == 2) {
        $statusText = 'rejected';
    } else {
        $statusText = 'pending';
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'verification_status' => $statusText,
            'driver_data' => $driver
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
