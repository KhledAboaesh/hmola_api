<?php
// fix_vehicle_images.php
include 'db.php';

//  قائمة الصور الموجودة فعلياً في المجلد
$actualImages = [
    'Gemini_Generated_Image_3vea3n3vea3n3vea.png',
    'Gemini_Generated_Image_3z12qx3z12qx3z12.png',
    'Gemini_Generated_Image_3z38rw3z38rw3z38.png',
    'Gemini_Generated_Image_40yks840yks840yk.png',
    'Gemini_Generated_Image_45ki6w45ki6w45ki.png',
    'Gemini_Generated_Image_6ki97b6ki97b6ki9.png',
    'Gemini_Generated_Image_7yaqh97yaqh97yaq.png',
    'Gemini_Generated_Image_bxsewpbxsewpbxse.png',
    'Gemini_Generated_Image_c4eh0fc4eh0fc4eh.png',
    'Gemini_Generated_Image_ccv3k5ccv3k5ccv3.png',
    'Gemini_Generated_Image_ezjgfxezjgfxezjg.png',
    'Gemini_Generated_Image_haqfl0haqfl0haqf.png',
    'Gemini_Generated_Image_kh7km7kh7km7kh7k.png',
    'Gemini_Generated_Image_s047h8s047h8s047.png',
    'Gemini_Generated_Image_s32d35s32d35s32d (1).png',
    'Gemini_Generated_Image_syq5kssyq5kssyq5.png',
    'Gemini_Generated_Image_ussveyussveyussv.png',
    'Gemini_Generated_Image_z2q52dz2q52dz2q5.png',
];

// تحديث جميع أنواع المركبات بمسارات الصور الصحيحة
$imageMap = [
    1 => 'uploads/vehicles/truck_small.png',   // شاحنة صغيرة
    2 => 'uploads/vehicles/truck_large.png',   // شاحنة ثقيلة
    3 => 'uploads/vehicles/truck_flatbed.png', // شاحنة سحب مسطحة
    4 => 'uploads/vehicles/truck_medium.png',  // ونش (شاحنة متوسطة)
    5 => 'uploads/vehicles/Gemini_Generated_Image_45ki6w45ki6w45ki.png',  // براد
    6 => 'uploads/vehicles/Gemini_Generated_Image_6ki97b6ki97b6ki9.png',  // حاوية
    7 => 'uploads/vehicles/Gemini_Generated_Image_7yaqh97yaqh97yaq.png',  // ثلاجة
];

echo "تحديث قاعدة البيانات...\n\n";

foreach ($imageMap as $vehicleId => $imagePath) {
    $stmt = $pdo->prepare("UPDATE vehicle_types SET image = ? WHERE id = ?");
    $stmt->execute([$imagePath, $vehicleId]);
    echo "✓ ID $vehicleId: $imagePath\n";
}

echo "\n✅ تم التحديث بنجاح!\n";
?>
