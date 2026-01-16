<?php
// backend/add_profile_column.php
include 'db.php';

try {
    $sql = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL";
    $pdo->exec($sql);
    echo "Column profile_image added successfully.";
} catch (PDOException $e) {
    echo "Error (might already exist): " . $e->getMessage();
}
?>
