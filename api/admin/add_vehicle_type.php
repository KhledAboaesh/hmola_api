<?php
// backend/api/admin/add_vehicle_type.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../../db.php';

$name_ar = $_POST['name_ar'] ?? null;
$name_en = $_POST['name_en'] ?? null;
$base_price = $_POST['base_price'] ?? 0;
$price_per_km = $_POST['price_per_km'] ?? 0;

if (!$name_ar || !$name_en) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../uploads/vehicles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = "vehicle_" . time() . "_" . rand(1000,9999) . "." . $file_extension;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_url = "uploads/vehicles/" . $filename;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO vehicle_types (name_ar, name_en, base_price, price_per_km, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name_ar, $name_en, $base_price, $price_per_km, $image_url]);
    
    echo json_encode(['status' => 'success', 'message' => 'Vehicle type added successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
