<?php
require_once __DIR__ . '/koneksi_log.php';

/**
 * Menambahkan riwayat aktivitas ke tabel log_aktivitas
 *
 * @param string $aksi        Nama aksi, misal: "Tambah PON", "Update User"
 * @param string|array $oleh  Nama user/admin atau array data session
 * @param mixed $keterangan   Detail aktivitas (string, array, atau object)
 * @return bool               True jika berhasil, false jika gagal
 */
function tambahRiwayat($aksi, $oleh, $keterangan = '-')
{
    global $pdo_log;

    // Pastikan koneksi tersedia
    if (!isset($pdo_log) || !$pdo_log) {
        error_log("[LOG ERROR] Koneksi log tidak tersedia");
        return false;
    }

    // Pastikan $oleh string
    if (is_array($oleh)) {
        $oleh = $oleh['username'] ?? 'unknown';
    }
    $oleh = trim((string)$oleh) ?: 'unknown';

    // Format keterangan jadi string
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
        return $stmt->execute([$aksi, $oleh, $keterangan]);
    } catch (PDOException $e) {
        error_log("[LOG ERROR] Gagal menulis log: " . $e->getMessage());
        return false;
    }
}
