<?php
// router.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (preg_match('/\.(?:png|jpg|jpeg|gif|webp)$/', $_SERVER["REQUEST_URI"])) {
    $file = __DIR__ . $_SERVER["REQUEST_URI"];
    if (file_exists($file)) {
        $mime = mime_content_type($file);
        header("Content-Type: $mime");
        readfile($file);
        exit;
    }
}

return false; // Let the built-in server handle other files
?>
