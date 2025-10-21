<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php';
require_once 'log_helper.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hapus PON</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $pon_id = (int) $_GET['id'];

        // Ambil data PON sebelum dihapus
        $stmt = $pdo3->prepare("SELECT * FROM pon3 WHERE id = ?");
        $stmt->execute([$pon_id]);
        $pon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pon) {
            // Cek apakah ada ODP terkait
            $stmt = $pdo3->prepare("SELECT COUNT(*) FROM odp3 WHERE pon_id = ?");
            $stmt->execute([$pon_id]);
            $odp_count = $stmt->fetchColumn();

            if ($odp_count > 0) {
                echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal!',
                    text: 'Tidak bisa menghapus PON yang memiliki ODP terkait!',
                    confirmButtonText: 'OK'
                }).then(() => { window.location = 'olt_soreang.php'; });
                </script>";
                exit();
            }

            $oleh = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Unknown';

            // Siapkan log
            $log_keterangan  = "Nama PON: " . ($pon['nama_pon'] ?? '(kosong)') . " | ";
            $log_keterangan .= "Jumlah Port: " . ($pon['port_max'] ?? '(tidak tersedia)');

            // Hapus PON
            $stmt = $pdo3->prepare("DELETE FROM pon3 WHERE id = ?");
            if ($stmt->execute([$pon_id])) {
                // Tambahkan log (gunakan fungsi baru, fallback ke lama jika ada)
                if (function_exists('tambahRiwayatSoreang')) {
                    tambahRiwayatSoreang($pdo3, "Hapus PON", $oleh, $log_keterangan);
                } else {
                    tambahRiwayatSoreang($pdo3, "Hapus PON", $oleh, $log_keterangan);
                }

                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'PON berhasil dihapus!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { window.location = 'olt_soreang.php'; });
                </script>";
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menghapus PON!',
                    confirmButtonText: 'OK'
                }).then(() => { window.location = 'olt_soreang.php'; });
                </script>";
            }
        } else {
            echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Tidak ditemukan!',
                text: 'Data PON tidak ditemukan.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => { window.location = 'olt_soreang.php'; });
            </script>";
        }
    } else {
        echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Error!',
            text: 'ID PON tidak valid.',
            timer: 2000,
            showConfirmButton: false
        }).then(() => { window.location = 'olt_soreang.php'; });
        </script>";
    }
    ?>
</body>

</html>