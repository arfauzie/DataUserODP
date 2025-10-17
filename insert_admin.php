<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// Koneksi ke Database
// ==========================
$host = "localhost";
$dbname = "msn_db";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center mt-3'>
        <b>Koneksi database gagal:</b> " . $e->getMessage() . "
    </div>");
}

// ==========================
// Variabel untuk notifikasi
// ==========================
$message = "";

// ==========================
// Proses form tambah admin
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = trim($_POST['password']);
    $role         = trim($_POST['role']);

    // Validasi input
    if (!empty($nama_lengkap) && !empty($username) && !empty($password) && !empty($role)) {
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            $message = "<div class='alert alert-danger mt-3 text-center'>
                <i class='fas fa-exclamation-circle'></i> Username sudah digunakan!
            </div>";
        } else {
            // Enkripsi password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Masukkan data baru ke tabel admin
            $stmt = $pdo->prepare("INSERT INTO admin (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nama_lengkap, $username, $hashed_password, $role])) {
                $message = "<div class='alert alert-success mt-3 text-center'>
                    <i class='fas fa-check-circle'></i> Admin berhasil ditambahkan!
                </div>";
            } else {
                $message = "<div class='alert alert-danger mt-3 text-center'>
                    <i class='fas fa-times-circle'></i> Gagal menambahkan admin.
                </div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning mt-3 text-center'>
            <i class='fas fa-exclamation-triangle'></i> Semua kolom wajib diisi!
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('bg-login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.96);
            max-width: 480px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .btn-success {
            background: linear-gradient(to right, #00625f, #009688);
            border: none;
            transition: all 0.3s;
        }

        .btn-success:hover {
            background: linear-gradient(to right, #004d47, #00796b);
            transform: scale(1.03);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .card {
                padding: 25px;
            }
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100" style="background-color: #f8f9fa;">
    <div class="card">
        <h3 class="text-center mb-3"><b>Tambah Admin Baru</b></h3>
        <hr>
        <?php echo $message; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                    <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="teknisi">Teknisi</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-user-plus"></i> Tambah Admin
            </button>

            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
