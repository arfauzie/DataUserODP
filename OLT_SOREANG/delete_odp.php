<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php';
require_once 'log_helper.php'; // helper baru khusus OLT_MSN
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hapus ODP</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $odp_id = (int) $_GET['id'];

        // Ambil data ODP sebelum dihapus
        $stmt = $pdo3->prepare("SELECT * FROM odp3 WHERE id = ?");
        $stmt->execute([$odp_id]);
        $odp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($odp) {
            $pon_id   = $odp['pon_id'];
            $nama_odp = $odp['nama_odp'] ?? '(kosong)';
            $port_odp = $odp['port_max'] ?? '(tidak tersedia)';

            // Siapkan keterangan log
            $log_keterangan = "Nama ODP: $nama_odp | Port Maksimum: $port_odp";

            // Ambil nama admin
            $oleh = is_array($_SESSION['admin'])
                ? ($_SESSION['admin']['username'] ?? 'admin')
                : $_SESSION['admin'];

            // Hapus ODP
            $stmt = $pdo3->prepare("DELETE FROM odp3 WHERE id = ?");
            if ($stmt->execute([$odp_id])) {
                // Tambahkan log dengan helper baru (fallback ke lama jika masih ada)
                if (function_exists('tambahRiwayatSoreang')) {
                    tambahRiwayatSoreang($pdo3, "Hapus ODP", $oleh, $log_keterangan);
                } else {
                    tambahRiwayatSoreang($pdo3, "Hapus ODP", $oleh, $log_keterangan);
                }

                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'ODP berhasil dihapus!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { window.location = 'olt_soreang.php?pon_id=$pon_id'; });
                </script>";
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menghapus ODP!',
                    confirmButtonText: 'OK'
                }).then(() => { window.location = 'olt_soreang.php?pon_id=$pon_id'; });
                </script>";
            }
        } else {
            echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Tidak ditemukan!',
                text: 'Data ODP tidak ditemukan.',
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
            text: 'ID ODP tidak valid.',
            timer: 2000,
            showConfirmButton: false
        }).then(() => { window.location = 'olt_soreang.php'; });
        </script>";
    }
    ?>
</body>

</html>