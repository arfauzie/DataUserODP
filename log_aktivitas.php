<?php
require_once 'koneksi_log.php';

$stmt = $pdo_log->query("SELECT * FROM log_aktivitas ORDER BY waktu DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
