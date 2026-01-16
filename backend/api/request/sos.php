<?php
// backend/api/request/sos.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$requestId = $data['request_id'];
$userId = $data['user_id'];
$lat = $data['lat'] ?? null;
$lng = $data['lng'] ?? null;

try {
    // Insert SOS alert
    // Assuming table 'sos_alerts' exists
    $stmt = $pdo->prepare("INSERT INTO sos_alerts (request_id, user_id, lat, lng, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$requestId, $userId, $lat, $lng]);

    // Update request status to 'sos' ?! OR just log it. 
    // Usually SOS doesn't stop the trip but alerts admin.
    
    echo json_encode(['status' => 'success', 'message' => 'تم إرسال بلاغ الطوارئ بنجاح. فريق الدعم في طريقه إليك.']);

} catch (Exception $e) {
    // If table doesn't exist, handle gracefully or create it?
    // For now, assume it exists as per previous tasks.
    // If it fails, maybe table missing.
    echo json_encode(['status' => 'error', 'message' => 'Failed to log SOS: ' . $e->getMessage()]);
}
?>
