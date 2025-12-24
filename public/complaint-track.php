<?php
// public/complaint-track.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user(); $role = $user['role_id'];

// fetch complaints for display
if ($role == 1 || $role == 2) {
    $q = $conn->prepare('SELECT c.complaint_id, c.description, c.submitted_at, c.current_status_id, s.service_name, a.district_name, a.neighborhood_name, ci.user_id FROM complaints c JOIN services s ON c.service_id=s.service_id JOIN areas a ON c.area_id=a.area_id JOIN citizens ci ON c.citizen_id=ci.citizen_id ORDER BY c.submitted_at DESC');
    $q->execute();
} else {
    $q = $conn->prepare('SELECT c.complaint_id, c.description, c.submitted_at, c.current_status_id, s.service_name, a.district_name, a.neighborhood_name FROM complaints c JOIN services s ON c.service_id=s.service_id JOIN areas a ON c.area_id=a.area_id JOIN citizens ci ON c.citizen_id=ci.citizen_id WHERE ci.user_id = ? ORDER BY c.submitted_at DESC');
    $q->bind_param('i', $user['user_id']); $q->execute();
}
$res = $q->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<h2>Complaint Tracking</h2>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead class="table-light"><tr><th>ID</th><th>Service</th><th>Area</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($r = $res->fetch_assoc()):
                        $id = (int)$r['complaint_id'];
                ?>
                <tr>
                    <td><?= $id ?></td>
                    <td><?= htmlspecialchars($r['service_name']) ?></td>
                    <td><?= htmlspecialchars($r['district_name'] . ' - ' . $r['neighborhood_name']) ?></td>
                    <td><?= htmlspecialchars($r['submitted_at']) ?></td>
                    <td><?= (int)$r['current_status_id'] ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="complaint-view.php?id=<?= $id ?>">View</a></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

include __DIR__ . '/../includes/footer.php';
