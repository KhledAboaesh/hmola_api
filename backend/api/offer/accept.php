<?php
// backend/api/offer/accept.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['offer_id'], $data['client_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات ناقصة (رقم العرض أو العميل)']);
    exit;
}

$offerId = $data['offer_id'];
$clientId = $data['client_id'];

try {
    $pdo->beginTransaction();

    // 1. Verify Offer and Ownership
    $stmt = $pdo->prepare("
        SELECT o.*, r.client_id, r.status as request_status 
        FROM offers o 
        JOIN requests r ON o.request_id = r.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();

    if (!$offer) {
        throw new Exception("العرض غير موجود");
    }

    if ($offer['client_id'] != $clientId) {
        throw new Exception("غير مصرح لك بقبول هذا العرض");
    }

    if ($offer['request_status'] !== 'open') {
        throw new Exception("هذا الطلب تم التعامل معه مسبقاً");
    }

    // 2. Update Request Status and link Driver
    $driverId = $offer['driver_id'];
    $stmt = $pdo->prepare("UPDATE requests SET status = 'accepted', driver_id = ? WHERE id = ?");
    $stmt->execute([$driverId, $offer['request_id']]);

    // 3. Update Offer Status to Accepted
    $stmt = $pdo->prepare("UPDATE offers SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$offerId]);

    // 4. Reject other offers
    $stmt = $pdo->prepare("UPDATE offers SET status = 'rejected' WHERE request_id = ? AND id != ?");
    $stmt->execute([$offer['request_id'], $offerId]);

    // 5. Calculate and Deduct Commission
    $stmt = $pdo->prepare("SELECT value FROM app_settings WHERE `key` = 'commission_percentage'");
    $stmt->execute();
    $commPct = $stmt->fetchColumn() ?: 10;
    $commission = ($offer['price'] * $commPct) / 100;

    // Ensure driver has a wallet record
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_wallets (user_id, balance) VALUES (?, 0.00)");
    $stmt->execute([$driverId]);

    // Deduct from driver's wallet
    $stmt = $pdo->prepare("UPDATE user_wallets SET balance = balance - ? WHERE user_id = ?");
    $stmt->execute([$commission, $driverId]);

    // Log transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status) VALUES (?, ?, 'ride_commission', 'completed')");
    $stmt->execute([$driverId, -$commission]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'تم قبول العرض بنجاح. بدأت الرحلة.'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
