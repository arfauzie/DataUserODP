<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config2.php';
require_once '../log_helper.php'; // Pastikan path benar
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
        $id = (int) $_GET['id'];

        // Ambil data PON sebelum dihapus
        $stmt = $pdo2->prepare("SELECT * FROM pon2 WHERE id = ?");
        $stmt->execute([$id]);
        $pon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pon) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Tidak ditemukan!',
                text: 'Data PON tidak ditemukan!',
                confirmButtonText: 'OK'
            }).then(() => {
                location.href = 'olt_bagong.php';
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
            Swal.fire({
                icon: 'warning',
                title: 'Gagal!',
                text: 'Tidak bisa menghapus PON yang memiliki ODP terkait!',
                confirmButtonText: 'OK'
            }).then(() => {
                location.href = 'olt_bagong.php';
            });
        </script>";
            exit();
        }

        // Ambil nama admin dari session
        $oleh = is_array($_SESSION['admin']) ? ($_SESSION['admin']['username'] ?? 'admin') : $_SESSION['admin'];

        // Siapkan log tanpa ID
        $log_keterangan  = "Nama PON: " . ($pon['nama_pon'] ?? '(kosong)') . "\n";
        $log_keterangan .= "Jumlah Port: " . ($pon['port_max'] ?? '(tidak tersedia)');

        // Hapus PON
        $stmt = $pdo2->prepare("DELETE FROM pon2 WHERE id = ?");
        if ($stmt->execute([$id])) {
            tambahRiwayat("Hapus PON", $oleh, $log_keterangan);

            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'PON berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.href = 'olt_bagong.php';
            });
        </script>";
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus PON!',
                confirmButtonText: 'OK'
            }).then(() => {
                location.href = 'olt_bagong.php';
            });
        </script>";
        }
    }
    ?>
</body>

</html>