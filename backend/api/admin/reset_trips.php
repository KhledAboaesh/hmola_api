<?php
// backend/api/admin/reset_trips.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

try {
    $pdo->beginTransaction();
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Clear operational data
    $pdo->exec("DELETE FROM offers");
    $pdo->exec("DELETE FROM requests");
    $pdo->exec("DELETE FROM messages");
    $pdo->exec("DELETE FROM ratings");
    
    // Clear transaction history related to rides only
    $pdo->exec("DELETE FROM transactions WHERE type = 'ride_commission'");
    
    // Reset driver locations to start fresh
    $pdo->exec("DELETE FROM driver_locations");
    
    // Reset Auto-increment
    $pdo->exec("ALTER TABLE requests AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE offers AUTO_INCREMENT = 1");
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $pdo->commit();

    echo json_encode(["status" => "success", "message" => "تم تصفير كافة الطلبات، الرحلات، والبيانات التشغيلية بنجاح."]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
