<?php
// api/users.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/session.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $q = $conn->prepare('SELECT user_id, role_id, full_name, email, phone, is_active, created_at FROM users ORDER BY created_at DESC'); $q->execute(); $res = $q->get_result(); $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['data'=>$rows]); exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $role_id = (int)($input['role_id'] ?? 4);
    if (!$full_name || !$email || !$password) { http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('issss', $role_id, $full_name, $email, $phone, $hash); $stmt->execute();
    $new = $stmt->insert_id;
    if ($role_id == 4) { $ci = $conn->prepare('INSERT INTO citizens (user_id, created_at) VALUES (?, NOW())'); $ci->bind_param('i', $new); $ci->execute(); }
    echo json_encode(['success'=>true,'user_id'=>$new]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit;
