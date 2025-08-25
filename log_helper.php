<?php
require_once __DIR__ . '/koneksi_log.php';

function tambahRiwayat($aksi, $oleh, $keterangan = '-')
{
    global $pdo_log;

    if (!$pdo_log) {
        error_log("Koneksi log tidak tersedia");
        return false;
    }

    try {
        $stmt = $pdo_log->prepare("INSERT INTO log_riwayat (aksi, oleh, keterangan) VALUES (?, ?, ?)");
        return $stmt->execute([$aksi, $oleh, $keterangan]);
    } catch (PDOException $e) {
        error_log("Gagal menambah log: " . $e->getMessage());
        return false;
    }
}
