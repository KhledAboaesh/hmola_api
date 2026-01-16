<?php
// backend/api/request/details.php
include '../../cors.php';
include '../../db.php';

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing request_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u1.name as client_name, u1.phone as client_phone,
               u2.name as driver_name, u2.phone as driver_phone,
               v.name_en as vehicle_type, v.name_ar as vehicle_type_ar,
               v.image as vehicle_image
        FROM requests r
        LEFT JOIN users u1 ON r.client_id = u1.id
        LEFT JOIN users u2 ON r.driver_id = u2.id
        LEFT JOIN vehicle_types v ON r.vehicle_type_id = v.id
        WHERE r.id = ?
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        echo json_encode(['status' => 'error', 'message' => 'Request not found']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $request
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
