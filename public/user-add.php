<?php
// public/user-add.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
if ($user['role_id'] != 1) { http_response_code(403); echo 'Forbidden'; exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? ''); 
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 4);

    if (!$full_name || !$email || !$password) {
        $error = 'Name, email and password are required.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('issss', $role_id, $full_name, $email, $phone, $hash);
        try {
            $stmt->execute();
            $new = $stmt->insert_id;
            if ($role_id == 4) {
                $ci = $conn->prepare('INSERT INTO citizens (user_id, created_at) VALUES (?, NOW())');
                $ci->bind_param('i', $new); $ci->execute();
            }
            header('Location: /Citizen_Complaint/public/user-list.php'); exit;
        } catch (Exception $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="card-title">Add User</h3>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input class="form-control" name="full_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role_id">
                    <option value="1">Admin</option>
                    <option value="2">Dept Head</option>
                    <option value="3">Officer</option>
                    <option value="4">Citizen</option>
                </select>
            </div>
            <div class="d-grid"><button class="btn btn-primary" type="submit">Create</button></div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>