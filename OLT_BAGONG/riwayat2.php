<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

include '../navbar.php';
require_once 'config2.php';
require_once 'log_helper.php';

$riwayat = getRiwayatBagong($pdo2); // gunakan fungsi khusus OLT BAGONG
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat OLT BAGONG</title>
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

        .card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        /* Styling tabel ala transaction log */
        .table {
            border-collapse: separate;
            border-spacing: 0 6px;
        }

        .table thead th {
            background-color: #f1f3f5;
            border: none;
            font-weight: 600;
            color: #495057;
            text-align: left;
        }

        .table tbody tr {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .table tbody td {
            border: none;
            vertical-align: middle;
            font-size: 14px;
            color: #343a40;
            padding: 12px;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <h2 class="mb-4">Riwayat Aktivitas OLT BAGONG</h2>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="olt_bagong.php" class="btn btn-secondary">Kembali</a>
            <form id="hapusSemuaForm" method="post" action="delete2_log.php" style="margin:0;">
                <input type="hidden" name="hapus_semua" value="1">
                <button type="button" id="hapusSemuaBtn" class="btn btn-danger">Hapus Semua</button>
            </form>
        </div>

        <div class="card">
            <table class="table">
                <thead>
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
                            <td><?= htmlspecialchars($row['aksi']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                            <td><?= htmlspecialchars($row['oleh'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                            <td>
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
            // 🔹 Hapus per-baris
            const buttons = document.querySelectorAll(".delete-btn");
            buttons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();

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
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'delete2_log.php';

                            ['aksi', 'keterangan', 'waktu'].forEach(key => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = eval(key);
                                form.appendChild(input);
                            });

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // 🔹 Hapus semua log
            const hapusSemuaBtn = document.getElementById("hapusSemuaBtn");
            const hapusSemuaForm = document.getElementById("hapusSemuaForm");
            if (hapusSemuaBtn) {
                hapusSemuaBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hapus Semua?',
                        text: "Semua riwayat akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus semua!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            hapusSemuaForm.submit();
                        }
                    });
                });
            }

            // 🔹 SweetAlert hasil hapus
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