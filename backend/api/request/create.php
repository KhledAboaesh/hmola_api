<?php
// backend/api/request/create.php
include '../../cors.php';
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['client_id'], $data['pickup_lat'], $data['pickup_lng'], $data['dropoff_lat'], $data['dropoff_lng'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required location data or client ID']);
    exit;
}

$clientId = $data['client_id'];
$vehicleTypeId = $data['vehicle_type_id'] ?? null;
$laborOptionId = $data['labor_option_id'] ?? null;
$pickupLat = $data['pickup_lat'];
$pickupLng = $data['pickup_lng'];
$dropoffLat = $data['dropoff_lat'];
$dropoffLng = $data['dropoff_lng'];
$pickupAddress = $data['pickup_address'] ?? '';
$dropoffAddress = $data['dropoff_address'] ?? '';
$description = $data['description'] ?? '';

try {
    // Check for active requests
    $stmt = $pdo->prepare("SELECT id FROM requests WHERE client_id = ? AND status IN ('open', 'accepted', 'in_progress')");
    $stmt->execute([$clientId]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'لديك طلب نشط بالفعل. يرجى المتابعة معه أولاً.']);
        exit;
    }

    // Optional: Check if client has active subscription if free_mode is off (Skipped for now per plan)

    $stmt = $pdo->prepare("
        INSERT INTO requests 
        (client_id, vehicle_type_id, labor_option_id, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng, pickup_address, dropoff_address, description, status) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
    ");

    $stmt->execute([
        $clientId, $vehicleTypeId, $laborOptionId, 
        $pickupLat, $pickupLng, $dropoffLat, $dropoffLng, 
        $pickupAddress, $dropoffAddress, $description
    ]);

    $requestId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Request created successfully',
        'data' => ['request_id' => $requestId]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create request: ' . $e->getMessage()]);
}
?>
