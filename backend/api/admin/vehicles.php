<?php
// backend/api/admin/vehicles.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM vehicle_types ORDER BY id");
        $vehicles = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $vehicles]);
        
    } elseif ($method === 'POST') {
        // Support for multipart/form-data (for image uploads)
        $id = $_POST['id'] ?? null;
        $name_ar = $_POST['name_ar'] ?? '';
        $name_en = $_POST['name_en'] ?? '';
        $is_active = $_POST['is_active'] ?? 1;
        $imagePath = null;

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/vehicles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = "vehicle_" . time() . "_" . rand(1000, 9999) . "." . $fileExt;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/vehicles/' . $fileName;
            }
        }

        if ($id) {
            // UPDATE
            if ($imagePath) {
                $stmt = $pdo->prepare("UPDATE vehicle_types SET name_ar=?, name_en=?, is_active=?, image=? WHERE id=?");
                $stmt->execute([$name_ar, $name_en, $is_active, $imagePath, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE vehicle_types SET name_ar=?, name_en=?, is_active=? WHERE id=?");
                $stmt->execute([$name_ar, $name_en, $is_active, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'تم التحديث بنجاح']);
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO vehicle_types (name_ar, name_en, is_active, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name_ar, $name_en, $is_active, $imagePath]);
            echo json_encode(['status' => 'success', 'message' => 'تم الإضافة بنجاح']);
        }
        
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if (!$id) throw new Exception("ID required");
        $stmt = $pdo->prepare("DELETE FROM vehicle_types WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'تم الحذف بنجاح']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
