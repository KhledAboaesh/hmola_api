<?php
// backend/api/request/active_request.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$userId = $_GET['user_id'] ?? null;
$role = $_GET['role'] ?? 'client';

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

try {
    $sql = "SELECT id, status FROM requests WHERE ";
    if ($role === 'driver') {
        $sql .= "driver_id = ?";
    } else {
        $sql .= "client_id = ?";
    }
    $sql .= " AND status IN ('accepted', 'in_progress') ORDER BY created_at DESC LIMIT 1";

    // For clients, we also check 'open' status (they might be waiting for offers)
    if ($role === 'client') {
        $sql = "SELECT id, status FROM requests WHERE client_id = ? AND status IN ('open', 'accepted', 'in_progress') ORDER BY created_at DESC LIMIT 1";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $request = $stmt->fetch();

    if ($request) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'request_id' => $request['id'],
                'status' => $request['status']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => null
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
