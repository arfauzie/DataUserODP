<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Belum login, arahkan ke halaman login
    header("Location: /DataUserODP/login.php");
    exit();
}

// Fungsi untuk membatasi akses berdasarkan role
function checkAccess($allowedRoles = [])
{
    if (!isset($_SESSION['role'])) {
        header("Location: /DataUserODP/login.php");
        exit();
    }

    // Jika role user tidak ada dalam daftar yang diizinkan
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // Redirect ke halaman sesuai rolenya
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: /DataUserODP/dashboard.php");
                break;
            case 'user':
                header("Location: /DataUserODP/dashboard_user.php");
                break;
            default:
                header("Location: /DataUserODP/login.php");
        }
        exit();
    }
}
