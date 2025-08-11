<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once '../log_helper.php';
require_once 'config.php';
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
                window.location.href = 'olt_msn.php';
            });
        });
    </script>";
    exit();
}

// Ambil data user lama
$stmt = $pdo->prepare("SELECT * FROM users1 WHERE id = ?");
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
                window.location.href = 'olt_msn.php?pon_id=$pon_id&odp_id=$odp_id';
            });
        });
    </script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_user = trim($_POST['nama_user'] ?? '');
    $nomor_internet = trim($_POST['nomor_internet'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama_user && $nomor_internet && $alamat) {
        $stmt = $pdo->prepare("UPDATE users1 SET nama_user = ?, nomor_internet = ?, alamat = ? WHERE id = ?");
        if ($stmt->execute([$nama_user, $nomor_internet, $alamat, $id])) {
            $oleh = $_SESSION['admin']['username'] ?? 'unknown';
            $log_parts = [];

            if ($user['nama_user'] !== $nama_user) {
                $log_parts[] = "Nama: {$user['nama_user']} → $nama_user";
            }
            if ($user['nomor_internet'] !== $nomor_internet) {
                $log_parts[] = "Nomor Internet: {$user['nomor_internet']} → $nomor_internet";
            }
            if ($user['alamat'] !== $alamat) {
                $log_parts[] = "Alamat: {$user['alamat']} → $alamat";
            }

            if (!empty($log_parts)) {
                $keterangan = "Edit User\n" . implode("\n", $log_parts);
                tambahRiwayat("Edit User", $oleh, $keterangan);
            }

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data user berhasil diperbarui.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'olt_msn.php?pon_id=$pon_id&odp_id=$odp_id';
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
                        showConfirmButton: true
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
                    showConfirmButton: true
                });
            });
        </script>";
    }
}
?>
<!-- HTML Form (tidak perlu diubah dari sebelumnya) -->
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
                    <a href="olt_msn.php?pon_id=<?= $pon_id ?>&odp_id=<?= $odp_id ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>