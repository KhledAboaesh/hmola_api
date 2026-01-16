<?php
// backend/api/admin/login.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['phone'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات الدخول ناقصة']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? AND role = 'admin'");
    $stmt->execute([$data['phone']]);
    $user = $stmt->fetch();

    if ($user && password_verify($data['password'], $user['password_hash'])) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'token' => base64_encode($user['phone'] . ':' . time())
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'بيانات الدخول غير صحيحة أو لست مسؤولاً.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
