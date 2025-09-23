<?php
function tambahRiwayat($pdo, $aksi, $oleh, $keterangan)
{
    $stmt = $pdo->prepare("INSERT INTO riwayat1 (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$aksi, $oleh, $keterangan]);
}

function getRiwayat($pdo)
{
    $stmt = $pdo->query("SELECT * FROM riwayat1 ORDER BY waktu DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteRiwayat($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM riwayat1 WHERE id = ?");
    return $stmt->execute([$id]);
}
