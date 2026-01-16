<?php
// backend/api/offer/submit.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['driver_id'], $data['price'])) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات العرض ناقصة']);
    exit;
}

try {
    // 1. Check if request is still open
    $stmt = $pdo->prepare("SELECT status FROM requests WHERE id = ?");
    $stmt->execute([$data['request_id']]);
    $status = $stmt->fetchColumn();

    if (!$status || $status !== 'open') {
        throw new Exception("هذا الطلب لم يعد متاحاً لاستقبال العروض");
    }

    // 2. Check if driver already submitted an offer
    $stmt = $pdo->prepare("SELECT id FROM offers WHERE request_id = ? AND driver_id = ? AND status = 'pending'");
    $stmt->execute([$data['request_id'], $data['driver_id']]);
    if ($stmt->fetch()) {
        throw new Exception("لقد قمت بتقديم عرض على هذا الطلب مسبقاً");
    }

    // 3. Insert Offer
    $stmt = $pdo->prepare("INSERT INTO offers (request_id, driver_id, price, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['request_id'],
        $data['driver_id'],
        $data['price'],
        $data['comment'] ?? null
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'تم تقديم عرضك بنجاح'
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
