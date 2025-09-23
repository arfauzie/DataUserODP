<?php
function tambahRiwayat($aksi, $oleh, $keterangan)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO riwayat3 (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$aksi, $oleh, $keterangan]);
}

function getRiwayat($pdo)
{
    $stmt = $pdo->query("SELECT * FROM riwayat3 ORDER BY waktu DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteRiwayat($id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM riwayat3 WHERE id = ?");
    return $stmt->execute([$id]);
}
