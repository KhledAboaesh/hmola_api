<?php
// backend/api/request/history.php
include '../../cors.php';
include '../../db.php';

$userId = $_GET['user_id'] ?? null;
$role = $_GET['role'] ?? 'client';

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

try {
    if ($role == 'client') {
        $stmt = $pdo->prepare("
            SELECT r.*, v.name_ar as vehicle_name, v.image as vehicle_image, l.name_ar as labor_name,
            (SELECT price FROM offers WHERE request_id = r.id AND status = 'accepted' LIMIT 1) as final_price
            FROM requests r
            LEFT JOIN vehicle_types v ON r.vehicle_type_id = v.id
            LEFT JOIN labor_options l ON r.labor_option_id = l.id
            WHERE r.client_id = ?
            ORDER BY r.created_at DESC
        ");
    } else {
        // Driver history: requests where they provided an accepted offer
        $stmt = $pdo->prepare("
            SELECT r.*, v.name_ar as vehicle_name, v.image as vehicle_image, l.name_ar as labor_name, o.price as final_price
            FROM requests r
            JOIN offers o ON r.id = o.request_id
            LEFT JOIN vehicle_types v ON r.vehicle_type_id = v.id
            LEFT JOIN labor_options l ON r.labor_option_id = l.id
            WHERE o.driver_id = ? AND o.status = 'accepted'
            ORDER BY r.created_at DESC
        ");
    }

    $stmt->execute([$userId]);
    $history = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $history
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
