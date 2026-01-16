<?php
// backend/api/admin/settings.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $query = "SELECT `key`, `value` FROM app_settings";
        $stmt = $pdo->query($query);
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['key']] = $row['value'];
        }
        echo json_encode(["status" => "success", "data" => $settings]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        echo json_encode(["status" => "error", "message" => "بيانات غير صالحة"]);
        exit;
    }
    try {
        $pdo->beginTransaction();
        $query = "INSERT INTO app_settings (`key`, `value`) VALUES (:key, :value) 
                  ON DUPLICATE KEY UPDATE `value` = :value_update";
        $stmt = $pdo->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value, ':value_update' => $value]);
        }
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "تم حفظ الإعدادات بنجاح"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
