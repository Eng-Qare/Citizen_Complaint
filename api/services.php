<?php
// api/services.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$stmt = $conn->prepare('SELECT service_id, service_name, expected_resolution_hours FROM services WHERE is_active = 1 ORDER BY service_name');
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
echo json_encode(['data' => $rows]);
exit;
