<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gunakan koneksi global (bukan dari OLT)
include __DIR__ . '/Includes/config.php';

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /DataUserODP/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'user') {
        header("Location: /DataUserODP/dashboard_user.php");
        exit();
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Ambil user dari tabel login_user
        $stmt = $pdo->prepare("SELECT * FROM login_user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Simpan session umum
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role']; // admin / user

            // Arahkan sesuai role
            if ($user['role'] === 'admin') {
                header("Location: /DataUserODP/dashboard.php");
            } elseif ($user['role'] === 'user') {
                header("Location: /DataUserODP/dashboard_user.php");
            }
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Harap isi semua kolom!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - Data User ODP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/DataUserODP/asset/logo-msn2.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ================= Desktop ================= */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #007bff, #03d5ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* agar mobile tidak menempel tepi */
        }

        .login-box {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .login-box img {
            width: 100px;
            margin-bottom: 20px;
        }

        .login-box h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #007bff;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
        }

        .btn-login {
            border-radius: 10px;
            background-color: #007bff;
            color: white;
            font-weight: 600;
            padding: 12px;
            margin-top: 10px;
            width: 100%;
            border: none;
            outline: none;
            box-shadow: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-login:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-login:focus,
        .btn-login:active {
            outline: none !important;
            box-shadow: none !important;
            background-color: #00408f;
        }

        .alert {
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* ================= Responsif Tablet ================= */
        @media (max-width: 768px) {
            .login-box {
                padding: 30px 20px;
                max-width: 90%;
            }

            .form-control {
                padding: 10px 12px;
            }

            .btn-login {
                padding: 10px;
                font-size: 14px;
            }

            .login-box h2 {
                font-size: 20px;
            }

            .login-box img {
                width: 90px;
            }
        }

        /* ================= Responsif Mobile Kecil ================= */
        @media (max-width: 480px) {
            .login-box {
                padding: 20px 15px;
                max-width: 95%;
            }

            .login-box img {
                width: 80px;
                margin-bottom: 15px;
            }

            .login-box h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .form-control {
                padding: 8px 10px;
                font-size: 14px;
            }

            .btn-login {
                padding: 8px;
                font-size: 14px;
            }

            .alert {
                font-size: 13px;
                padding: 8px;
            }
        }
    </style>

</head>

<body>
    <div class="login-box">
        <img src="/DataUserODP/logo-msn2.png" alt="Logo">
        <h2>LOGIN</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
</body>

</html>