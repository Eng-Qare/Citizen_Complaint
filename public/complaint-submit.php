<?php
// public/complaint-submit.php
require_once __DIR__ . '/../core/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
$user_id = (int)$user['user_id'];
$error = '';

// fetch services, areas, priorities
$svc = $conn->prepare('SELECT service_id, service_name FROM services WHERE is_active = 1'); $svc->execute(); $services = $svc->get_result()->fetch_all(MYSQLI_ASSOC);
$ar = $conn->prepare('SELECT area_id, district_name, neighborhood_name FROM areas'); $ar->execute(); $areas = $ar->get_result()->fetch_all(MYSQLI_ASSOC);
$pr = $conn->prepare('SELECT priority_id, priority_name FROM priorities'); $pr->execute(); $priorities = $pr->get_result()->fetch_all(MYSQLI_ASSOC);

// ensure citizen record exists for this user
$cstmt = $conn->prepare('SELECT citizen_id FROM citizens WHERE user_id = ? LIMIT 1');
$cstmt->bind_param('i', $user_id);
$cstmt->execute();
$cres = $cstmt->get_result();
if ($crow = $cres->fetch_assoc()) {
    $citizen_id = (int)$crow['citizen_id'];
} else {
    $ins = $conn->prepare('INSERT INTO citizens (user_id, created_at) VALUES (?, NOW())');
    $ins->bind_param('i', $user_id); $ins->execute(); $citizen_id = $ins->insert_id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $area_id = (int)($_POST['area_id'] ?? 0);
    $priority_id = (int)($_POST['priority_id'] ?? 2);
    $description = trim($_POST['description'] ?? '');

    if (!$service_id || !$area_id || !$description) {
        $error = 'Service, area and description are required.';
    } else {
        $status_id = 1; // La gudbiyey (Submitted)
        $stmt = $conn->prepare('INSERT INTO complaints (citizen_id, service_id, area_id, priority_id, current_status_id, description, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiiiis', $citizen_id, $service_id, $area_id, $priority_id, $status_id, $description);
        $stmt->execute();
        $complaint_id = $stmt->insert_id;
        header('Location: /Citizen_Complaint/public/complaint-view.php?id=' . $complaint_id);
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

if ($error) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>";
?>
<h2>Submit Complaint</h2>
<form method="post">
  <label>Service<select name="service_id" required>
    <option value="">-- Select --</option>
    <?php foreach ($services as $s): ?>
      <option value="<?= (int)$s['service_id'] ?>"><?= htmlspecialchars($s['service_name']) ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Area<select name="area_id" required>
    <option value="">-- Select --</option>
    <?php foreach ($areas as $a): ?>
      <option value="<?= (int)$a['area_id'] ?>"><?= htmlspecialchars($a['district_name'] . ' - ' . $a['neighborhood_name']) ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Priority<select name="priority_id">
    <?php foreach ($priorities as $p): ?>
      <option value="<?= (int)$p['priority_id'] ?>"><?= htmlspecialchars($p['priority_name']) ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Description<textarea name="description" rows="6" required></textarea></label>
  <button type="submit">Submit</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
