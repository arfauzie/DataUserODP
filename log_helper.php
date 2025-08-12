<?php
require_once __DIR__ . '/koneksi_log.php';

function tambahRiwayat($aksi, $oleh, $keterangan = '-')
{
    global $pdo_log;

    if (!isset($pdo_log) || !$pdo_log) {
        error_log("Koneksi log tidak tersedia");
        return false;
    }

    // Pastikan $oleh string
    if (is_array($oleh)) {
        $oleh = $oleh['username'] ?? 'unknown';
    }
    $oleh = trim((string)$oleh) ?: 'unknown';

    // Format keterangan
    if (is_array($keterangan)) {
        $keterangan = implode("\n", $keterangan);
    } elseif (is_object($keterangan)) {
        $keterangan = json_encode($keterangan, JSON_UNESCAPED_UNICODE);
    }
    $keterangan = trim($keterangan) ?: '-';

    try {
        $stmt = $pdo_log->prepare(
            "INSERT INTO log_aktivitas (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$aksi, $oleh, $keterangan]);
        return true;
    } catch (PDOException $e) {
        error_log("Gagal menulis log: " . $e->getMessage());
        return false;
    }
}
