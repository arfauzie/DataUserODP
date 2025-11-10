<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nama_lengkap = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="logo-msn2.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f5f7;
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: linear-gradient(to right, #0d60dc 230px, #0d6efd 230px);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-left img {
            height: 37px;
            width: auto;
            object-fit: contain;
            margin-left: 8px;
            position: relative;
            top: 1px;
        }

        .search-form {
            display: flex;
            align-items: center;
            background: #f1f3f5;
            border-radius: 6px;
            padding: 4px 10px;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
            margin-right: 16px;
        }

        .search-form input {
            border: none;
            outline: none;
            background: transparent;
            width: 180px;
            font-size: 0.9rem;
        }

        .search-form button {
            background: none;
            border: none;
            color: #495057;
            cursor: pointer;
        }

        .search-form button:hover {
            color: #0d6efd;
        }

        .admin-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-toggle {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 200px;
        }

        .admin-name {
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            text-overflow: unset;
            max-width: 100%;
            font-size: 0.9rem;
            scrollbar-width: none;
        }

        .admin-name::-webkit-scrollbar {
            display: none;
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.25);
            color: #fff;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* MENU BUTTON */
        .menu-button {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 6px;
        }

        .menu-button:focus {
            outline: none;
        }

        /* SIDEBAR DESKTOP */
        .sidebar {
            width: 230px;
            background-color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.25);
            transition: 0.3s;
            z-index: 999;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #000;
            text-decoration: none;
            font-size: 0.95rem;
            transition: 0.2s;
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
            color: #000;
        }

        .sidebar a:hover {
            background-color: #1e282c;
            color: #fff;
        }

        .sidebar a:hover i {
            color: #fff;
        }

        /* KONTEN */
        .content {
            margin-left: 230px;
            padding: 85px 24px;
        }

        /* ========================================
   RESPONSIVE FIX (MOBILE RAPAT DAN DEKAT TOPBAR)
=========================================*/
        @media (max-width: 768px) {

            /* Topbar biru solid */
            .top-bar {
                background-color: #0d6efd !important;
            }

            /* Tombol hamburger putih tanpa background */
            .menu-button {
                color: #fff !important;
                background: none !important;
                border: none;
                font-size: 1.6rem;
                padding: 6px;
            }

            /* Sidebar putih, muncul menempel tepat di bawah topbar */
            .sidebar {
                width: 100%;
                height: auto;
                top: 0;
                background: #ffffff !important;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                border-top: 1px solid #dee2e6;
                padding-top: 0;
            }

            /* Posisi aktif — menempel lebih dekat ke bawah topbar */
            .sidebar.active {
                transform: translateY(54px);
                /* turun sejajar tinggi topbar */
                opacity: 1;
                visibility: visible;
            }

            /* Search bar — rapat ke top */
            .search-mobile {
                padding: 6px 10px;
                background: #fff;
                text-align: center;
                border-bottom: 1px solid #dee2e6;
                margin-top: 0;
                /* hilangkan jarak ke atas */
            }

            .search-mobile input {
                width: 88%;
                border-radius: 6px;
                border: 1px solid #ced4da;
                padding: 6px 9px;
                font-size: 0.88rem;
                outline: none;
                background: #fff;
                color: #000;
                text-align: left;
            }

            .sidebar a {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px 20px;
                color: #000 !important;
                border-bottom: 1px solid #f1f1f1;
                font-size: 0.92rem;
                line-height: 1.2;
                background: #fff;
            }

            .sidebar a i {
                color: #000000ff !important;
                font-size: 1rem;
            }

            .sidebar a:hover {
                background: #f1f5ff;
                color: #0d6efd;
            }

            /* Hilangkan search desktop */
            .search-form {
                display: none !important;
            }

            /* Konten */
            .content {
                margin-left: 0;
                padding: 70px 14px;
            }

            .profile-avatar {
                display: none;
            }

            .admin-name {
                font-size: 0.88rem;
            }

            .topbar-left img {
                height: 34px;
            }

            .top-bar {
                height: 54px;
                padding: 0 10px;
            }
        }

        @media (max-width: 375px) {
            .topbar-left img {
                height: 32px;
            }

            .admin-name {
                font-size: 0.83rem;
            }
        }

        @media (max-width: 320px) {
            .topbar-left img {
                height: 30px;
            }

            .admin-name {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <!-- TOPBAR -->
    <div class="top-bar">
        <!-- KIRI: LOGO -->
        <div class="topbar-left">
            <img src="/DataUserODP/logo-msn3.png" alt="Logo">
        </div>

        <!-- KANAN: SEARCH + ADMIN -->
        <div class="admin-container">
            <form action="/DataUserODP/search.php" method="GET" class="search-form d-none d-md-flex">
                <input type="text" name="query" placeholder="Cari..." required />
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <div class="dropdown">
                <a href="#" class="dropdown-toggle" id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                    <span class="admin-name"><?php echo htmlspecialchars($nama_lengkap); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item text-danger" href="/DataUserODP/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tombol menu (mobile) -->
            <button class="menu-button d-md-none" id="menuToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="search-mobile d-md-none">
            <form action="/DataUserODP/search.php" method="GET">
                <input type="text" name="query" placeholder="Cari..." required />
            </form>
        </div>

        <a href="/DataUserODP/dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
        <a href="/DataUserODP/OLT_MSN/olt_msn.php"><i class="fas fa-server"></i>OLT MSN</a>
        <a href="/DataUserODP/OLT_BAGONG/olt_bagong.php"><i class="fas fa-server"></i>OLT Bagong</a>
        <a href="/DataUserODP/OLT_SOREANG/olt_soreang.php"><i class="fas fa-server"></i>OLT Soreang</a>
        <a href="/DataUserODP/CHECK_COVERAGE/check_coverage.php"><i class="fas fa-location-dot"></i>Check Coverage</a>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("active");
        }

        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.getElementById("sidebar").classList.remove("active");
                }
            });
        });
    </script>
</body>

</html>