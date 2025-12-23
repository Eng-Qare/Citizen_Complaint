<?php
// basic uploader helper
function save_uploaded_file($file_field, $targetDirRelative = 'uploads/complaints') {
    if (empty($_FILES[$file_field]['name'])) return null;
    $up = $_FILES[$file_field];
    if ($up['error'] !== UPLOAD_ERR_OK) return null;
    $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
    $targetDir = __DIR__ . '/../' . $targetDirRelative;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = time() . '_' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
    $dest = $targetDir . '/' . $filename;
    if (move_uploaded_file($up['tmp_name'], $dest)) return $targetDirRelative . '/' . $filename;
    return null;
}
