<?php
// backend/api/request/offers.php
include '../../cors.php';
include '../../db.php';

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing request ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT o.*, d.name as driver_name, d.rating as driver_rating, 
               dd.plate_number, vt.name_en as vehicle_type_en, vt.name_ar as vehicle_type_ar,
               vt.image as vehicle_image
        FROM offers o
        JOIN users d ON o.driver_id = d.id
        LEFT JOIN driver_details dd ON d.id = dd.user_id
        LEFT JOIN vehicle_types vt ON dd.vehicle_type_id = vt.id
        WHERE o.request_id = ?
        ORDER BY o.price ASC
    ");
    $stmt->execute([$requestId]);
    $offers = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $offers
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
