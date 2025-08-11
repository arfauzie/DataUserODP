<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}
include 'config3.php';
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
    // Ambil dan sanitasi parameter
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $odp_id = isset($_GET['odp_id']) ? htmlspecialchars($_GET['odp_id']) : '';
    $pon_id = isset($_GET['pon_id']) ? htmlspecialchars($_GET['pon_id']) : '';

    if ($id && $odp_id && $pon_id) {
        $stmt = $pdo3->prepare("DELETE FROM users3 WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Berhasil hapus
            echo "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'User berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location = 'olt_soreang.php?odp_id={$odp_id}&pon_id={$pon_id}';
            });
        });
        </script>";
        } else {
            // Gagal hapus
            echo "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus user!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location = 'olt_soreang.php?odp_id={$odp_id}&pon_id={$pon_id}';
            });
        });
        </script>";
        }
    } else {
        // Parameter tidak lengkap
        echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'ID tidak valid!',
            text: 'Permintaan tidak dapat diproses.',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location = 'olt_soreang.php?odp_id={$odp_id}&pon_id={$pon_id}';
        });
    });
    </script>";
    }
    ?>
</body>

</html>