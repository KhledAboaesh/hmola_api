<?php
// backend/api/driver/upload_image.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['image_base64'])) {
    echo json_encode(['status' => 'error', 'message' => 'No image provided']);
    exit;
}

try {
    // Decode base64
    $imageData = base64_decode($data['image_base64']);
    
    // Generate unique filename
    $filename = 'license_' . time() . '_' . uniqid() . '.jpg';
    $filepath = '../../uploads/licenses/' . $filename;
    
    // Save file
    file_put_contents($filepath, $imageData);
    
    echo json_encode([
        'status' => 'success', 
        'filename' => $filename,
        'url' => '/uploads/licenses/' . $filename
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
