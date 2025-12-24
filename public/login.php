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
           // if (password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$row['user_id'];
                $_SESSION['role_id'] = (int)$row['role_id'];
                $_SESSION['full_name'] = $row['full_name'];
                header('Location: /Citizen_Complaint/public/dashboard.php');
                exit;
          ////  } else {
           //     $error = 'Invalid credentials.';
          //  }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex align-items-center justify-content-center" style="min-height:70vh;">
    <div class="card shadow-sm w-100" style="max-width:420px;">
        <div class="card-body">
            <h4 class="card-title mb-3">Sign in</h4>
            <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Email<input class="form-control" type="email" name="email" required></label></div>
                <div class="mb-3"><label class="form-label">Password<input class="form-control" type="password" name="password" required></label></div>
                <div class="d-grid"><button class="btn btn-primary" type="submit">Login</button></div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>