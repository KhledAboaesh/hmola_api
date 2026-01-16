<?php
// backend/api/auth/register.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name'], $data['phone'], $data['password'], $data['role'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$name = $data['name'];
$phone = $data['phone'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$role = $data['role']; // 'client' or 'driver'

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute([$phone]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number already registered']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert User
    $stmt = $pdo->prepare("INSERT INTO users (name, phone, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $password, $role]);
    $userId = $pdo->lastInsertId();

    // Create Wallet
    $stmt = $pdo->prepare("INSERT INTO user_wallets (user_id, balance) VALUES (?, 0.00)");
    $stmt->execute([$userId]);

    // If Driver, insert details (simplified for MVP, expects vehicle_type_id and plate if driver)
    if ($role === 'driver') {
        $vehicleTypeId = $data['vehicle_type_id'] ?? null;
        $plateNumber = $data['plate_number'] ?? 'PENDING';
        
        $stmt = $pdo->prepare("INSERT INTO driver_details (user_id, vehicle_type_id, plate_number) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $vehicleTypeId, $plateNumber]);
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'User registered successfully',
        'data' => [
            'id' => $userId,
            'name' => $name,
            'role' => $role
        ]
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
}
?>
