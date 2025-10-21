<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// Koneksi Database
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
// Variabel notifikasi
// ==========================
$message = "";

// ==========================
// Proses form tambah admin/user
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = trim($_POST['password']);
    $role         = trim($_POST['role']);

    // Validasi input
    if (!empty($nama_lengkap) && !empty($username) && !empty($password) && !empty($role)) {
        // Pastikan role valid
        if (!in_array($role, ['admin', 'user'])) {
            $message = "<div class='alert alert-warning text-center mt-3'>Role tidak valid!</div>";
        } else {
            // Cek username sudah ada atau belum
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_user WHERE username = ?");
            $stmt->execute([$username]);
            $userExists = $stmt->fetchColumn();

            if ($userExists) {
                $message = "<div class='alert alert-danger text-center mt-3'>Username sudah digunakan!</div>";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert ke tabel login_user
                $stmt = $pdo->prepare("INSERT INTO login_user (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nama_lengkap, $username, $hashed_password, $role])) {
                    $message = "<div class='alert alert-success text-center mt-3'>User berhasil ditambahkan!</div>";
                } else {
                    $message = "<div class='alert alert-danger text-center mt-3'>Gagal menambahkan user.</div>";
                }
            }
        }
    } else {
        $message = "<div class='alert alert-warning text-center mt-3'>Semua kolom wajib diisi!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah User/Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #007bff, #03d5ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .card {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 10px 12px;
        }

        .btn-submit {
            border-radius: 10px;
            width: 100%;
            background-color: #007bff;
            color: white;
            font-weight: 600;
            padding: 10px;
            margin-top: 10px;
            border: none;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .text-center a {
            text-decoration: none;
            color: #007bff;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .card {
                padding: 20px;
            }

            .form-control,
            .form-select {
                font-size: 14px;
            }

            .btn-submit {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <h3 class="text-center mb-3">Tambah User/Admin</h3>
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
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Tambah User
            </button>

            <div class="text-center mt-3">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        </form>
    </div>
</body>

</html>