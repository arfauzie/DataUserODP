<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config.php'; // koneksi database $pdo
require_once '../log_helper.php'; // fungsi tambahRiwayat
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hapus User</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Ambil data user sebelum dihapus
        $stmt = $pdo->prepare("SELECT * FROM users1 WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak ditemukan!',
                    text: 'Data user tidak ditemukan!',
                    showConfirmButton: true
                }).then(() => {
                    window.location = 'olt_msn.php';
                });
            });
        </script>";
            exit();
        }

        // Ambil nama admin dari session
        $oleh = is_array($_SESSION['admin']) ? ($_SESSION['admin']['username'] ?? 'admin') : $_SESSION['admin'];

        // Siapkan keterangan log TANPA ID apapun
        $log_keterangan = [];
        $log_keterangan[] = "Nama User: " . ($user['nama_user'] ?? '(kosong)');
        $log_keterangan[] = "Nomor Internet: " . ($user['nomor_internet'] ?? '(kosong)');
        $log_keterangan[] = "Alamat: " . ($user['alamat'] ?? '(kosong)');
        // ODP ID tidak dimasukkan ke log

        // Eksekusi hapus
        $stmt = $pdo->prepare("DELETE FROM users1 WHERE id = ?");
        if ($stmt->execute([$id])) {
            tambahRiwayat("Hapus User", $oleh, implode("\n", $log_keterangan));
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'User berhasil dihapus!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_msn.php';
                });
            });
        </script>";
        } else {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menghapus user!',
                    showConfirmButton: true
                }).then(() => {
                    window.location = 'olt_msn.php';
                });
            });
        </script>";
        }
    }
    ?>
</body>

</html>