<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config2.php';
require_once '../log_helper.php'; // Pastikan path file log_helper.php benar
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hapus PON</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Ambil data PON sebelum dihapus
        $stmt = $pdo2->prepare("SELECT * FROM pon2 WHERE id = ?");
        $stmt->execute([$id]);
        $pon = $stmt->fetch();

        if (!$pon) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak ditemukan!',
                    text: 'Data PON tidak ditemukan!',
                    showConfirmButton: true
                }).then(() => {
                    window.location = 'olt_bagong.php';
                });
            });
        </script>";
            exit();
        }

        // Cek apakah ada ODP terkait
        $stmt = $pdo2->prepare("SELECT COUNT(*) FROM odp2 WHERE pon_id = ?");
        $stmt->execute([$id]);
        $odp_count = $stmt->fetchColumn();

        if ($odp_count > 0) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal!',
                    text: 'Tidak bisa menghapus PON yang memiliki ODP terkait!',
                    showConfirmButton: true
                }).then(() => {
                    window.location = 'olt_bagong.php';
                });
            });
        </script>";
            exit();
        }

        // Ambil nama admin dari session
        $oleh = is_array($_SESSION['admin']) ? ($_SESSION['admin']['username'] ?? 'admin') : $_SESSION['admin'];

        // Siapkan log
        $log_keterangan = [];

        $log_keterangan[] = "ID PON: " . $pon['id'];
        $log_keterangan[] = "Nama PON: " . ($pon['nama_pon'] ?? '(kosong)');

        // Pastikan kolom jumlah_port ada dan tidak null
        if (isset($pon['jumlah_port']) && $pon['jumlah_port'] !== null && $pon['jumlah_port'] !== '') {
            $log_keterangan[] = "Jumlah Port: " . $pon['jumlah_port'];
        } else {
            $log_keterangan[] = "Jumlah Port: (tidak tersedia)";
        }

        // Hapus PON
        $stmt = $pdo2->prepare("DELETE FROM pon2 WHERE id = ?");
        if ($stmt->execute([$id])) {
            tambahRiwayat("Hapus PON", $oleh, $log_keterangan);

            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'PON berhasil dihapus!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_bagong.php';
                });
            });
        </script>";
        } else {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menghapus PON!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_bagong.php';
                });
            });
        </script>";
        }
    }
    ?>
</body>

</html>