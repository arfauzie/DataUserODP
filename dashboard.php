<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /DataUserODP/dashboard.php");
    exit();
}


include __DIR__ . '/Includes/navbar.php';

// ✅ KONEKSI KE SEMUA OLT
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/config2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/config3.php';

// ✅ HELPER LOG PER OLT
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/log_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/log_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/log_helper.php';

// ✅ DATA RINGKASAN OLT
$databases = [
    'OLT_MSN'     => ['pdo' => $pdo,  'tables' => ['pon1', 'odp1', 'users1']],
    'OLT_BAGONG'  => ['pdo' => $pdo2, 'tables' => ['pon2', 'odp2', 'users2']],
    'OLT_SOREANG' => ['pdo' => $pdo3, 'tables' => ['pon3', 'odp3', 'users3']],
];

$olt_totals = [];
$total_pon_all = $total_odp_all = $total_users_all = 0;

foreach ($databases as $nama_db => $data) {
    try {
        $conn = $data['pdo'];
        $total_pon = $total_odp = $total_users = 0;

        foreach ($data['tables'] as $table) {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = (int)$stmt->fetchColumn();
            if (strpos($table, 'pon') !== false)   $total_pon   += $count;
            if (strpos($table, 'odp') !== false)   $total_odp   += $count;
            if (strpos($table, 'users') !== false) $total_users += $count;
        }

        $olt_totals[$nama_db] = [
            'pon'   => $total_pon,
            'odp'   => $total_odp,
            'users' => $total_users
        ];

        $total_pon_all   += $total_pon;
        $total_odp_all   += $total_odp;
        $total_users_all += $total_users;
    } catch (PDOException $e) {
        $olt_totals[$nama_db] = ['pon' => 0, 'odp' => 0, 'users' => 0];
    }
}

// ✅ LOG AKTIVITAS
$logs = [];

try {
    $riwayat_msn = getRiwayatMSN($pdo, 20);
    foreach ($riwayat_msn as $row) {
        $row['olt'] = 'MSN';
        $logs[] = $row;
    }
} catch (Exception $e) {
}

try {
    $riwayat_bagong = getRiwayatBagong($pdo2, 20);
    foreach ($riwayat_bagong as $row) {
        $row['olt'] = 'BAGONG';
        $logs[] = $row;
    }
} catch (Exception $e) {
}

try {
    $riwayat_soreang = getRiwayatSoreang($pdo3, 20);
    foreach ($riwayat_soreang as $row) {
        $row['olt'] = 'SOREANG';
        $logs[] = $row;
    }
} catch (Exception $e) {
}

// ✅ URUTKAN LOG
usort($logs, fn($a, $b) => strtotime($b['waktu']) <=> strtotime($a['waktu']));
$recentLogs = array_slice($logs, 0, 5);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>
    <link rel="icon" href="/DataUserODP/asset/logo-msn2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        :root {
            --sidebar-width: 230px;
            --page-padding: 28px;
        }

        body {
            background-color: #f8fcff;
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial;
            margin: 0;
            overflow-x: hidden;
        }

        .content {
            margin-left: var(--sidebar-width);
            padding: calc(var(--page-padding) + 60px) var(--page-padding) var(--page-padding);
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                width: 100%;
                padding: 60px 16px 16px;
            }
        }

        .page-header .title {
            font-size: 20px;
            font-weight: 600;
            margin: 10px 0 4px 0;
        }

        /* === BOX OLT === */
        .small-box {
            border-radius: 8px;
            padding: 10px 12px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 120px;
            transition: transform .18s ease;
        }

        .small-box:hover {
            transform: translateY(-6px);
        }

        .small-box .inner h5 {
            margin: 0 0 6px 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .small-box .inner p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .small-box .icon {
            position: absolute;
            top: -6px;
            right: 8px;
            font-size: 64px;
            opacity: .12;
        }

        .small-box-footer {
            display: inline-block;
            margin-top: 8px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .bg-olt-msn {
            background: linear-gradient(135deg, #38a3a5, #00625f);
        }

        .bg-olt-bagong {
            background: linear-gradient(135deg, #1cba88, #0e5137);
        }

        .bg-olt-soreang {
            background: linear-gradient(135deg, #3498db, #1e3799);
        }

        /* === PANEL === */
        .panel-row {
            margin-top: 22px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .panel-left {
            flex: 1 1 0;
        }

        .panel-right {
            width: 320px;
            flex: 0 0 320px;
        }

        .history-card,
        .summary-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            padding: 14px;
        }

        .history-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .history-item {
            padding: 8px 6px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .history-item:last-child {
            border-bottom: 0;
        }

        .history-empty {
            text-align: center;
            color: #777;
            padding: 18px 0;
        }

        .summary-grid {
            display: grid;
            gap: 12px;
        }

        .summary-tile {
            background: #f8fbff;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }

        .summary-tile .value {
            font-size: 1.2rem;
            color: #0b61d6;
            font-weight: 700;
        }

        /* === RESPONSIVE FIX === */
        @media (max-width: 992px) {
            .panel-row {
                flex-direction: column-reverse;
            }

            .panel-right {
                width: 100%;
                flex: 1 1 auto;
            }
        }

        @media (max-width: 480px) {
            .small-box {
                min-height: 100px;
                padding: 8px 10px;
            }

            .small-box .inner h5 {
                font-size: 1rem;
            }

            .small-box .inner p {
                font-size: 0.85rem;
            }

            .summary-tile {
                font-size: 0.9rem;
            }

            .summary-tile .value {
                font-size: 1rem;
            }
        }

        @media (max-width: 360px) {
            .content {
                padding: 80px 12px 16px;
            }

            .small-box {
                min-height: 90px;
            }

            .panel-right,
            .panel-left {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="container-big">
            <div class="big-box">
                <div class="row g-3">
                    <?php foreach ($olt_totals as $olt => $totals):
                        $bg_class = match ($olt) {
                            'OLT_MSN' => 'bg-olt-msn',
                            'OLT_BAGONG' => 'bg-olt-bagong',
                            'OLT_SOREANG' => 'bg-olt-soreang',
                            default => 'bg-primary'
                        }; ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="small-box <?= $bg_class ?>">
                                <div class="inner">
                                    <h5><?= htmlspecialchars($olt); ?></h5>
                                    <p><i class="fa-solid fa-sitemap"></i> PON: <strong><?= $totals['pon'] ?></strong></p>
                                    <p><i class="fa-solid fa-box"></i> ODP: <strong><?= $totals['odp'] ?></strong></p>
                                    <p><i class="fa-solid fa-users"></i> Users: <strong><?= $totals['users'] ?></strong></p>
                                    <a href="/DataUserODP/<?= strtolower($olt); ?>/<?= strtolower($olt); ?>.php" class="small-box-footer">
                                        More info <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                                <div class="icon"><i class="fa-solid fa-server"></i></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="panel-row">
                <div class="panel-left">
                    <div class="history-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Riwayat Aktivitas Terbaru</h6>
                        </div>
                        <?php if (!empty($recentLogs)): ?>
                            <ul class="history-list">
                                <?php foreach ($recentLogs as $log): ?>
                                    <li class="history-item">
                                        <div style="flex:1">
                                            <div style="font-weight:600; font-size:0.95rem; margin-bottom:4px;">
                                                [<?= htmlspecialchars($log['olt']) ?>] <?= htmlspecialchars($log['aksi']) ?>
                                            </div>
                                            <div style="font-size:0.9rem; color:#444;"><?= htmlspecialchars($log['keterangan']) ?></div>
                                        </div>
                                        <div style="text-align:right; min-width:120px;">
                                            <div style="font-size:0.9rem; font-weight:600;"><?= htmlspecialchars($log['oleh']) ?></div>
                                            <div style="font-size:12px; color:#777;"><?= $log['waktu'] ?></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="history-empty">Belum ada aktivitas.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel-right">
                    <div class="summary-card">
                        <h6 class="mb-3">Ringkasan</h6>
                        <div class="summary-grid">
                            <div class="summary-tile">
                                <div>Total PON</div>
                                <div class="value"><?= $total_pon_all ?></div>
                            </div>
                            <div class="summary-tile">
                                <div>Total ODP</div>
                                <div class="value"><?= $total_odp_all ?></div>
                            </div>
                            <div class="summary-tile">
                                <div>Total Users</div>
                                <div class="value"><?= $total_users_all ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../Includes/footer.php'; ?>
</body>

</html>