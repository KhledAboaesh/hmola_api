<?php
// backend/api/wallet/get_balance.php
include '../../cors.php';
include '../../db.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT balance FROM user_wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        // Create wallet if not exists
        $stmt = $pdo->prepare("INSERT INTO user_wallets (user_id, balance) VALUES (?, 0.00)");
        $stmt->execute([$userId]);
        $balance = 0.00;
    } else {
        $balance = $wallet['balance'];
    }

    echo json_encode([
        'status' => 'success',
        'balance' => $balance
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
