<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

include 'config3.php';
include '../navbar.php';

// SweetAlert
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

$id = $_GET['id'] ?? null;
$odp_id = $_GET['odp_id'] ?? null;
$pon_id = $_GET['pon_id'] ?? null;

if (!$id || !$odp_id || !$pon_id) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Parameter tidak lengkap!',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

// Ambil data user
$stmt = $pdo3->prepare("SELECT * FROM users3 WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data tidak ditemukan!',
                text: 'User tidak ditemukan di database.',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = 'olt_soreang.php?pon_id=$pon_id&odp_id=$odp_id';
            });
        });
    </script>";
    exit();
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_user = $_POST['nama_user'] ?? '';
    $nomor_internet = $_POST['nomor_internet'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    // Validasi sederhana
    if ($nama_user && $nomor_internet && $alamat) {
        $stmt = $pdo3->prepare("UPDATE users3 SET nama_user = ?, nomor_internet = ?, alamat = ? WHERE id = ?");
        if ($stmt->execute([$nama_user, $nomor_internet, $alamat, $id])) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data user berhasil diperbarui.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'olt_soreang.php?pon_id=$pon_id&odp_id=$odp_id';
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
                        text: 'Gagal memperbarui data user.',
                        showConfirmButton: true,
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum lengkap!',
                    text: 'Semua field wajib diisi.',
                    showConfirmButton: true,
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
    <title>Edit User</title>
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
            <h2 class="mb-4 text-center">Edit User</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama User:</label>
                    <input type="text" name="nama_user" value="<?= htmlspecialchars($user['nama_user']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor Internet:</label>
                    <input type="text" name="nomor_internet" value="<?= htmlspecialchars($user['nomor_internet']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat:</label>
                    <input type="text" name="alamat" value="<?= htmlspecialchars($user['alamat']) ?>" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                    <a href="olt_soreang.php?pon_id=<?= $pon_id ?>&odp_id=<?= $odp_id ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>