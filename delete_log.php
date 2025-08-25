<?php
require_once 'koneksi_log.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $hapus = $pdo_log->prepare("DELETE FROM log_riwayat WHERE id = ?");
    $hapus->execute([$id]);

    if ($hapus->rowCount() > 0) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Log berhasil dihapus.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'riwayat.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Log tidak ditemukan atau sudah terhapus.',
                }).then(() => {
                    window.location.href = 'riwayat.php';
                });
            });
        </script>";
    }
} else {
    header("Location: riwayat.php");
    exit();
}
