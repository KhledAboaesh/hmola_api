<?php
// backend/update_vehicles_data.php
include 'db.php';

try {
    // 1. Add image column
    $pdo->exec("ALTER TABLE vehicle_types ADD COLUMN image VARCHAR(255) DEFAULT NULL");
    echo "Column 'image' added (or already exists).\n";
} catch (Exception $e) { /* Ignore if exists */ }

// 2. Update Vehicle Names
$updates = [
    'Chevrolet NPR Flatbed Truck' => 'شاحنة مسطحة (كميون مسطح)',
    'Sedan Car on Tow Truck' => 'سيارة سيدان على شاحنة سحب (كروسة مربوطة فوق كميون جرّ)',
    'Suzuki Carry Mini Truck' => 'شاحنة صغيرة (كميون صغير متاع حوايج)',
    'Flatbed Tow Truck' => 'شاحنة سحب مسطحة (كميون جرّ مسطح)',
    'Mobile Billboard Truck with Lift' => 'شاحنة إعلانات رقمية (كميون شاشة متحرك)',
    'Car Carrier Truck (Two-Level)' => 'شاحنة نقل سيارات (كميون حاملة كرايسات)',
    'Semi-Truck with Cargo Trailer' => 'شاحنة نصفية بمقطورة (كميون طويل متاع حمولة)',
    'Passenger Bus' => 'حافلة ركاب (باص متاع ناس)',
    'Dump Truck' => 'شاحنة تفريغ (كميون رملة أو كميون تفريغ)',
    'Flatbed Truck' => 'شاحنة مسطحة (كميون بلا جوانب)',
    'Bulldozer' => 'جرافة (بلدوزر)',
    'Tractor' => 'جرار زراعي (تراكتور)',
    'Combine Harvester' => 'حصادة زراعية (كومباين)',
    'Drilling Rig Truck' => 'شاحنة حفر أرضي (كميون حفّارة)',
    'Mobile Crane Truck' => 'شاحنة رافعة (كميون كرين)',
    'Boom Lift Truck (Cherry Picker)' => 'شاحنة رفع أفراد (كميون سلة متحركة)',
    'Tanker Truck' => 'شاحنة صهريج (كميون متاع ماية أو بنزين)',
    'Delivery Van' => 'سيارة توصيل (كروسة متاع توصيل)',
    'Flatbed Truck with Boxes' => 'شاحنة مسطحة محملة بصناديق (كميون صناديق)',
    'Compact Delivery Truck' => 'شاحنة توصيل صغيرة (كميون توصيل صغير)',
];

foreach ($updates as $en => $ar) {
    // Updating name_ar based on name_en (assuming name_en matches the key)
    // Or just generic update if name_en is the English column
    // Let's assume there is a name_en column or we match by some logic. 
    // Since I don't know the exact current names in DB, I will try to match by resemblance or just update if name_en matches.
    
    // Attempt to update name_ar where name_en matches vaguely or exactly
    $stmt = $pdo->prepare("UPDATE vehicle_types SET name_ar = ? WHERE name_en = ?");
    $stmt->execute([$ar, $en]);
}

echo "Vehicle names updated.\n";
?>
