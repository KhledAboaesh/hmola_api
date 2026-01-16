<?php
// backend/api/request/upload_photo.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

// Check if request_id and type (pickup/delivery) are provided
$requestId = $_POST['request_id'] ?? null;
$type = $_POST['type'] ?? 'pickup'; // pickup or delivery

if (!$requestId || !isset($_FILES['photo'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data or photo']);
    exit;
}

try {
    $target_dir = "../../uploads/trips/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
    $new_filename = "trip_" . $requestId . "_" . $type . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $photo_url = "uploads/trips/" . $new_filename;
        
        // Update requests table with photo URL
        $column = ($type === 'pickup') ? 'pickup_photo' : 'delivery_photo';
        
        $stmt = $pdo->prepare("UPDATE requests SET $column = ? WHERE id = ?");
        $stmt->execute([$photo_url, $requestId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Photo uploaded successfully',
            'url' => $photo_url
        ]);
    } else {
        throw new Exception("Failed to move uploaded file");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
