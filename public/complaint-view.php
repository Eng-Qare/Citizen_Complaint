<?php
// public/complaint-view.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user(); $role = $user['role_id'];
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: complaint-track.php'); exit; }

// fetch complaint
$stmt = $conn->prepare('SELECT c.*, ci.user_id, s.service_name, a.district_name, a.neighborhood_name, p.priority_name, st.status_name FROM complaints c JOIN citizens ci ON c.citizen_id=ci.citizen_id JOIN services s ON c.service_id=s.service_id JOIN areas a ON c.area_id=a.area_id JOIN priorities p ON c.priority_id=p.priority_id JOIN complaint_statuses st ON c.current_status_id=st.status_id WHERE c.complaint_id = ? LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute(); $complaint = $stmt->get_result()->fetch_assoc();
if (!$complaint) { header('Location: complaint-track.php'); exit; }

// permission: citizen owner or admin/staff
if ($role == 4 && $complaint['user_id'] != $user['user_id']) { http_response_code(403); echo 'Forbidden'; exit; }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($role == 1 || $role == 2)) {
    $new_status = (int)($_POST['current_status_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($new_status) {
        $u = $conn->prepare('UPDATE complaints SET current_status_id = ? WHERE complaint_id = ?');
        $u->bind_param('ii', $new_status, $id); $u->execute();
        // log assignment/event: determine department from complaint->service
        $dept_stmt = $conn->prepare('SELECT s.department_id FROM complaints c JOIN services s ON c.service_id = s.service_id WHERE c.complaint_id = ? LIMIT 1');
        $dept_stmt->bind_param('i', $id); $dept_stmt->execute(); $dept_row = $dept_stmt->get_result()->fetch_assoc();
        if ($dept_row && isset($dept_row['department_id'])) {
            $assigned_department_id = (int)$dept_row['department_id'];
            $e = $conn->prepare('INSERT INTO complaint_assignments (complaint_id, assigned_department_id, assigned_user_id, assigned_at) VALUES (?, ?, ?, NOW())');
            if ($e) { $e->bind_param('iii', $id, $assigned_department_id, $user['user_id']); $e->execute(); }
        }
        $message = 'Status updated.';
        // reload complaint
        $stmt->execute(); $complaint = $stmt->get_result()->fetch_assoc();
    }
}

// fetch assignments/events
$events = [];
$es = $conn->prepare('SELECT assignment_id, assigned_department_id, assigned_user_id, assigned_at FROM complaint_assignments WHERE complaint_id = ? ORDER BY assigned_at DESC');
$es->bind_param('i', $id); $es->execute(); $er = $es->get_result(); while ($row = $er->fetch_assoc()) $events[] = $row;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

echo '<h2>Complaint #' . (int)$complaint['complaint_id'] . '</h2>';
if ($message) echo '<p style="color:green;">' . htmlspecialchars($message) . '</p>';
echo '<p><strong>Service:</strong> ' . htmlspecialchars($complaint['service_name']) . '</p>';
echo '<p><strong>Area:</strong> ' . htmlspecialchars($complaint['district_name'] . ' - ' . $complaint['neighborhood_name']) . '</p>';
echo '<p><strong>Priority:</strong> ' . htmlspecialchars($complaint['priority_name']) . '</p>';
echo '<p><strong>Status:</strong> ' . htmlspecialchars($complaint['status_name']) . '</p>';
echo '<p><strong>Description:</strong><br>' . nl2br(htmlspecialchars($complaint['description'])) . '</p>';

if ($role == 1 || $role == 2) {
    // status update form
    echo '<h3>Update Status</h3>';
    echo '<form id="status-form" method="post" data-complaint-id="' . (int)$complaint['complaint_id'] . '">';
    echo '<select name="current_status_id">';
    $ss = $conn->prepare('SELECT status_id, status_name FROM complaint_statuses ORDER BY status_order'); $ss->execute(); $sres = $ss->get_result();
    while ($s = $sres->fetch_assoc()) {
        $sel = ($s['status_id'] == $complaint['current_status_id']) ? 'selected' : '';
        echo '<option value="' . (int)$s['status_id'] . '" ' . $sel . '>' . htmlspecialchars($s['status_name']) . '</option>';
    }
    echo '</select><br><label>Comment<br><textarea name="comment" rows="4"></textarea></label><br><button type="submit">Update</button></form>';
}

echo '<h3>Events / Assignments</h3>';
if ($events) {
    echo '<ul>';
    foreach ($events as $ev) {
        echo '<li>Assigned user: ' . htmlspecialchars($ev['assigned_user_id'] ?? '') . ' at ' . htmlspecialchars($ev['assigned_at']) . '</li>';
    }
    echo '</ul>';
} else { echo '<p>No events.</p>'; }

include __DIR__ . '/../includes/footer.php';