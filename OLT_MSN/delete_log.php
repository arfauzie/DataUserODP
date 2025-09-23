<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

// Hapus semua log
if (isset($_POST['hapus_semua'])) {
    try {
        $pdo->exec("TRUNCATE TABLE riwayat1");
        header("Location: riwayat.php?status=success");
        exit;
    } catch (Exception $e) {
        header("Location: riwayat.php?status=error");
        exit;
    }
}

// Hapus log tertentu berdasarkan aksi + keterangan + waktu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['keterangan'], $_POST['waktu'])) {
    $aksi = $_POST['aksi'];
    $keterangan = $_POST['keterangan'];
    $waktu = $_POST['waktu'];

    $stmt = $pdo->prepare("DELETE FROM riwayat1 WHERE aksi = :aksi AND keterangan = :keterangan AND waktu = :waktu LIMIT 1");
    $result = $stmt->execute([
        ':aksi' => $aksi,
        ':keterangan' => $keterangan,
        ':waktu' => $waktu
    ]);

    if ($result) {
        header("Location: riwayat.php?status=success");
    } else {
        header("Location: riwayat.php?status=error");
    }
    exit;
}

// Redirect jika akses langsung
header("Location: riwayat.php");
exit;
