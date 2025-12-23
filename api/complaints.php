<?php
// api/complaints.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/session.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$role = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

if ($method === 'GET') {
    // List complaints. Admin/Staff see all, Citizens see their own via citizens.user_id
    if ($role == 1 || $role == 2) {
        $q = $conn->prepare('SELECT c.*, ci.user_id AS user_id, s.service_name, a.district_name, a.neighborhood_name FROM complaints c JOIN citizens ci ON c.citizen_id = ci.citizen_id JOIN services s ON c.service_id = s.service_id JOIN areas a ON c.area_id = a.area_id ORDER BY c.submitted_at DESC');
        $q->execute();
    } else {
        $q = $conn->prepare('SELECT c.*, ci.user_id AS user_id, s.service_name, a.district_name, a.neighborhood_name FROM complaints c JOIN citizens ci ON c.citizen_id = ci.citizen_id JOIN services s ON c.service_id = s.service_id JOIN areas a ON c.area_id = a.area_id WHERE ci.user_id = ? ORDER BY c.submitted_at DESC');
        $q->bind_param('i', $user_id); $q->execute();
    }
    $res = $q->get_result(); $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['data'=>$rows]); exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    // citizen must exist
    $cstmt = $conn->prepare('SELECT citizen_id FROM citizens WHERE user_id = ? LIMIT 1'); $cstmt->bind_param('i', $user_id); $cstmt->execute(); $cres = $cstmt->get_result();
    if ($crow = $cres->fetch_assoc()) { $citizen_id = (int)$crow['citizen_id']; } else { $ins = $conn->prepare('INSERT INTO citizens (user_id, created_at) VALUES (?, NOW())'); $ins->bind_param('i', $user_id); $ins->execute(); $citizen_id = $ins->insert_id; }

    $service_id = (int)($input['service_id'] ?? 0);
    $area_id = (int)($input['area_id'] ?? 0);
    $priority_id = (int)($input['priority_id'] ?? 2);
    $description = trim($input['description'] ?? '');
    if (!$service_id || !$area_id || !$description) { http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }

    $status_id = 1; // submitted
    $stmt = $conn->prepare('INSERT INTO complaints (citizen_id, service_id, area_id, priority_id, current_status_id, description, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiiiis', $citizen_id, $service_id, $area_id, $priority_id, $status_id, $description);
    $stmt->execute();
    echo json_encode(['success'=>true,'id'=>$stmt->insert_id]); exit;
}

if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    $complaint_id = (int)($input['complaint_id'] ?? $input['id'] ?? 0);
    $new_status = (int)($input['current_status_id'] ?? 0);
    $comment = $input['comment'] ?? null;
    if (!$complaint_id || !$new_status) { http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }

    // fetch old status
    $s = $conn->prepare('SELECT current_status_id FROM complaints WHERE complaint_id = ? LIMIT 1'); $s->bind_param('i', $complaint_id); $s->execute(); $old = $s->get_result()->fetch_assoc();
    if (!$old) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    $old_status = (int)$old['current_status_id'];

    // update complaint status
    $u = $conn->prepare('UPDATE complaints SET current_status_id = ?, resolved_at = CASE WHEN ? = (SELECT status_id FROM complaint_statuses WHERE is_final = 1 LIMIT 1) THEN NOW() ELSE resolved_at END WHERE complaint_id = ?');
    $u->bind_param('iii', $new_status, $new_status, $complaint_id); $u->execute();

        // log assignment/event: determine department from complaint->service
        $dept_stmt = $conn->prepare('SELECT s.department_id FROM complaints c JOIN services s ON c.service_id = s.service_id WHERE c.complaint_id = ? LIMIT 1');
        $dept_stmt->bind_param('i', $complaint_id); $dept_stmt->execute();
        $dept_row = $dept_stmt->get_result()->fetch_assoc();
        if ($dept_row && isset($dept_row['department_id'])) {
            $assigned_department_id = (int)$dept_row['department_id'];
            $e = $conn->prepare('INSERT INTO complaint_assignments (complaint_id, assigned_department_id, assigned_user_id, assigned_at) VALUES (?, ?, ?, NOW())');
            if ($e) { $e->bind_param('iii', $complaint_id, $assigned_department_id, $_SESSION['user_id']); $e->execute(); }
        }

    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit;
