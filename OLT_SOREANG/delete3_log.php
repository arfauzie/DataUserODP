<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php'; // config untuk OLT 3

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $pdo3->prepare("DELETE FROM riwayat3 WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: riwayat3.php?success=deleted");
    } else {
        header("Location: riwayat3.php?error=failed");
    }
    exit();
}
