<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config2.php';
require_once 'log_helper.php'; // helper baru yang menerima $pdo2 sebagai parameter
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
        $odp_id = (int)$_GET['id'];

        // Ambil data ODP sebelum dihapus
        $stmt = $pdo2->prepare("SELECT * FROM odp2 WHERE id = ?");
        $stmt->execute([$odp_id]);
        $odp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($odp) {
            $pon_id   = $odp['pon_id'];
            $nama_odp = $odp['nama_odp'];
            $port_odp = $odp['port_max'];

            $log_keterangan = "Hapus ODP: Nama ODP = $nama_odp | Port Maksimum = $port_odp | PON ID = $pon_id";

            $oleh = $_SESSION['admin']['username'] ?? 'admin';

            // Hapus ODP
            $stmt = $pdo2->prepare("DELETE FROM odp2 WHERE id = ?");
            if ($stmt->execute([$odp_id])) {
                // Tambahkan log menggunakan helper baru
                tambahRiwayat($pdo2, "Hapus ODP", $oleh, $log_keterangan);

                echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'ODP berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => { window.location = 'olt_bagong.php?pon_id=$pon_id'; });
            </script>";
            } else {
                echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus ODP!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => { window.location = 'olt_bagong.php?pon_id=$pon_id'; });
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
        }).then(() => { window.location = 'olt_bagong.php'; });
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
    }).then(() => { window.location = 'olt_bagong.php'; });
    </script>";
    }
    ?>
</body>

</html>