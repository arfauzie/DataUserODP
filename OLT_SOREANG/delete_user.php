<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php'; // koneksi database $pdo3
require_once 'log_helper.php'; // fungsi tambahRiwayat
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
        $user_id = (int) $_GET['id'];

        // Ambil data user sebelum dihapus
        $stmt = $pdo3->prepare("SELECT * FROM users3 WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Tidak ditemukan!',
                text: 'Data user tidak ditemukan!',
                confirmButtonText: 'OK'
            }).then(() => { window.location = 'olt_soreang.php'; });
            </script>";
            exit();
        }

        $oleh = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Unknown';

        // Siapkan log dengan format konsisten
        $nama_user      = $user['nama_user'] ?? '(kosong)';
        $nomor_internet = $user['nomor_internet'] ?? '(kosong)';
        $alamat         = $user['alamat'] ?? '(kosong)';
        $log_keterangan = "Nama User: $nama_user | Nomor Internet: $nomor_internet | Alamat: $alamat";

        // Eksekusi hapus
        $stmt = $pdo3->prepare("DELETE FROM users3 WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            // Tambahkan log dengan helper baru (fallback ke lama)
            if (function_exists('tambahRiwayatSoreang')) {
                tambahRiwayatSoreang($pdo3, "Hapus User", $oleh, $log_keterangan);
            } else {
                tambahRiwayatSoreang($pdo3, "Hapus User", $oleh, $log_keterangan);
            }

            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'User berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => { window.location = 'olt_soreang.php'; });
            </script>";
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus user!',
                confirmButtonText: 'OK'
            }).then(() => { window.location = 'olt_soreang.php'; });
            </script>";
        }
    } else {
        echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Error!',
            text: 'ID user tidak valid.',
            timer: 2000,
            showConfirmButton: false
        }).then(() => { window.location = 'olt_soreang.php'; });
        </script>";
    }
    ?>
</body>

</html>