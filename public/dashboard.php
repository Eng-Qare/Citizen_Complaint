<?php
// public/dashboard.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
$role_id = $user['role_id'];

// Gather counts
$counts = ['users' => 0, 'complaints' => 0];
$q = $conn->prepare('SELECT COUNT(*) AS c FROM complaints');
$q->execute(); $counts['complaints'] = (int)$q->get_result()->fetch_assoc()['c'];

if ($role_id == 1) {
    $q2 = $conn->prepare('SELECT COUNT(*) AS c FROM users'); $q2->execute(); $counts['users'] = (int)$q2->get_result()->fetch_assoc()['c'];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

echo "<h2>Welcome, " . htmlspecialchars($user['full_name']) . "</h2>";
if ($role_id == 1) {
    echo "<p>Role: Admin</p>";
    echo "<p>Total users: " . (int)$counts['users'] . "</p>";
    echo "<p>Total complaints: " . (int)$counts['complaints'] . "</p>";
} elseif ($role_id == 2) {
    echo "<p>Role: Dept Head / Staff</p>";
    echo "<p>Total complaints: " . (int)$counts['complaints'] . "</p>";
} else {
    echo "<p>Role: Citizen</p>";
    // count own complaints via citizens table
    $s = $conn->prepare('SELECT c.complaint_id FROM complaints c JOIN citizens ci ON c.citizen_id = ci.citizen_id WHERE ci.user_id = ?');
    $s->bind_param('i', $user['user_id']); $s->execute(); $own_count = $s->get_result()->num_rows;
    echo "<p>Your complaints: " . (int)$own_count . "</p>";
}

include __DIR__ . '/../includes/footer.php';
