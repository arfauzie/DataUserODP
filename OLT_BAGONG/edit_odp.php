<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once '../log_helper.php';
require_once '../koneksi_log.php';
require_once 'config2.php';
include '../navbar.php';

echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

// Validasi ID
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

// Ambil data ODP berdasarkan ID
$stmt = $pdo2->prepare("SELECT * FROM odp2 WHERE id = ?");
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
                window.location = 'olt_bagong.php';
            });
        });
    </script>";
    exit();
}

// Ambil semua PON untuk dropdown dengan urutan angka dari nama_pon
$pon_stmt = $pdo2->query("SELECT * FROM pon2 ORDER BY CAST(REGEXP_SUBSTR(nama_pon, '[0-9]+') AS UNSIGNED) ASC");
$all_pons = $pon_stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses update data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_odp = trim($_POST['nama_odp']);
    $port_max = (int)$_POST['port_max'];
    $pon_id = (int)$_POST['pon_id'];

    $stmt = $pdo2->prepare("UPDATE odp2 SET nama_odp = ?, pon_id = ?, port_max = ? WHERE id = ?");
    if ($stmt->execute([$nama_odp, $pon_id, $port_max, $id])) {
        $oleh = $_SESSION['admin']['username'] ?? 'unknown';
        $log_keterangan = [];

        if ($odp['nama_odp'] !== $nama_odp) {
            $log_keterangan[] = "Nama ODP: {$odp['nama_odp']} ➔ $nama_odp";
        }

        if ((int)$odp['port_max'] !== $port_max) {
            $log_keterangan[] = "Port Max: {$odp['port_max']} ➔ $port_max";
        }

        if ((int)$odp['pon_id'] !== $pon_id) {
            $pon_lama_stmt = $pdo2->prepare("SELECT nama_pon FROM pon2 WHERE id = ?");
            $pon_lama_stmt->execute([$odp['pon_id']]);
            $nama_pon_lama = $pon_lama_stmt->fetchColumn();

            $pon_baru_stmt = $pdo2->prepare("SELECT nama_pon FROM pon2 WHERE id = ?");
            $pon_baru_stmt->execute([$pon_id]);
            $nama_pon_baru = $pon_baru_stmt->fetchColumn();

            $log_keterangan[] = "PON: $nama_pon_lama ➔ $nama_pon_baru";
        }

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
                    text: 'Gagal memperbarui data ODP.',
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
            <h2 class="mb-4 text-center">Edit ODP</h2>
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
                    <input type="text" name="nama_odp" id="nama_odp" value="<?= htmlspecialchars($odp['nama_odp']); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="port_max" class="form-label">PORT Maks:</label>
                    <select name="port_max" id="port_max" class="form-control" required>
                        <option value="8" <?= $odp['port_max'] == 8 ? 'selected' : ''; ?>>Maks 8 Port</option>
                        <option value="16" <?= $odp['port_max'] == 16 ? 'selected' : ''; ?>>Maks 16 Port</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                    <a href="olt_bagong.php?pon_id=<?= $odp['pon_id']; ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>