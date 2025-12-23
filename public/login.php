<?php
// public/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/session.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
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
                header('Location: /Citizen_Complaint/public/dashboard.php');
                exit;
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<h2>Login</h2>
<?php if ($error): ?><p style="color:red"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post">
  <label>Email<br><input type="email" name="email" required></label>
  <label>Password<br><input type="password" name="password" required></label>
  <button type="submit">Login</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
