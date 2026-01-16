<?php
// backend/api/user/upload_profile_photo.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$userId = $_POST['user_id'] ?? null;

if (!$userId || !isset($_FILES['photo'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id or photo']);
    exit;
}

try {
    $target_dir = "../../uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
    $new_filename = "user_" . $userId . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $photo_url = "uploads/profiles/" . $new_filename;
        
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$photo_url, $userId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile photo uploaded successfully',
            'url' => $photo_url
        ]);
    } else {
        throw new Exception("Failed to move uploaded file");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
