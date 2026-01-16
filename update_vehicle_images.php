<?php
// update_vehicle_images.php
include 'db.php';

// خريطة ربط أنواع المركبات بأسماء الصور
$imageMap = [
    1 => 'uploads/vehicles/truck_small.png',
    2 => 'uploads/vehicles/truck_medium.png',
    3 => 'uploads/vehicles/truck_large.png',
    4 => 'uploads/vehicles/truck_flatbed.png',
    5 => 'uploads/vehicles/truck_refrigerated.png',
    6 => 'uploads/vehicles/truck_heavy.png',
    7 => 'uploads/vehicles/van_passenger.png',
    8 => 'uploads/vehicles/van_cargo.png',
    9 => 'uploads/vehicles/van_delivery.png',
    10 => 'uploads/vehicles/van_box.png',
    11 => 'uploads/vehicles/pickup_single.png',
    12 => 'uploads/vehicles/pickup_double.png',
    13 => 'uploads/vehicles/pickup_4x4.png',
    14 => 'uploads/vehicles/bus_minibus.png',
    15 => 'uploads/vehicles/bus_transport.png',
    16 => 'uploads/vehicles/trailer_semi.png',
    17 => 'uploads/vehicles/trailer_full.png',
    18 => 'uploads/vehicles/motorcycle_delivery.png',
    // الأنواع 19 و 20 ستستخدم صور احتياطية من الموجود
    19 => 'uploads/vehicles/truck_large.png', // مشاركة صورة
    20 => 'uploads/vehicles/pickup_4x4.png', // مشاركة صورة
];

echo "بدء تحديث قاعدة البيانات...\n\n";

$updated = 0;
foreach ($imageMap as $vehicleId => $imagePath) {
    $stmt = $pdo->prepare("UPDATE vehicle_types SET image = ? WHERE id = ?");
    $stmt->execute([$imagePath, $vehicleId]);
    
    echo "✓ تم تحديث المركبة رقم $vehicleId بالصورة: $imagePath\n";
    $updated++;
}

echo "\n✅ تم تحديث $updated نوع مركبة بنجاح!\n";
?>
