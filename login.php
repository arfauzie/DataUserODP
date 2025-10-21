<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/Includes/config.php';

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
        $stmt = $pdo->prepare("SELECT * FROM login_user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

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
        /* ====== GLOBAL STYLE ====== */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #009ffd, #2a2a72);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        /* ====== LOGIN BOX ====== */
        .login-box {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            transition: 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.25);
        }

        .login-box img {
            width: 90px;
            margin-bottom: 20px;
        }

        .login-box h2 {
            color: #2a2a72;
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 26px;
        }

        /* ====== FORM INPUT ====== */
        .input-group-text {
            background-color: #f0f3f7;
            border: none;
            border-radius: 10px 0 0 10px;
            color: #007bff;
            font-size: 16px;
        }

        .form-control {
            border: none;
            border-radius: 0 10px 10px 0;
            background-color: #f0f3f7;
            padding: 12px 15px;
            font-size: 15px;
        }

        .form-control:focus {
            box-shadow: none;
            background-color: #eaf3ff;
            border-left: 3px solid #007bff;
        }

        /* ====== BUTTON ====== */
        .btn-login {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            margin-top: 15px;
            width: 100%;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(90deg, #005ecb, #0099dd);
            transform: scale(1.02);
        }

        /* ====== ALERT ====== */
        .alert {
            font-size: 14px;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 768px) {
            .login-box {
                padding: 35px 25px;
                max-width: 90%;
            }

            .login-box h2 {
                font-size: 22px;
            }

            .btn-login {
                padding: 10px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 25px 20px;
                border-radius: 15px;
            }

            .login-box h2 {
                font-size: 20px;
            }

            .login-box img {
                width: 75px;
                margin-bottom: 15px;
            }

            .btn-login {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="login-box">
        <img src="/DataUserODP/logo-msn2.png" alt="Logo">
        <h2>Login</h2>

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
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
    </div>
</body>

</html>