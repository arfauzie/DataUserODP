<?php
require_once 'koneksi_log.php'; // Pastikan file ini memuat koneksi $pdo_log

function tambahRiwayat($aksi, $oleh, $keterangan = null)
{
    global $pdo_log;

    // Jika $oleh array (contoh: $_SESSION['admin']), ambil username-nya
    if (is_array($oleh)) {
        $oleh = isset($oleh['username']) ? $oleh['username'] : 'unknown';
    }

    // Pastikan $keterangan tidak menyimpan array mentah
    if (is_array($keterangan)) {
        // Jika array numerik
        if (array_keys($keterangan) === range(0, count($keterangan) - 1)) {
            $keterangan = implode("\n", $keterangan);
        } else {
            // Jika array asosiatif
            $baris = [];
            foreach ($keterangan as $key => $value) {
                // Pastikan value bukan array nested
                if (is_array($value)) {
                    $value = json_encode($value); // fallback jika ada array nested
                }
                $baris[] = "$key: $value";
            }
            $keterangan = implode("\n", $baris);
        }
    } elseif (is_object($keterangan)) {
        $keterangan = json_encode($keterangan); // fallback jika object
    }

    // Amankan NULL jika kosong
    if (empty($keterangan)) {
        $keterangan = '-';
    }

    try {
        $stmt = $pdo_log->prepare("INSERT INTO log_aktivitas (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$aksi, $oleh, $keterangan]);
    } catch (PDOException $e) {
        error_log("Gagal menulis log aktivitas: " . $e->getMessage());
    }
}
