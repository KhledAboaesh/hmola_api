<?php
// backend/api/request/rate.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['rating'], $data['target_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update target user rating
    // Average rating logic
    $stmt = $pdo->prepare("SELECT rating, (SELECT COUNT(*) FROM requests WHERE (client_id = ? OR id IN (SELECT request_id FROM offers WHERE driver_id = ? AND status='accepted')) AND status='completed') as completed_count FROM users WHERE id = ?");
    $stmt->execute([$data['target_user_id'], $data['target_user_id'], $data['target_user_id']]);
    $user = $stmt->fetch();

    $newRating = ($user['rating'] + $data['rating']) / 2; // Simple average for MVP

    $updateStmt = $pdo->prepare("UPDATE users SET rating = ? WHERE id = ?");
    $updateStmt->execute([$newRating, $data['target_user_id']]);

    // 2. Mark request as completed if not already (logic depends on flow)
    $stmt = $pdo->prepare("UPDATE requests SET status = 'completed' WHERE id = ?");
    $stmt->execute([$data['request_id']]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
