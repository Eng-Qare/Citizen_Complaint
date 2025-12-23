<?php
// simple file logger
function app_log($msg) {
    $dir = __DIR__ . '/../logs'; if (!is_dir($dir)) mkdir($dir, 0755, true);
    $file = $dir . '/app.log';
    $line = '[' . date('c') . '] ' . $msg . "\n";
    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}