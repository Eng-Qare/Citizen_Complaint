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

if ($error) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
?>
<h2>Add User</h2>
<form method="post">
    <label>Full name<br><input name="full_name" required></label><br>
    <label>Email<br><input type="email" name="email" required></label><br>
    <label>Phone<br><input name="phone"></label><br>
    <label>Password<br><input type="password" name="password" required></label><br>
    <label>Role<br>
        <select name="role_id">
            <option value="1">Admin</option>
            <option value="2">Dept Head / Staff</option>
            <option value="3">Officer</option>
            <option value="4">Citizen</option>
        </select>
    </label><br>
    <button type="submit">Create</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>