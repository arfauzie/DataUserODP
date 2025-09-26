<?php
function tambahRiwayatMSN($pdo, $aksi, $oleh, $keterangan)
{
    $stmt = $pdo->prepare("INSERT INTO riwayat1 (aksi, oleh, keterangan, waktu) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$aksi, $oleh, $keterangan]);
}

function getRiwayatMSN($pdo, $limit = null)
{
    $sql = "SELECT * FROM riwayat1 ORDER BY waktu DESC";
    if ($limit !== null) {
        $sql .= " LIMIT :limit";
    }

    $stmt = $pdo->prepare($sql);
    if ($limit !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteRiwayatMSN($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM riwayat1 WHERE id = ?");
    return $stmt->execute([$id]);
}
