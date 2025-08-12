<?php
include 'navbar.php';        // Sidebar + Topbar
require_once 'koneksi_log.php';

// Ambil semua log
$stmt = $pdo_log->query("SELECT * FROM log_aktivitas ORDER BY waktu DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                window.location.href = 'log_riwayat.php';
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
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .content {
            margin-top: 80px;
            /* Biar tidak ketutup topbar */
            margin-left: 250px;
            /* Biar tidak ketutup sidebar */
            padding: 20px;
        }

        .card-box {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(90deg, #007bff, #00b4d8);
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5ff;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="content">
        <div class="card-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Riwayat Aktivitas</h3>
                <button class="btn btn-danger btn-sm" onclick="hapusSemua()">Hapus Semua</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Aksi</th>
                            <th>Oleh</th>
                            <th>Keterangan</th>
                            <th>Waktu</th>
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php $no = 1;
                            foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($log['aksi']) ?></td>
                                    <td><?= htmlspecialchars($log['oleh']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($log['keterangan'])) ?></td>
                                    <td><?= htmlspecialchars($log['waktu']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="hapusLog(<?= $log['id'] ?>)">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-muted">Tidak ada riwayat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function hapusSemua() {
            Swal.fire({
                title: 'Hapus semua riwayat?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus semua'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'log_riwayat.php?hapus=semua';
                }
            });
        }

        function hapusLog(id) {
            Swal.fire({
                title: 'Hapus log ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_log.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'id';
                    input.value = id;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

</body>

</html>