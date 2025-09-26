<?php
function tambahRiwayatBagong($pdo2, $aksi, $oleh, $keterangan)
{
    $stmt = $pdo2->prepare("INSERT INTO riwayat2 (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$aksi, $oleh, $keterangan]);
}

function getRiwayatBagong($pdo2, $limit = null)
{
    $sql = "SELECT * FROM riwayat2 ORDER BY waktu DESC";
    if ($limit !== null) {
        $sql .= " LIMIT :limit";
    }

    $stmt = $pdo2->prepare($sql);
    if ($limit !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteRiwayatBagong($pdo2, $id)
{
    $stmt = $pdo2->prepare("DELETE FROM riwayat2 WHERE id = ?");
    return $stmt->execute([$id]);
}
