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
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

?>
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Welcome, <?=htmlspecialchars($user['full_name'])?></h2>
            <small class="text-muted">Role: <?= ($role_id==1)?'Admin':(($role_id==2)?'Staff':'Citizen') ?></small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Complaints</h5>
                <p class="display-6 mb-0"><?= (int)$counts['complaints'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="display-6 mb-0"><?= (int)$counts['users'] ?></p>
            </div>
        </div>
    </div>

    <div class="col-12 mt-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Recent Complaints</h5>
                <?php
                $q2 = $conn->prepare('SELECT c.complaint_id, s.service_name, a.district_name, a.neighborhood_name, c.submitted_at, c.current_status_id FROM complaints c JOIN services s ON c.service_id=s.service_id JOIN areas a ON c.area_id=a.area_id ORDER BY c.submitted_at DESC LIMIT 10');
                $q2->execute(); $r2 = $q2->get_result();
                if ($r2->num_rows): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable">
                            <thead><tr><th>ID</th><th>Service</th><th>Area</th><th>Submitted</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php while ($row = $r2->fetch_assoc()): ?>
                                <tr>
                                    <td><?= (int)$row['complaint_id'] ?></td>
                                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td><?= htmlspecialchars($row['district_name'].' - '.$row['neighborhood_name']) ?></td>
                                    <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                                    <td><?= (int)$row['current_status_id'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent complaints.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php

include __DIR__ . '/../includes/footer.php';
