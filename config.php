<?php
// backend/config.php
// Central configuration for the whole backend

// Database Settings
define('DB_HOST', 'localhost:3360');
define('DB_NAME', 'hmola_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URL Settings
// Use this to change the base URL for the backend (e.g. for uploads or admin panel)
define('BASE_URL', 'http://102.221.58.202:4040/backend');
define('API_URL', BASE_URL . '/api');

// File Paths
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('VEHICLE_UPLOADS', 'uploads/vehicles/');

?>
