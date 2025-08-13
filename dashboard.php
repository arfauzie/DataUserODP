<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'navbar.php';

// koneksi OLT (sesuaikan path jika berbeda)
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/config2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/config3.php';

// koneksi log (pastikan file log_koneksi.php berada di folder yang sama)
require_once __DIR__ . '/koneksi_log.php'; // ini membuat $pdo_log

// Ambil ringkasan OLT
$databases = [
    'OLT_MSN' => ['pdo' => $pdo, 'tables' => ['pon1', 'odp1', 'users1']],
    'OLT_BAGONG' => ['pdo' => $pdo2, 'tables' => ['pon2', 'odp2', 'users2']],
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
            if (strpos($table, 'pon') !== false) $total_pon += $count;
            if (strpos($table, 'odp') !== false) $total_odp += $count;
            if (strpos($table, 'users') !== false) $total_users += $count;
        }

        $olt_totals[$nama_db] = [
            'pon' => $total_pon,
            'odp' => $total_odp,
            'users' => $total_users
        ];

        $total_pon_all += $total_pon;
        $total_odp_all += $total_odp;
        $total_users_all += $total_users;
    } catch (PDOException $e) {
        $olt_totals[$nama_db] = ['pon' => 0, 'odp' => 0, 'users' => 0];
    }
}

// Ambil 5 log terbaru dari tabel log_aktivitas
try {
    $stmtLogs = $pdo_log->query("SELECT id, aksi, oleh, keterangan, waktu FROM log_aktivitas ORDER BY waktu DESC LIMIT 5");
    $recentLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
    // total log count (untuk ringkasan kecil)
    $stmtCount = $pdo_log->query("SELECT COUNT(*) FROM log_aktivitas");
    $total_logs = (int)$stmtCount->fetchColumn();
} catch (PDOException $e) {
    $recentLogs = [];
    $total_logs = 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="icon" href="logo-msn2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        :root {
            --sidebar-width: 230px;
            --page-padding: 28px;
        }

        body {
            background-color: #f8f9fa;
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial;
            margin: 0;
        }

        /* konten utama menyesuaikan sidebar */
        .content {
            margin-left: var(--sidebar-width);
            padding: var(--page-padding);
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                width: 100%;
                padding: 16px;
            }
        }

        /* header biru full */
        .page-header {
            background: linear-gradient(90deg, #0555e9d6, #2004beff);
            padding: 60px var(--page-padding);
            color: white;
            margin: 0;
            border-radius: 0;
            width: calc(100% + var(--page-padding) * 2);
            box-sizing: border-box;
            margin-left: calc(-1 * var(--page-padding));
            margin-right: calc(-1 * var(--page-padding));
        }

        .page-header .title {
            font-size: 20px;
            font-weight: 600;
            margin: 10px 0 4px 0;
        }

        .page-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        /* area kartu OLT */
        .container-big {
            padding-left: 0;
            padding-right: 0;
        }

        .big-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.06);
            padding: 20px;
            margin-top: -28px;
            /* naik ke header */
        }

        .small-box {
            border-radius: 10px;
            padding: 18px;
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
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
        }

        .small-box .inner p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.8;
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

        /* History & Summary area */
        .panel-row {
            margin-top: 22px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
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

        .history-card h6 {
            margin: 0 0 8px 0;
            font-weight: 600;
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

        .history-time {
            font-size: 12px;
            color: #888;
            min-width: 100px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .summary-tile {
            background: #f8fbff;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }

        .summary-tile .value {
            font-size: 1.2rem;
            color: #0b61d6;
            font-weight: 700;
        }

        /* tabel ringkasan (dashboard small table) */
        .history-card .table {
            margin-bottom: 0;
        }

        .history-empty {
            text-align: center;
            color: #777;
            padding: 18px 0;
        }

        @media (max-width: 900px) {
            .panel-row {
                flex-direction: column-reverse;
            }

            .panel-right {
                width: 100%;
                flex: 1 1 0;
            }
        }
    </style>

</head>

<body>
    <div class="content">
        <!-- header -->
        <div class="page-header">
            <div class="title">Dashboard</div>
        </div>

        <!-- Kartu OLT -->
        <div class="container-big">
            <div class="big-box">
                <div class="row g-3">
                    <?php foreach ($olt_totals as $olt => $totals): ?>
                        <?php
                        $bg_class = match ($olt) {
                            'OLT_MSN' => 'bg-olt-msn',
                            'OLT_BAGONG' => 'bg-olt-bagong',
                            'OLT_SOREANG' => 'bg-olt-soreang',
                            default => 'bg-primary'
                        };
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="small-box <?= $bg_class ?>">
                                <div class="inner">
                                    <h5><?= htmlspecialchars($olt); ?></h5>
                                    <p><i class="fa-solid fa-sitemap"></i> PON: <strong><?= $totals['pon'] ?></strong></p>
                                    <p><i class="fa-solid fa-box"></i> ODP: <strong><?= $totals['odp'] ?></strong></p>
                                    <p><i class="fa-solid fa-users"></i> Users: <strong><?= $totals['users'] ?></strong></p>
                                    <a href="/DataUserODP/<?= strtolower($olt); ?>/<?= strtolower($olt); ?>.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                                <div class="icon"><i class="fa-solid fa-server"></i></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Panel Riwayat & Summary -->


            </div>
            <div class="panel-row">
                <!-- left: history -->
                <div class="panel-left">
                    <div class="history-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Riwayat Aktivitas Terbaru</h6>
                            <a href="log_riwayat.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>

                        <?php if (!empty($recentLogs)): ?>
                            <ul class="history-list">
                                <?php foreach ($recentLogs as $log): ?>
                                    <li class="history-item">
                                        <div style="flex:1">
                                            <div style="font-weight:600; font-size:0.95rem; margin-bottom:4px;"><?= htmlspecialchars($log['aksi']) ?></div>
                                            <div style="font-size:0.9rem; color:#444; white-space:pre-wrap;"><?= htmlspecialchars($log['keterangan']) ?></div>
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

                <!-- right: summary -->
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
                            <div class="summary-tile">
                                <div>Total Logs</div>
                                <div class="value"><?= $total_logs ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /panel-row -->
        </div> <!-- /container-big -->
    </div> <!-- /content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>