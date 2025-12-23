<?php
// api/auth.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400); echo json_encode(['error' => 'Missing credentials']); exit;
}

$stmt = $conn->prepare('SELECT user_id, password_hash, role_id, full_name FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$row['user_id'];
        $_SESSION['role_id'] = (int)$row['role_id'];
        $_SESSION['full_name'] = $row['full_name'];
        echo json_encode(['success' => true, 'user' => ['user_id' => (int)$row['user_id'], 'full_name' => $row['full_name'], 'role_id' => (int)$row['role_id']]]);
        exit;
    }
}

http_response_code(401); echo json_encode(['error' => 'Invalid credentials']); exit;
