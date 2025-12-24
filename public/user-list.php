<?php
// public/user-list.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
if ($user['role_id'] != 1) { http_response_code(403); echo 'Forbidden'; exit; }

$q = $conn->prepare('SELECT user_id, role_id, full_name, email, phone, is_active, created_at FROM users ORDER BY created_at DESC');
$q->execute(); $res = $q->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

echo '<div class="d-flex justify-content-between align-items-center"><h2>Users</h2><a class="btn btn-sm btn-success" href="user-add.php">Add User</a></div>';
echo '<div class="card mt-3 shadow-sm"><div class="card-body">';
echo '<div class="table-responsive">';
echo '<table class="table table-striped table-hover datatable">';
echo '<thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Active</th><th>Created</th></tr></thead><tbody>';
while ($r = $res->fetch_assoc()) {
    $role_label = ($r['role_id']==1)?'Admin':(($r['role_id']==2)?'Staff':'Citizen');
    echo '<tr>';
    echo '<td>' . (int)$r['user_id'] . '</td>';
    echo '<td>' . htmlspecialchars($r['full_name']) . '</td>';
    echo '<td>' . htmlspecialchars($r['email']) . '</td>';
    echo '<td>' . htmlspecialchars($r['phone']) . '</td>';
    echo '<td>' . htmlspecialchars($role_label) . '</td>';
    echo '<td>' . ((int)$r['is_active'] ? 'Yes' : 'No') . '</td>';
    echo '<td>' . htmlspecialchars($r['created_at']) . '</td>';
    echo '</tr>';
}
echo '</tbody></table></div></div></div>';

include __DIR__ . '/../includes/footer.php';