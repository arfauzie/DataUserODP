<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'navbar.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/config2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/config3.php';

$databases = [
    'OLT_MSN' => ['pdo' => $pdo, 'tables' => ['pon1', 'odp1', 'users1']],
    'OLT_BAGONG' => ['pdo' => $pdo2, 'tables' => ['pon2', 'odp2', 'users2']],
    'OLT_SOREANG' => ['pdo' => $pdo3, 'tables' => ['pon3', 'odp3', 'users3']],
];

$olt_totals = [];

foreach ($databases as $nama_db => $data) {
    try {
        $conn = $data['pdo'];
        $total_pon = $total_odp = $total_users = 0;

        foreach ($data['tables'] as $table) {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            if (strpos($table, 'pon') !== false) $total_pon += $count;
            if (strpos($table, 'odp') !== false) $total_odp += $count;
            if (strpos($table, 'users') !== false) $total_users += $count;
        }

        $olt_totals[$nama_db] = [
            'pon' => $total_pon,
            'odp' => $total_odp,
            'users' => $total_users
        ];
    } catch (PDOException $e) {
        $olt_totals[$nama_db] = ['pon' => 0, 'odp' => 0, 'users' => 0];
        error_log("Error di $nama_db: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="logo-msn2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background-color: white;
        }

        .content {
            margin-left: 230px;
            padding: 90px 20px 20px;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
        }

        .welcome-text {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .welcome-text::after {
            content: "";
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            display: block;
            margin: 8px auto 0;
            border-radius: 10px;
        }

        .info-title {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            background: linear-gradient(to right, #3498db, #e67e22);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 15px;
        }

        .small-box {
            border-radius: 10px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 180px;
            transition: transform 0.3s ease;
        }

        .small-box:hover {
            transform: scale(1.03);
        }

        .small-box .inner {
            z-index: 2;
            position: relative;
        }

        .small-box .inner h4 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .small-box .inner p {
            font-size: 1rem;
            margin: 5px 0;
        }

        .small-box .icon {
            position: absolute;
            top: -10px;
            right: 10px;
            z-index: 1;
            font-size: 70px;
            opacity: 0.2;
        }

        .small-box-footer {
            display: inline-block;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 4px;
            text-decoration: none;
            z-index: 2;
            position: relative;
        }

        .small-box-footer:hover {
            background: rgba(255, 255, 255, 0.3);
            text-decoration: underline;
        }

        .bg-olt-msn {
            background: linear-gradient(to left, #38a3a5, #00625F);
        }

        .bg-olt-bagong {
            background: linear-gradient(to left, #1cba88, #0e5137);
        }

        .bg-olt-soreang {
            background: linear-gradient(to left, #3498DB, #1e3799);
        }

        /* Mobile Responsive */
        @media (max-width: 630px) {
            .row {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                margin: 0 -10px;
            }

            .col-md-4 {
                flex: 0 0 300px;
                max-width: 300px;
                padding: 0 10px;
            }

            .small-box {
                min-height: 160px;
                padding: 15px;
            }

            .small-box .icon {
                font-size: 50px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="container mt-4">
            <h1 class="welcome-text">Selamat Datang di Dashboard</h1>
            <h3 class="info-title">Informasi Data OLT</h3>

            <div class="row g-4 mt-3">
                <?php foreach ($olt_totals as $olt => $totals): ?>
                    <?php
                    $bg_class = match ($olt) {
                        'OLT_MSN' => 'bg-olt-msn',
                        'OLT_BAGONG' => 'bg-olt-bagong',
                        'OLT_SOREANG' => 'bg-olt-soreang',
                        default => 'bg-primary'
                    };
                    ?>
                    <div class="col-md-4">
                        <div class="small-box <?= $bg_class ?>">
                            <div class="inner">
                                <h4><?= htmlspecialchars($olt); ?></h4>
                                <p><i class="fa-solid fa-sitemap"></i> PON: <?= $totals['pon']; ?></p>
                                <p><i class="fa-solid fa-box"></i> ODP: <?= $totals['odp']; ?></p>
                                <p><i class="fa-solid fa-users"></i> Users: <?= $totals['users']; ?></p>
                                <a href="/DataUserODP/<?= strtolower($olt); ?>/<?= strtolower($olt); ?>.php" class="small-box-footer d-block text-center">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                            <div class="icon">
                                <i class="fa-solid fa-server"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>