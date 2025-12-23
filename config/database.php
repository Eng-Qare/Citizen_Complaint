<?php
// Database configuration (use provided credentials)
$DB_HOST = '148.222.53.74';
$DB_NAME = 'u675357151_CitizeDB';
$DB_USER = 'u675357151_CitizeDB_user';
$DB_PASS = 'yK2:uyisS4|';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection error";
    exit;
}

// $conn is available to included scripts
?>
