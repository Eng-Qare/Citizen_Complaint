<?php
// tools/create_admin.php
// Usage (CLI): php tools/create_admin.php "Full Name" email@example.com password
if (PHP_SAPI !== 'cli') {
    echo "This script may only be run from the command line.\n";
    exit(1);
}
if ($argc < 4) {
    echo "Usage: php tools/create_admin.php \"Full Name\" email@example.com password\n";
    exit(1);
}
$name = $argv[1]; $email = $argv[2]; $password = $argv[3];
require_once __DIR__ . '/../config/database.php';

$hash = password_hash($password, PASSWORD_DEFAULT);
$role_id = 1; // Admin by schema
$stmt = $conn->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
$phone = '';
$stmt->bind_param('issss', $role_id, $name, $email, $phone, $hash);
try {
    $stmt->execute();
    $id = $stmt->insert_id;
    echo "Admin user created with user_id={$id}\n";
} catch (Exception $e) {
    echo "Error creating user: " . $e->getMessage() . "\n";
}
