<?php
require_once __DIR__ . '/session.php';
// auth helpers
function current_user() {
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'user_id' => $_SESSION['user_id'],
        'role_id' => $_SESSION['role_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
    ];
}

function require_role($role_id) {
    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != $role_id) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

?>
