<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}
require_once 'config3.php';
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

        // Ambil pon_id sebelum menghapus
        $stmt = $pdo3->prepare("SELECT pon_id FROM odp3 WHERE id = ?");
        $stmt->execute([$odp_id]);
        $odp = $stmt->fetch();

        if ($odp) {
            $pon_id = $odp['pon_id'];

            // Hapus ODP
            $stmt = $pdo3->prepare("DELETE FROM odp3 WHERE id = ?");
            if ($stmt->execute([$odp_id])) {
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
        }
    }
    ?>
</body>

</html>