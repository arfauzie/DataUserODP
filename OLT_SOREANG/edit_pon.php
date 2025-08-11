<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once 'config3.php';
include '../navbar.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ID tidak valid!',
                text: 'Tidak ada ID yang diberikan.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

$stmt = $pdo3->prepare("SELECT * FROM pon3 WHERE id = ?");
$stmt->execute([$id]);
$pon = $stmt->fetch();

if (!$pon) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data tidak ditemukan!',
                text: 'PON tidak ditemukan di database.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_pon = $_POST['nama_pon'];
    $port_max = $_POST['port_max'];

    $stmt = $pdo3->prepare("UPDATE pon3 SET nama_pon = ?, port_max = ? WHERE id = ?");
    if ($stmt->execute([$nama_pon, $port_max, $id])) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data PON berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'olt_soreang.php';
                });
            });
        </script>";
        exit();
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal memperbarui data PON.',
                    showConfirmButton: false,
                    timer: 1500
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
    <title>Edit PON</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 260px;
            /* sesuai sidebar */
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
            <h4 class="mb-4 text-center">Edit PON</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="nama_pon" class="form-label">Nama PON:</label>
                    <input type="text" name="nama_pon" id="nama_pon" value="<?= htmlspecialchars($pon['nama_pon']) ?>" class="form-control border border-primary" required>
                </div>
                <div class="mb-4">
                    <label for="port_max" class="form-label">PORT:</label>
                    <select name="port_max" id="port_max" class="form-control border border-primary" required>
                        <option value="2" <?= $pon['port_max'] == 2 ? 'selected' : '' ?>>Maks 2 Port</option>
                        <option value="4" <?= $pon['port_max'] == 4 ? 'selected' : '' ?>>Maks 4 Port</option>
                        <option value="8" <?= $pon['port_max'] == 8 ? 'selected' : '' ?>>Maks 8 Port</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                    <a href="olt_soreang.php" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>