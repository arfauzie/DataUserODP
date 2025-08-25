<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php';
require_once '../log_helper.php'; // Pastikan path benar

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hapus ODP</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $odp_id = $_GET['id'];

        // Ambil data ODP sebelum dihapus untuk log
        $stmt = $pdo3->prepare("SELECT * FROM odp3 WHERE id = ?");
        $stmt->execute([$odp_id]);
        $odp = $stmt->fetch();

        if ($odp) {
            $pon_id   = $odp['pon_id'];
            $nama_odp = $odp['nama_odp'];
            $port_odp = $odp['port_max'];

            // Siapkan log keterangan (string, bukan array)
            $log_keterangan = "Nama ODP: $nama_odp | Port Maksimum: $port_odp";

            // Ambil nama admin dari session
            $oleh = isset($_SESSION['admin']['username']) ? $_SESSION['admin']['username'] : 'admin';

            // Hapus ODP
            $stmt = $pdo3->prepare("DELETE FROM odp3 WHERE id = ?");
            if ($stmt->execute([$odp_id])) {
                // Simpan log riwayat
                tambahRiwayat("Hapus ODP", $oleh, $log_keterangan);

                echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'ODP berhasil dihapus!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location = 'olt_soreang.php?pon_id=$pon_id';
                    });
                });
            </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal menghapus ODP!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location = 'olt_soreang.php?pon_id=$pon_id';
                    });
                });
            </script>";
            }
        } else {
            // Jika data tidak ditemukan
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ditemukan!',
                    text: 'Data ODP tidak ditemukan.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_soreang.php';
                });
            });
        </script>";
        }
    }
    ?>
</body>

</html>