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
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <h3>Complaint #<?= (int)$complaint['complaint_id'] ?></h3>
            <?php if ($message): ?><span class="badge bg-success">Updated</span><?php endif; ?>
        </div>
        <div class="row mt-3">
            <div class="col-md-8">
                <div class="mb-2"><strong>Service:</strong> <?= htmlspecialchars($complaint['service_name']) ?></div>
                <div class="mb-2"><strong>Area:</strong> <?= htmlspecialchars($complaint['district_name'] . ' - ' . $complaint['neighborhood_name']) ?></div>
                <div class="mb-2"><strong>Priority:</strong> <?= htmlspecialchars($complaint['priority_name']) ?></div>
                <div class="mb-2"><strong>Status:</strong> <?= htmlspecialchars($complaint['status_name']) ?></div>
                <div class="mb-3"><strong>Description:</strong><div class="border rounded p-2 bg-light mt-1"><?= nl2br(htmlspecialchars($complaint['description'])) ?></div></div>
            </div>
            <div class="col-md-4">
                <?php if ($role == 1 || $role == 2): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Status</h5>
                        <form id="status-form" method="post" data-complaint-id="<?= (int)$complaint['complaint_id'] ?>">
                            <div class="mb-2">
                                <select name="current_status_id" class="form-select">
                                <?php $ss = $conn->prepare('SELECT status_id, status_name FROM complaint_statuses ORDER BY status_order'); $ss->execute(); $sres = $ss->get_result();
                                    while ($s = $sres->fetch_assoc()): $sel = ($s['status_id'] == $complaint['current_status_id']) ? 'selected' : ''; ?>
                                    <option value="<?= (int)$s['status_id'] ?>" <?= $sel ?>><?= htmlspecialchars($s['status_name']) ?></option>
                                <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-2"><textarea class="form-control" name="comment" rows="3" placeholder="Optional comment"></textarea></div>
                            <div class="d-grid"><button class="btn btn-primary" type="submit">Update</button></div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <h5 class="mt-4">Events / Assignments</h5>
        <?php if ($events): ?>
            <ul class="list-group">
                <?php foreach ($events as $ev): ?>
                    <li class="list-group-item">Assigned user: <?= htmlspecialchars($ev['assigned_user_id'] ?? '') ?> at <?= htmlspecialchars($ev['assigned_at']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No events.</p>
        <?php endif; ?>
    </div>
</div>

<?php

include __DIR__ . '/../includes/footer.php';