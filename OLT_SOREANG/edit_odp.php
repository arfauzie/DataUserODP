<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php';
include '../navbar.php';

echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

// Validasi ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
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

$id = $_GET['id'];

// Ambil data ODP berdasarkan ID
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

// Ambil semua PON untuk dropdown dengan urutan angka dari nama_pon
$pon_stmt = $pdo3->query("
    SELECT * FROM pon3
    ORDER BY CAST(REGEXP_SUBSTR(nama_pon, '[0-9]+') AS UNSIGNED) ASC
");
$all_pons = $pon_stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_odp = $_POST['nama_odp'];
    $port_max = $_POST['port_max'];
    $pon_id = $_POST['pon_id'];

    $stmt = $pdo3->prepare("UPDATE odp3 SET nama_odp = ?, pon_id = ?, port_max = ? WHERE id = ?");
    if ($stmt->execute([$nama_odp, $pon_id, $port_max, $id])) {
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
                    <a href="olt_soreang.php?pon_id=<?= $odp['pon_id']; ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>