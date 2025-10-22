<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$search_nomor = isset($_GET['search_nomor']) ? $_GET['search_nomor'] : null;

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link rel="icon" type="logo-msn2.png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        /* Profil dan search di atas */
        .top-bar {
            background-color: rgb(0, 98, 95);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            height: 60px;
        }

        .top-bar .profile {
            white-space: nowrap;
        }

        @media (max-width: 768px) {
    .top-bar {
        height: auto;
        padding: 8px 10px;
    }

    .profile {
        font-size: 0.85rem; /* Perkecil ukuran teks */
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .logout-btn {
        padding: 3px 8px;
        font-size: 0.85rem; /* Perkecil ukuran tombol */
        border-radius: 5px;
    }

}


        /* Sidebar */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #212529;
            position: fixed;
            padding-top: 100px;
            top: 0;
            left: 0;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar .nav-link {
            color: white;
            padding: 15px;
            margin-top: 15px;
            font-size: 1.1rem;
            transition: 0.2s;
        }

        .sidebar .nav-link:hover {
            background-color: rgb(0, 86, 173);
            color: white;
            font-size: 1.2rem;
            border-radius: 5px;
        }

        .content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 90px 50px 20px;
        }

        /* Tombol Logout */
        .logout-btn {
            background: linear-gradient(to right, #00625f, #009688);
            color: white;
            padding: 4px 11px;
            border-radius: 8px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.1s;
        }

        .logout-btn i {
            margin-right: 5px;
        }

        .logout-btn:hover {
            background: linear-gradient(to right, #00625f, #009688);
            transform: scale(1.1);
            color: white;
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                padding: 70px 20px;
            }
            .menu-toggle {
                display: block;
                cursor: pointer;
                color: white;
                font-size: 1.5rem;
                margin-right: 15px;
            }
        }
        .menu-toggle {
            display: none;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="profile">
            👤 Admin |
            <a href="/DataUserODP/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
    </div>

    <div class="sidebar">
        <div class="text-center">
            <img src="logo-msn.png" alt="Logo" width="150">
        </div>
        <a class="nav-link" href="/DataUserODP/dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a class="nav-link" href="/DataUserODP/OLT_MSN/olt_msn.php"><i class="fas fa-server"></i> OLT MSN</a>
        <a class="nav-link" href="/DataUserODP/OLT_BAGONG/olt_bagong.php"><i class="fas fa-server"></i> OLT Bagong</a>
        <a class="nav-link" href="/DataUserODP/OLT_SOREANG/olt_soreang.php"><i class="fas fa-server"></i> OLT Soreang</a>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector(".sidebar").classList.toggle("active");
        }
    </script>
</body>