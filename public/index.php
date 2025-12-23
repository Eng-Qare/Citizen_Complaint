<?php
// public/index.php
require_once __DIR__ . '/../core/session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: /Citizen_Complaint/public/dashboard.php');
} else {
    header('Location: /Citizen_Complaint/public/login.php');
}
exit;
