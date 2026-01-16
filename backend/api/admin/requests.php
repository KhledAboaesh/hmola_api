<?php
// backend/api/admin/requests.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

try {
    // Join via accepted offer to find driver name, since requests table lacks driver_id col in schema
    $query = "SELECT r.*, u.name as client_name, d.name as driver_name, vt.name_ar as vehicle_name_ar 
              FROM requests r 
              JOIN users u ON r.client_id = u.id 
              LEFT JOIN offers o ON r.id = o.request_id AND o.status = 'accepted'
              LEFT JOIN users d ON o.driver_id = d.id 
              JOIN vehicle_types vt ON r.vehicle_type_id = vt.id 
              ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $requests]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
