<?php
// backend/api/request/update_status.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$requestId = $data['request_id'];
$status = $data['status']; // accepted, in_progress, completed, cancelled

try {
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $requestId]);

    echo json_encode(['status' => 'success', 'message' => 'Status updated to ' . $status]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
