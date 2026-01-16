<?php
// backend/api/request/nearby.php
include '../../cors.php';
include '../../db.php';

$driverLat = $_GET['lat'] ?? null;
$driverLng = $_GET['lng'] ?? null;
$vehicleTypeId = $_GET['vehicle_type_id'] ?? null;
$radiusKm = $_GET['radius'] ?? 50; // Default 50km

if (!$driverLat || !$driverLng) {
    echo json_encode(['status' => 'error', 'message' => 'Missing driver location']);
    exit;
}

try {
    // Haversine Formula Implementation in SQL
    $sql = "
        SELECT 
            r.*, 
            vt.name_ar as vehicle_name_ar,
            vt.name_en as vehicle_name_en,
            vt.image as vehicle_image,
            lo.name_ar as labor_name_ar,
            lo.name_en as labor_name_en,
            c.name as client_name,
            c.rating as client_rating,
            ( 6371 * acos( cos( radians(?) ) * cos( radians( pickup_lat ) ) * cos( radians( pickup_lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( pickup_lat ) ) ) ) AS distance
        FROM requests r
        JOIN users c ON r.client_id = c.id
        LEFT JOIN vehicle_types vt ON r.vehicle_type_id = vt.id
        LEFT JOIN labor_options lo ON r.labor_option_id = lo.id
        WHERE r.status = 'open'
    ";

    $params = [$driverLat, $driverLng, $driverLat];

    if ($vehicleTypeId) {
        // To check if the driver is verified and can handle this vehicle type
        // This assumes the driver's vehicle_type_id is passed as $vehicleTypeId
        // and we are looking for requests that match this driver's vehicle type
        // or requests that don't specify a vehicle type (NULL).
        $sql .= " AND (r.vehicle_type_id = ? OR r.vehicle_type_id IS NULL)";
        $params[] = $vehicleTypeId;
    }

    $sql .= " HAVING distance < ? ORDER BY distance ASC";
    $params[] = $radiusKm;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'count' => count($requests),
        'data' => $requests
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
