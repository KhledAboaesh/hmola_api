<?php
// backend/api/chat/send.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['sender_id'], $data['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

try {
    // Check if chat table exists, if not create it (Simple Migration)
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $stmt = $pdo->prepare("INSERT INTO messages (request_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$data['request_id'], $data['sender_id'], $data['message']]);

    echo json_encode(['status' => 'success', 'message' => 'Message sent']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
