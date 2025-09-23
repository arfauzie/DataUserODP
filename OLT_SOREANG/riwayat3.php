<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

include '../navbar.php';
require_once 'config3.php';
require_once 'log_helper.php';

$riwayat = getRiwayat($pdo3);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat OLT MSN</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content-wrapper {
            margin-left: 250px;
            /* lebar sidebar */
            padding: 80px 20px 20px 20px;
            /* topbar + padding */
        }

        h2 {
            font-weight: 600;
        }

        tr.clickable-row {
            cursor: pointer;
        }

        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <h2 class="mb-4">Riwayat Aktivitas OLT SOREANG</h2>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="olt_soreang.php" class="btn btn-secondary">Kembali</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Aksi</th>
                    <th style="width: 50%;">Keterangan</th>
                    <th style="width: 20%;">Waktu</th>
                    <th style="width: 10%;">Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['aksi']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td><?= htmlspecialchars($row['waktu']) ?></td>
                        <td>
                            <a href="delete_log.php?id=<?= $row['id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus riwayat ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>