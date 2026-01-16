<?php
// backend/api/driver/get_earnings.php
include '../../cors.php';
include '../../db.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

try {
    // Get daily earnings for the last 7 days
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, SUM(price) as total 
        FROM requests 
        WHERE driver_id = (SELECT id FROM driver_details WHERE user_id = ?) 
        AND status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$userId]);
    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total earnings
    $stmt = $pdo->prepare("
        SELECT SUM(price) as total 
        FROM requests 
        WHERE driver_id = (SELECT id FROM driver_details WHERE user_id = ?) 
        AND status = 'completed'
    ");
    $stmt->execute([$userId]);
    $total = $stmt->fetch()['total'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'chart_data' => $daily,
            'total_earnings' => $total,
            'currency' => 'د.ل'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
