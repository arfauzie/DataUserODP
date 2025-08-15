<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once '../log_helper.php';
require_once '../koneksi_log.php';
require_once 'config3.php';
include '../navbar.php';

echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'ID ODP tidak valid!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

$id = (int)$_GET['id'];
$stmt = $pdo3->prepare("SELECT * FROM odp3 WHERE id = ?");
$stmt->execute([$id]);
$odp = $stmt->fetch();

if (!$odp) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data tidak ditemukan!',
                text: 'ODP tidak ditemukan di database.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

// Ambil semua data pon
$pon_stmt = $pdo3->query("
    SELECT * 
    FROM pon3
    ORDER BY CAST(TRIM(REPLACE(nama_pon, 'PON', '')) AS UNSIGNED)
");
$all_pons = $pon_stmt->fetchAll();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_odp = trim($_POST['nama_odp']);
    $port_max = (int)$_POST['port_max'];
    $pon_id = (int)$_POST['pon_id'];
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    $update_stmt = $pdo3->prepare("UPDATE odp3 SET nama_odp=?, pon_id=?, port_max=?, latitude=?, longitude=? WHERE id=?");
    $success = $update_stmt->execute([$nama_odp, $pon_id, $port_max, $latitude, $longitude, $id]);

    if ($success) {
        $oleh = $_SESSION['admin']['username'] ?? 'unknown';
        $log_keterangan = [];

        if ($odp['nama_odp'] !== $nama_odp) $log_keterangan[] = "Nama ODP: {$odp['nama_odp']} ➔ $nama_odp";
        if ((int)$odp['port_max'] !== $port_max) $log_keterangan[] = "Port Max: {$odp['port_max']} ➔ $port_max";
        if ((int)$odp['pon_id'] !== $pon_id) {
            $old_pon = $pdo3->prepare("SELECT nama_pon FROM pon3 WHERE id=?");
            $old_pon->execute([$odp['pon_id']]);
            $old_pon_name = $old_pon->fetchColumn();

            $new_pon = $pdo3->prepare("SELECT nama_pon FROM pon3 WHERE id=?");
            $new_pon->execute([$pon_id]);
            $new_pon_name = $new_pon->fetchColumn();

            $log_keterangan[] = "PON: $old_pon_name ➔ $new_pon_name";
        }
        if ($odp['latitude'] != $latitude) $log_keterangan[] = "Latitude: {$odp['latitude']} ➔ $latitude";
        if ($odp['longitude'] != $longitude) $log_keterangan[] = "Longitude: {$odp['longitude']} ➔ $longitude";

        if (!empty($log_keterangan)) {
            tambahRiwayat("Edit ODP", $oleh, implode("\n", $log_keterangan));
        }

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data ODP berhasil diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_soreang.php?pon_id=$pon_id';
                });
            });
        </script>";
        exit();
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menyimpan perubahan.',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit ODP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            max-width: 550px;
            width: 100%;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
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
            <h3 class="text-center mb-4">Edit Data ODP</h3>
            <form method="POST">
                <div class="mb-3">
                    <label for="pon_id" class="form-label">Pilih PON:</label>
                    <select name="pon_id" id="pon_id" class="form-control" required>
                        <?php foreach ($all_pons as $pon): ?>
                            <option value="<?= $pon['id']; ?>" <?= $pon['id'] == $odp['pon_id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($pon['nama_pon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="nama_odp" class="form-label">Nama ODP:</label>
                    <input type="text" name="nama_odp" id="nama_odp" class="form-control" value="<?= htmlspecialchars($odp['nama_odp']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="port_max" class="form-label">Port Maksimal:</label>
                    <select name="port_max" id="port_max" class="form-control" required>
                        <option value="8" <?= $odp['port_max'] == 8 ? 'selected' : ''; ?>>8 Port</option>
                        <option value="16" <?= $odp['port_max'] == 16 ? 'selected' : ''; ?>>16 Port</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="latitude" class="form-label">Latitude:</label>
                    <input type="text" name="latitude" id="latitude" class="form-control" value="<?= htmlspecialchars($odp['latitude'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="longitude" class="form-label">Longitude:</label>
                    <input type="text" name="longitude" id="longitude" class="form-control" value="<?= htmlspecialchars($odp['longitude'] ?? '') ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="olt_soreang.php?pon_id=<?= $odp['pon_id']; ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>