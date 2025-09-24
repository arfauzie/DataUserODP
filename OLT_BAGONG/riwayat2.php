<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

include '../navbar.php';
require_once 'config2.php';
require_once 'log_helper.php';

$riwayat = getRiwayat($pdo2);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat OLT MSN</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content-wrapper {
            margin-left: 250px;
            padding: 80px 20px 20px 20px;
        }

        h2 {
            font-weight: 600;
        }

        /* Card box untuk tabel */
        .card {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table {
            text-align: center;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
            word-break: break-word;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <h2 class="mb-4">Riwayat Aktivitas OLT MSN</h2>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="olt_bagong.php" class="btn btn-secondary">Kembali</a>
            <form method="post" action="delete_log.php" onsubmit="return confirm('Hapus semua riwayat?');">
                <button type="submit" name="hapus_semua" class="btn btn-danger">Hapus Semua</button>
            </form>
        </div>

        <div class="card">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 15%;">Aksi</th>
                        <th style="width: 35%;">Keterangan</th>
                        <th style="width: 15%;">Oleh</th>
                        <th style="width: 20%;">Waktu</th>
                        <th style="width: 15%;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $row): ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($row['aksi']) ?></td>
                            <td class="text-center"><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['oleh'] ?? '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['waktu']) ?></td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm delete-btn"
                                    data-aksi="<?= htmlspecialchars($row['aksi']) ?>"
                                    data-keterangan="<?= htmlspecialchars($row['keterangan']) ?>"
                                    data-waktu="<?= htmlspecialchars($row['waktu']) ?>">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".delete-btn");
            buttons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault(); // cegah submit form default

                    const aksi = this.dataset.aksi;
                    const keterangan = this.dataset.keterangan;
                    const waktu = this.dataset.waktu;

                    Swal.fire({
                        title: 'Yakin?',
                        text: "Data log ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Buat form sementara untuk POST
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'delete2_log.php';

                            ['aksi', 'keterangan', 'waktu'].forEach(key => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = eval(key); // isi dari dataset
                                form.appendChild(input);
                            });

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // ✅ SweetAlert hasil hapus
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Riwayat berhasil dihapus.',
                    timer: 2000,
                    showConfirmButton: false
                });
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Riwayat gagal dihapus.',
                });
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'invalid'): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'ID Tidak Valid',
                    text: 'Log yang dipilih tidak ditemukan.',
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>