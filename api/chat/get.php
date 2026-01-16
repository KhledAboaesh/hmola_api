<?php
// backend/api/chat/get.php
include '../../cors.php';
include '../../db.php';

$requestId = $_GET['request_id'] ?? null;

if (!$requestId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing request_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.request_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$requestId]);
    $messages = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $messages
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
