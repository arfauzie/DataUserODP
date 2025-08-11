<?php
include 'log_aktivitas.php';
include 'navbar.php';
require_once 'koneksi_log.php'; // koneksi ke database log

// Hapus semua riwayat
if (isset($_GET['hapus']) && $_GET['hapus'] == 'semua') {
    $hapus = $pdo_log->prepare("DELETE FROM log_aktivitas");
    $hapus->execute();
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Semua riwayat aktivitas telah dihapus.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'log_view.php';
            });
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Aktivitas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-top: 100px;
            margin-left: 260px;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .card-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 1000px;
        }

        .table {
            background-color: #fff;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }

            .card-box {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="content">
        <div class="card-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Riwayat Aktivitas</h3>
                <a href="log_view.php?hapus=semua" class="btn btn-danger btn-sm"
                    onclick="return confirm('Yakin ingin menghapus semua riwayat?')">Hapus Semua</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Aksi</th>
                            <th>Oleh</th>
                            <th>Keterangan</th>
                            <th>Waktu</th>
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        $no = 1;
                        foreach ($logs as $log) :
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($log['aksi']) ?></td>
                                <td><?= htmlspecialchars($log['oleh']) ?></td>
                                <td><?= htmlspecialchars($log['keterangan']) ?></td>
                                <td><?= $log['waktu'] ?></td>
                                <td>
                                    <form action="delete_log.php" method="POST" onsubmit="return confirm('Hapus log ini?')">
                                        <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada riwayat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>