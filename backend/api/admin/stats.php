<?php
// backend/api/admin/stats.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

try {
    $stats = [];

    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];

    // Total Drivers (Verified)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM driver_details WHERE is_verified = 1");
    $stats['verified_drivers'] = $stmt->fetch()['count'];

    // Total Requests (Completed)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM requests WHERE status = 'completed'");
    $stats['completed_rides'] = $stmt->fetch()['count'];

    // Data for Charts (Last 7 days requests)
    $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
              FROM requests 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
              GROUP BY DATE(created_at) 
              ORDER BY date ASC";
    $stmt = $pdo->query($query);
    $chart_data = $stmt->fetchAll();

    echo json_encode([
        "status" => "success", 
        "data" => [
            "summary" => $stats,
            "chart" => $chart_data
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
