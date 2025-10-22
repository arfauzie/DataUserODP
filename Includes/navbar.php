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

        /* ===== TOPBAR ===== */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 54px;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-left img {
            height: 44px;
            width: auto;
            object-fit: contain;
            margin-top: -2px;
        }

        /* Search bar di kiri */
        .search-form {
            display: flex;
            align-items: center;
            background: #f1f3f5;
            border-radius: 5px;
            padding: 3px 8px;
            border: 1px solid #dee2e6;
            margin-left: 10px;
        }

        .search-form input {
            border: none;
            outline: none;
            background: transparent;
            width: 150px;
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

        /* Tombol menu sidebar */
        .menu-button {
            border: none;
            background: transparent;
            font-size: 1.3rem;
            color: #212529;
            cursor: pointer;
            flex-shrink: 0;
            padding: 2px;
        }

        .admin-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-toggle {
            color: #212529;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            max-width: 180px;
        }

        /* Nama admin full tanpa ... */
        .admin-name {
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            text-overflow: unset;
            max-width: 100%;
            font-size: 0.9rem;
            flex-shrink: 1;
            scrollbar-width: none;
        }

        .admin-name::-webkit-scrollbar {
            display: none;
        }

        /* Avatar admin */
        .profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #495057;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 230px;
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
            z-index: 999;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #212529;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #e7f1ff;
        }

        .content {
            margin-left: 230px;
            padding: 80px 24px;
        }

        /* ===== MOBILE (JANGAN DIUBAH) ===== */
        @media (max-width: 768px) {
            .search-form {
                display: none !important;
            }

            .sidebar {
                width: 100%;
                height: auto;
                top: 10px;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: 0.3s;
            }

            .sidebar.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .sidebar a {
                border-bottom: 1px solid #f1f1f1;
            }

            .sidebar .search-mobile {
                padding: 8px 14px;
                border-bottom: 1px solid #f1f1f1;
            }

            .sidebar .search-mobile input {
                width: 100%;
                padding: 7px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 0.9rem;
            }

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
                height: 42px;
            }

            .top-bar {
                height: 50px;
                padding: 0 8px;
            }

            .dropdown-toggle {
                gap: 3px;
            }

            .menu-button {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 375px) {
            .topbar-left img {
                height: 40px;
            }

            .admin-name {
                font-size: 0.83rem;
            }
        }

        @media (max-width: 320px) {
            .topbar-left img {
                height: 36px;
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
        <div class="topbar-left">
            <img src="/DataUserODP/logo-msn.png" alt="Logo">

            <!-- Search desktop pindah ke kiri -->
            <form action="/DataUserODP/search.php" method="GET" class="search-form d-none d-md-flex">
                <input type="text" name="query" placeholder="Cari..." required />
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="admin-container">
            <!-- Dropdown -->
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

            <!-- Tombol menu -->
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

        <a href="/DataUserODP/dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="/DataUserODP/OLT_MSN/olt_msn.php"><i class="fas fa-server me-2"></i>OLT MSN</a>
        <a href="/DataUserODP/OLT_BAGONG/olt_bagong.php"><i class="fas fa-server me-2"></i>OLT Bagong</a>
        <a href="/DataUserODP/OLT_SOREANG/olt_soreang.php"><i class="fas fa-server me-2"></i>OLT Soreang</a>
        <a href="/DataUserODP/CHECK_COVERAGE/check_coverage.php"><i class="fas fa-location-dot me-2"></i>Check Coverage</a>
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