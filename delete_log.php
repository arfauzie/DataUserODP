<?php
require_once 'koneksi_log.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo_log->prepare("DELETE FROM log_aktivitas WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: log_view.php");
exit;
