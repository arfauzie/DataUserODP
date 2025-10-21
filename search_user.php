<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Koneksi ke semua OLT
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/config2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/config3.php';

// Navbar
include $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/Includes/navbar_user.php';

// Ambil query pencarian
$query = isset($_GET['query']) ? trim($_GET['query']) : null;

if (!$query) {
    echo "<script>
        alert('Masukkan kata kunci pencarian!');
        window.location.href = 'dashboard_user.php';
    </script>";
    exit();
}

// Fungsi pencarian
function searchDatabaseAllFields($conn, $query, $nama_db, $users_table, $odp_table, $pon_table)
{
    $stmt = $conn->prepare("SELECT 
                                $users_table.nama_user, 
                                $users_table.nomor_internet, 
                                $odp_table.nama_odp, 
                                $odp_table.id AS odp_id,
                                $pon_table.nama_pon,
                                $pon_table.id AS pon_id
                            FROM $users_table 
                            JOIN $odp_table ON $users_table.odp_id = $odp_table.id 
                            JOIN $pon_table ON $odp_table.pon_id = $pon_table.id 
                            WHERE 
                                $users_table.nama_user LIKE ? OR 
                                $users_table.nomor_internet LIKE ? OR 
                                $odp_table.nama_odp LIKE ?");
    $stmt->execute(["%$query%", "%$query%", "%$query%"]);
    $results = $stmt->fetchAll();

    foreach ($results as &$row) {
        $row['OLT'] = $nama_db;
    }
    return $results;
}

// Jalankan pencarian ke semua database
$all_results = [];
$databases = [
    'OLT_MSN' => ['conn' => $pdo, 'users' => ['users1'], 'odp' => ['odp1'], 'pon' => ['pon1']],
    'OLT_BAGONG' => ['conn' => $pdo2, 'users' => ['users2'], 'odp' => ['odp2'], 'pon' => ['pon2']],
    'OLT_SOREANG' => ['conn' => $pdo3, 'users' => ['users3'], 'odp' => ['odp3'], 'pon' => ['pon3']],
];

foreach ($databases as $nama_db => $db_info) {
    foreach ($db_info['users'] as $key => $users_table) {
        $results = searchDatabaseAllFields(
            $db_info['conn'],
            $query,
            $nama_db,
            $users_table,
            $db_info['odp'][$key],
            $db_info['pon'][$key]
        );
        $all_results = array_merge($all_results, $results);
    }
}

// Highlight kata yang cocok
function highlightMatch($text, $query)
{
    return preg_replace("/(" . preg_quote($query, '/') . ")/i", "<mark>$1</mark>", htmlspecialchars($text));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8fcff;
        }

        .content {
            margin-left: 260px;
            padding: 40px;
            flex: 1;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 70px 18px;
            }

            .table {
                font-size: 14px;
            }

            h3 {
                font-size: 1.2rem;
                text-align: center;
            }
        }

        .table thead {
            background-color: #00625F;
            color: white;
            text-align: center;
        }

        .table tbody tr:hover {
            background-color: #eaf7ff;
            transition: 0.2s;
        }

        .badge-olt {
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 5px 10px;
            color: #fff;
        }

        .badge-OLT_MSN {
            background-color: #0078D7;
        }

        .badge-OLT_BAGONG {
            background-color: #ff8c00;
        }

        .badge-OLT_SOREANG {
            background-color: #28a745;
        }

        mark {
            background-color: #ffe780;
            padding: 0 2px;
            border-radius: 3px;
        }

        .table th,
        .table td {
            padding: 14px;
            vertical-align: middle;
        }

        .table td i {
            margin-right: 6px;
        }

        .header-box {
            background-color: #fff;
            border-left: 5px solid #00625F;
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.08);
        }

        .btn-primary {
            background-color: #0078D7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #005fa3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #555f66;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="content">
            <div class="header-box mb-3">
                <h3><i class="fas fa-search"></i> Hasil Pencarian: "<span class="text-primary"><?= htmlspecialchars($query); ?></span>"</h3>
            </div>

            <div class="mt-4">
                <a href="dashboard_user.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            <?php if (count($all_results) > 0): ?>
                <div class="table-responsive fade-in mt-4">
                    <table class="table table-bordered align-middle shadow-sm">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Nomor Internet</th>
                                <th>OLT</th>
                                <th>PON</th>
                                <th>ODP</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_results as $row): ?>
                                <tr>
                                    <td><?= highlightMatch($row['nama_user'], $query); ?></td>
                                    <td><?= highlightMatch($row['nomor_internet'], $query); ?></td>
                                    <td>
                                        <span class="badge badge-olt badge-<?= htmlspecialchars($row['OLT']); ?>">
                                            <?= htmlspecialchars($row['OLT']); ?>
                                        </span>
                                    </td>
                                    <td><?= highlightMatch($row['nama_pon'], $query); ?></td>
                                    <td><?= highlightMatch($row['nama_odp'], $query); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $olt_file = '';
                                        if ($row['OLT'] == 'OLT_MSN') $olt_file = '/DataUserODP/OLT_MSN/olt_msn_user.php';
                                        elseif ($row['OLT'] == 'OLT_BAGONG') $olt_file = '/DataUserODP/OLT_BAGONG/olt_bagong_user.php';
                                        elseif ($row['OLT'] == 'OLT_SOREANG') $olt_file = '/DataUserODP/OLT_SOREANG/olt_soreang_user.php';

                                        $link = $olt_file . '?pon_id=' . urlencode($row['pon_id']) . '&odp_id=' . urlencode($row['odp_id']);
                                        ?>
                                        <a href="<?= $link ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat User
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-4 fade-in text-center shadow-sm">
                    <i class="fas fa-exclamation-circle"></i> Tidak ditemukan hasil untuk kata kunci "<b><?= htmlspecialchars($query); ?></b>".
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>