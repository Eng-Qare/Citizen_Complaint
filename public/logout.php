<?php
// public/logout.php
require_once __DIR__ . '/../core/session.php';

// Destroy session and redirect to login
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: /Citizen_Complaint/public/login.php');
exit;