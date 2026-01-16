<?php
// test_db_connection.php
$host = 'localhost:3360';
$db   = 'hmola_db';
$user = 'root';
$pass = '';

echo "Testing connection to $host...\n";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "SUCCESS: Connected to database on port 3360.\n";
} catch (PDOException $e) {
    echo "FAILED on 3360: " . $e->getMessage() . "\n";
    
    // Try 3306
    echo "Retrying with port 3306...\n";
    $host = 'localhost:3306';
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        echo "SUCCESS: Connected to database on port 3306.\n";
    } catch (PDOException $e2) {
        echo "FAILED on 3306: " . $e2->getMessage() . "\n";
    }
}
?>
