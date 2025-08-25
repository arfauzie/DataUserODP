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
        $id = (int) $_GET['id'];

        // Ambil data user sebelum dihapus
        $stmt = $pdo->prepare("SELECT * FROM users1 WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Tidak ditemukan!',
                text: 'Data user tidak ditemukan!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location = 'olt_msn.php';
            });
        </script>";
            exit();
        }

        // Ambil nama admin dari session
        $oleh = isset($_SESSION['admin']['username']) ? $_SESSION['admin']['username'] : 'admin';

        // Siapkan log dengan format konsisten
        $nama_user      = $user['nama_user'] ?? '(kosong)';
        $nomor_internet = $user['nomor_internet'] ?? '(kosong)';
        $alamat         = $user['alamat'] ?? '(kosong)';

        $log_keterangan = "Nama User: $nama_user | Nomor Internet: $nomor_internet | Alamat: $alamat";

        // Eksekusi hapus
        $stmt = $pdo->prepare("DELETE FROM users1 WHERE id = ?");
        if ($stmt->execute([$id])) {
            tambahRiwayat("Hapus User", $oleh, $log_keterangan);

            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'User berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location = 'olt_msn.php';
            });
        </script>";
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus user!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location = 'olt_msn.php';
            });
        </script>";
        }
    }
    ?>
</body>

</html>