<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'log_helper.php';
require_once 'config2.php';
include '../Includes/navbar.php';

echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

// Validasi ID ODP
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
                window.location = 'olt_bagong.php';
            });
        });
    </script>";
    exit();
}

$id = (int)$_GET['id'];

// Ambil data ODP
$stmt = $pdo2->prepare("SELECT * FROM odp2 WHERE id = ?");
$stmt->execute([$id]);
$odp = $stmt->fetch(PDO::FETCH_ASSOC);

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
                window.location = 'olt_bagong.php';
            });
        });
    </script>";
    exit();
}

// Ambil semua data PON
$pon_stmt = $pdo2->query("
    SELECT * 
    FROM pon2 
    ORDER BY CAST(TRIM(REPLACE(nama_pon, 'PON', '')) AS UNSIGNED)
");
$all_pons = $pon_stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_odp = trim($_POST['nama_odp']);
    $port_max = (int)$_POST['port_max'];
    $pon_id   = (int)$_POST['pon_id'];
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    $update_stmt = $pdo2->prepare("UPDATE odp2 SET nama_odp=?, pon_id=?, port_max=?, latitude=?, longitude=? WHERE id=?");
    $success = $update_stmt->execute([$nama_odp, $pon_id, $port_max, $latitude, $longitude, $id]);

    if ($success) {
        // Ambil role (string/array)
        $oleh = is_array($_SESSION['role'])
            ? ($_SESSION['role']['username'] ?? 'role')
            : $_SESSION['role'];

        $log_keterangan = [];

        // cek perubahan nama
        if ($odp['nama_odp'] !== $nama_odp) {
            $log_keterangan[] = "Nama ODP: {$odp['nama_odp']} ➔ $nama_odp";
        }

        // cek perubahan port
        if ((int)$odp['port_max'] !== $port_max) {
            $log_keterangan[] = "Port Max: {$odp['port_max']} ➔ $port_max";
        }

        // cek perubahan PON
        if ((int)$odp['pon_id'] !== $pon_id) {
            $old_pon = $pdo2->prepare("SELECT nama_pon FROM pon2 WHERE id=?");
            $old_pon->execute([$odp['pon_id']]);
            $old_pon_name = $old_pon->fetchColumn() ?: '(kosong)';

            $new_pon = $pdo2->prepare("SELECT nama_pon FROM pon2 WHERE id=?");
            $new_pon->execute([$pon_id]);
            $new_pon_name = $new_pon->fetchColumn() ?: '(kosong)';

            $log_keterangan[] = "PON: $old_pon_name ➔ $new_pon_name";
        }

        // cek perubahan latitude
        if ($odp['latitude'] != $latitude) {
            $log_keterangan[] = "Latitude: {$odp['latitude']} ➔ $latitude";
        }

        // cek perubahan longitude
        if ($odp['longitude'] != $longitude) {
            $log_keterangan[] = "Longitude: {$odp['longitude']} ➔ $longitude";
        }

        // header log
        $log_header = "Edit ODP ({$odp['nama_odp']})";

        // simpan log
        $log_text = !empty($log_keterangan) ? implode("\n", $log_keterangan) : "Tidak ada perubahan data";

        if (function_exists('tambahRiwayatBagong')) {
            tambahRiwayatBagong($pdo2, $log_header, $oleh, $log_text);
        } else {
            tambahRiwayatBagong($pdo2, $log_header, $oleh, $log_text);
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
                    window.location = 'olt_bagong.php?pon_id=$pon_id';
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
                    <input type="text" name="nama_odp" id="nama_odp" class="form-control"
                        value="<?= htmlspecialchars($odp['nama_odp']); ?>" required>
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
                    <input type="text" name="latitude" id="latitude" class="form-control"
                        value="<?= htmlspecialchars($odp['latitude'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="longitude" class="form-label">Longitude:</label>
                    <input type="text" name="longitude" id="longitude" class="form-control"
                        value="<?= htmlspecialchars($odp['longitude'] ?? '') ?>">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="olt_bagong.php?pon_id=<?= $odp['pon_id']; ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>