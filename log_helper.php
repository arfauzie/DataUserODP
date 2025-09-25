<?php
// koneksi ke database log global
require_once __DIR__ . '/koneksi_log.php';

// Fungsi untuk simpan riwayat
function tambahRiwayat($aksi, $oleh, $keterangan, $olt)
{
    global $pdoLog;

    $stmt = $pdoLog->prepare("INSERT INTO log_riwayat (aksi, oleh, keterangan, olt, waktu) 
                              VALUES (:aksi, :oleh, :keterangan, :olt, NOW())");
    $stmt->execute([
        ':aksi'       => $aksi,
        ':oleh'       => $oleh,
        ':keterangan' => $keterangan,
        ':olt'        => $olt
    ]);
}

// Fungsi untuk ambil semua riwayat (buat dashboard)
function getRiwayat($limit = 10)
{
    global $pdoLog;

    $stmt = $pdoLog->prepare("SELECT * FROM log_riwayat ORDER BY waktu DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
