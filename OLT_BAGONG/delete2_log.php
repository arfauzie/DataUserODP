<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config2.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $pdo2->prepare("DELETE FROM riwayat2 WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: riwayat2.php?success=deleted");
    } else {
        header("Location: riwayat2.php?error=failed");
    }
    exit();
}
