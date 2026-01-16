<?php
// backend/api/driver/register_driver_info.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'], $data['vehicle_type_id'], $data['plate_number'], $data['license_photo'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check if driver details already exist
    $stmt = $pdo->prepare("SELECT user_id FROM driver_details WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE driver_details 
            SET vehicle_type_id=?, plate_number=?, license_photo=?, is_verified=0 
            WHERE user_id=?
        ");
        $stmt->execute([
            $data['vehicle_type_id'], 
            $data['plate_number'], 
            $data['license_photo'],
            $data['user_id']
        ]);
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO driver_details (user_id, vehicle_type_id, plate_number, license_photo, is_verified) 
            VALUES (?, ?, ?, ?, 0)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['vehicle_type_id'], 
            $data['plate_number'], 
            $data['license_photo']
        ]);
    }
    
    // Update user role to 'driver'
    $stmt = $pdo->prepare("UPDATE users SET role='driver' WHERE id=?");
    $stmt->execute([$data['user_id']]);
    
    $pdo->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'تم إرسال طلبك. سيتم مراجعته من قبل الإدارة.']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
