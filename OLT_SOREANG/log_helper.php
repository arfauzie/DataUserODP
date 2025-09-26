<?php
function tambahRiwayatSoreang($pdo3, $aksi, $oleh, $keterangan)
{
    $stmt = $pdo3->prepare("INSERT INTO riwayat3 (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$aksi, $oleh, $keterangan]);
}

function getRiwayatSoreang($pdo3, $limit = null)
{
    $sql = "SELECT * FROM riwayat3 ORDER BY waktu DESC";
    if ($limit !== null) {
        $sql .= " LIMIT :limit";
    }

    $stmt = $pdo3->prepare($sql);
    if ($limit !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteRiwayatSoreang($pdo3, $id)
{
    $stmt = $pdo3->prepare("DELETE FROM riwayat3 WHERE id = ?");
    return $stmt->execute([$id]);
}
