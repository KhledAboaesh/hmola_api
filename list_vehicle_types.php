<?php
// list_vehicle_types.php
include 'db.php';

$stmt = $pdo->query("SELECT id, name_ar, image FROM vehicle_types ORDER BY id");
$vehicles = $stmt->fetchAll();

echo "=== أنواع المركبات في قاعدة البيانات ===\n\n";
foreach ($vehicles as $v) {
    echo "ID: " . $v['id'] . "\n";
    echo "الاسم: " . $v['name_ar'] . "\n";
    echo "الصورة الحالية: " . ($v['image'] ?? 'لا توجد') . "\n";
    echo str_repeat('-', 50) . "\n";
}
echo "\nالإجمالي: " . count($vehicles) . " نوع مركبة\n";
?>
