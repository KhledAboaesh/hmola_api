<?php
// backend/api/auth/login.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['phone'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing phone or password']);
    exit;
}

$phone = $data['phone'];
$password = $data['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    // Generate a simple token (In production, use JWT)
    // For MVP, we will just return user data.
    
    unset($user['password_hash']); // Don't send hash back

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'data' => $user
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone or password']);
}
?>
