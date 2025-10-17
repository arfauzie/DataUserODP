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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

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

        .sidebar {
            width: 230px;
            height: 100vh;
            background-color: #fff;
            /* putih */
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            /* bayangan samping */
        }

        .sidebar img {
            width: 120px;
            display: block;
            margin: 0 auto 20px;
        }

        .sidebar a {
            color: #212529;
            /* teks gelap */
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .sidebar a:hover {
            background-color: #e7f1ff;
            /* hover biru muda */
            border-radius: 5px;
        }

        .garis-sidebar {
            height: 1px;
            background-color: #888;
            margin: 10px 20px;
            opacity: 0.5;
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: 230px;
            width: calc(100% - 230px);
            height: 60px;
            background-color: #0d6efd;
            /* bootstrap primary blue */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: white;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }

        .search-container {
            display: flex;
            align-items: center;
        }

        .search-form {
            display: flex;
            align-items: center;
            background-color: rgba(255 255 255 / 0.2);
            border-radius: 20px;
            padding: 5px 10px;
        }

        .search-form input {
            border: none;
            background: transparent;
            outline: none;
            width: 160px;
            padding: 5px;
            color: white;
        }

        /* FIX: saat fokus tetap biru */
        .search-form input:focus {
            background-color: #0d6efd;
            /* sama dengan top-bar */
            color: white;
        }

        .search-form input::placeholder,
        .search-form input::-webkit-input-placeholder,
        .search-form input:-moz-placeholder,
        .search-form input::-moz-placeholder,
        .search-form input:-ms-input-placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .search-form button {
            background: none;
            border: none;
            color: white;
        }

        .admin-container a.dropdown-toggle {
            text-decoration: none;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-container a.dropdown-toggle:hover {
            color: #cce5ff;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            background-color: #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar i {
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                height: auto;
                max-height: calc(100vh - 60px);
                bottom: 0;
                overflow-y: auto;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
                background-color: #fff;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar img {
                width: 100px;
                margin: 10px auto;
            }

            .sidebar a {
                padding: 10px 15px;
                font-size: 0.9rem;
                color: #212529;
            }

            .sidebar a:hover {
                background-color: #e7f1ff;
            }

            .top-bar {
                left: 0;
                width: 100%;
                height: 50px;
                padding: 5px 10px;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }

            .menu-button {
                margin-right: 10px;
                height: 36px;
                width: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                color: white;
            }

            .search-form {
                max-width: 180px;
            }

            .search-form input {
                width: 120px;
                height: 36px;
                font-size: 0.85rem;
                padding: 0.25rem 0.5rem;
                color: white;
            }

            /* FIX: mobile saat fokus tetap biru */
            .search-form input:focus {
                background-color: #0d6efd;
                color: white;
            }

            .search-form input::placeholder {
                color: rgba(255, 255, 255, 0.8);
            }

            .search-form button {
                height: 36px;
                width: 36px;
                padding: 0;
                color: white;
            }

            .admin-container a.dropdown-toggle {
                font-size: 0.85rem;
                color: white;
            }

            .content {
                margin-left: 0;
                padding: 70px 10px 20px;
            }
        }

        @media (max-width: 360px) {
            .sidebar {
                width: 180px;
            }

            .search-form input {
                width: 100px;
            }
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>

</head>

<body>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <img src="/DataUserODP/logo-msn.png" alt="Logo PT" />
        <div class="garis-sidebar"></div>
        <a href="/DataUserODP/dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a href="/DataUserODP/OLT_MSN/olt_msn.php"><i class="fas fa-server me-2"></i> OLT MSN</a>
        <a href="/DataUserODP/OLT_BAGONG/olt_bagong.php"><i class="fas fa-server me-2"></i> OLT Bagong</a>
        <a href="/DataUserODP/OLT_SOREANG/olt_soreang.php"><i class="fas fa-server me-2"></i> OLT Soreang</a>
        <a href="/DataUserODP/CHECK_AVG/check_avg.php"><i class="fas fa-location-dot me-3"></i>Check AVG</a>
    </div>

    <!-- TOPBAR -->
    <div class="top-bar">
        <button class="btn btn-outline-light d-md-none menu-button" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="top-bar-content">
            <div class="search-container">
                <form action="/DataUserODP/search.php" method="GET" class="search-form">
                    <input type="text" name="query" placeholder="Cari" required />
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="admin-container">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php echo htmlspecialchars($nama_lengkap); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAdmin">
                        <li><a class="dropdown-item text-danger" href="/DataUserODP/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const overlay = document.getElementById("sidebarOverlay");

            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");

            document.body.style.overflow = sidebar.classList.contains("active") ? "hidden" : "auto";
        }

        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });
    </script>
</body>

</html>