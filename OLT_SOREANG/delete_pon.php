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
    <title>Hapus PON</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Cek apakah ada ODP terkait
        $stmt = $pdo3->prepare("SELECT COUNT(*) FROM odp3 WHERE pon_id = ?");
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
                    window.location = 'olt_soreang.php';
                });
            });
        </script>";
            exit();
        }

        // Hapus PON
        $stmt = $pdo3->prepare("DELETE FROM pon3 WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'PON berhasil dihapus!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_soreang.php';
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
                    window.location = 'olt_soreang.php';
                });
            });
        </script>";
        }
    }
    ?>
</body>

</html>