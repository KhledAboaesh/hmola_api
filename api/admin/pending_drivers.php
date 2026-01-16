<?php
// backend/api/admin/pending_drivers.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch pending drivers
        $stmt = $pdo->query("
            SELECT u.id, u.name, u.phone, u.created_at,
                   dd.plate_number, dd.license_photo, dd.is_verified,
                   vt.name_ar as vehicle_type
            FROM users u
            JOIN driver_details dd ON u.id = dd.user_id
            LEFT JOIN vehicle_types vt ON dd.vehicle_type_id = vt.id
            WHERE u.role = 'driver' AND dd.is_verified = 0
            ORDER BY u.created_at DESC
        ");
        $drivers = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $drivers]);
        
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Handle both 'user_id' (from admin.js) and 'driver_id'
        $id = $data['user_id'] ?? $data['driver_id'] ?? null;
        $action = $data['action'] ?? 'approve'; // Default to approve if action missing
        
        if (!$id) throw new Exception("ID required");

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE driver_details SET is_verified=1 WHERE user_id=?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'تمت الموافقة على السائق']);
        } elseif ($action === 'reject') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM driver_details WHERE user_id=?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("UPDATE users SET role='client' WHERE id=?");
            $stmt->execute([$id]);
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'تم رفض السائق']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
