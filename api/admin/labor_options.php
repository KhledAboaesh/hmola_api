<?php
// backend/api/admin/labor_options.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM labor_options ORDER BY id");
        $options = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $options]);
        
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO labor_options (name_ar, name_en, is_active) VALUES (?, ?, ?)");
        $stmt->execute([$data['name_ar'], $data['name_en'], $data['is_active']]);
        echo json_encode(['status' => 'success', 'message' => 'تم الإضافة بنجاح']);
        
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE labor_options SET name_ar=?, name_en=?, is_active=? WHERE id=?");
        $stmt->execute([$data['name_ar'], $data['name_en'], $data['is_active'], $data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'تم التحديث بنجاح']);
        
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if (!$id) throw new Exception("ID required");
        $stmt = $pdo->prepare("DELETE FROM labor_options WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'تم الحذف بنجاح']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
