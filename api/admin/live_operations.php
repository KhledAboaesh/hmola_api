<?php
// backend/api/admin/live_operations.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

try {
    // 1. Fetch Drivers with their locations
    $drivers_query = "SELECT l.driver_id, l.latitude, l.longitude, l.updated_at, u.name, u.phone 
                      FROM driver_locations l
                      JOIN users u ON l.driver_id = u.id
                      WHERE l.updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"; 
    $drivers_stmt = $pdo->prepare($drivers_query);
    $drivers_stmt->execute();
    $drivers = $drivers_stmt->fetchAll();

    // 2. Fetch Open Requests
    $requests_query = "SELECT id, pickup_lat, pickup_lng, status, created_at 
                       FROM requests 
                       WHERE status = 'open'";
    $requests_stmt = $pdo->prepare($requests_query);
    $requests_stmt->execute();
    $requests = $requests_stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => [
            "drivers" => $drivers,
            "requests" => $requests
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
