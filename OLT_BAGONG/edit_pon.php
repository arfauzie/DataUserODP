<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config2.php';          // koneksi ke database $pdo2
require_once 'log_helper.php';      // untuk tambahRiwayat
include '../navbar.php';

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = "ID tidak valid.";
} else {
    $id = (int)$_GET['id'];
    $stmt = $pdo2->prepare("SELECT * FROM pon2 WHERE id = ?");
    $stmt->execute([$id]);
    $pon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pon) {
        $error = "Data tidak ditemukan.";
    }
}

// Proses update
$successMessage = $infoMessage = $errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($pon)) {
    $nama_pon = trim($_POST['nama_pon']);
    $port_max = (int)$_POST['port_max'];

    $perubahan = [];

    // Bandingkan field untuk log
    if ($nama_pon !== $pon['nama_pon']) {
        $perubahan[] = "Nama PON: '{$pon['nama_pon']}' ➝ '{$nama_pon}'";
    }
    if ($port_max != $pon['port_max']) {
        $perubahan[] = "Port Maksimum: '{$pon['port_max']}' ➝ '{$port_max}'";
    }

    if (!empty($perubahan)) {
        $stmt = $pdo2->prepare("UPDATE pon2 SET nama_pon = ?, port_max = ? WHERE id = ?");
        $stmt->execute([$nama_pon, $port_max, $id]);

        // Ambil nama admin
        $oleh = is_array($_SESSION['admin']) ? ($_SESSION['admin']['username'] ?? 'admin') : $_SESSION['admin'];

        // Simpan log menggunakan $pdo2 sebagai parameter pertama
        $log_keterangan = implode(" | ", $perubahan);
        tambahRiwayat($pdo2, "Edit PON", $oleh, $log_keterangan);

        $successMessage = "Data berhasil diperbarui!";
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{$successMessage}',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'olt_bagong.php';
                });
            };
        </script>";
    } else {
        $infoMessage = "Tidak ada perubahan data.";
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: '{$infoMessage}',
                    timer: 2000,
                    showConfirmButton: false
                });
            };
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit PON</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 260px;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
        }

        .btn {
            min-width: 100px;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="card-box">
            <h2 class="mb-4 text-center">Edit PON</h2>
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif (isset($pon)) : ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama PON:</label>
                        <input type="text" name="nama_pon" value="<?= htmlspecialchars($pon['nama_pon']); ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PORT Maks:</label>
                        <select name="port_max" class="form-control" required>
                            <option value="2" <?= ($pon['port_max'] == 2 ? 'selected' : '') ?>>Maks 2 Port</option>
                            <option value="4" <?= ($pon['port_max'] == 4 ? 'selected' : '') ?>>Maks 4 Port</option>
                            <option value="8" <?= ($pon['port_max'] == 8 ? 'selected' : '') ?>>Maks 8 Port</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="olt_bagong.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>