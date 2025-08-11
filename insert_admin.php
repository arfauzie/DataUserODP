<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
$host = "localhost";
$dbname = "msn_db";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div class='alert alert-danger text-center'>Koneksi database gagal: " . $e->getMessage() . "</div>");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($nama_lengkap) && !empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            $message = "<div class='alert alert-danger mt-3'>Username sudah digunakan! Gunakan yang lain.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO admin (nama_lengkap, username, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$nama_lengkap, $username, $hashed_password])) {
                $message = "<div class='alert alert-success mt-3'>Admin berhasil ditambahkan!</div>";
            } else {
                $message = "<div class='alert alert-danger mt-3'>Gagal menambahkan admin.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning mt-3'>Semua kolom harus diisi!</div>";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('bg-login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .card {
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            max-width: 450px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }

        .form-label {
            font-weight: bold;
            color: #333;
        }

        @media (max-width: 576px) {
            .card {
                padding: 20px;
            }
        }

        .btn-success {
            background: linear-gradient(to right, #00625f, #009688);
            transition: all 0.3s;
        }

        .btn-success:hover {
            background: linear-gradient(to right, #004d47, #00796b);
            transform: scale(1.02);
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100" style="background-color: #f8f9fa;">
    <div class="card">
        <h3 class="text-center"><b>Tambah Admin</b></h3>
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
            <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-user-plus"></i> Tambah Admin
            </button>
        </form>
    </div>
</body>

</html>