<?php
session_start();
require_once 'config3.php';

if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

// Hapus semua log
if (isset($_POST['hapus_semua'])) {
    try {
        $pdo3->exec("TRUNCATE TABLE riwayat3");
        header("Location: riwayat3.php?status=success");
        exit;
    } catch (Exception $e) {
        header("Location: riwayat3.php?status=error");
        exit;
    }
}

// Hapus log tertentu berdasarkan aksi + keterangan + waktu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['keterangan'], $_POST['waktu'])) {
    $aksi = $_POST['aksi'];
    $keterangan = $_POST['keterangan'];
    $waktu = $_POST['waktu'];

    $stmt = $pdo3->prepare("DELETE FROM riwayat3 WHERE aksi = :aksi AND keterangan = :keterangan AND waktu = :waktu LIMIT 1");
    $result = $stmt->execute([
        ':aksi' => $aksi,
        ':keterangan' => $keterangan,
        ':waktu' => $waktu
    ]);

    if ($result) {
        header("Location: riwayat3.php?status=success");
    } else {
        header("Location: riwayat3.php?status=error");
    }
    exit;
}

// Redirect jika akses langsung
header("Location: riwayat3.php");
exit;
