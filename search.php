<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_MSN/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_BAGONG/config2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DataUserODP/OLT_SOREANG/config3.php';
include 'navbar.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : null;

if (!$query) {
    echo "<script>
        alert('Masukkan kata kunci pencarian!');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

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
        }

        .content {
            margin-left: 260px;
            padding: 40px;
            flex: 1;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding-top: 50px;
            }

            .table {
                font-size: 14px;
            }
        }

        .table thead {
            background-color: #00625F;
            color: white;
            text-align: center;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="content">
            <h3>Hasil Pencarian: "<?php echo htmlspecialchars($query); ?>"</h3>

            <?php if (count($all_results) > 0): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-success border-dark table-striped-columns">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Nomor Internet</th>
                                <th>OLT</th>
                                <th>PON</th>
                                <th>ODP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                                    <td><?= htmlspecialchars($row['nomor_internet']); ?></td>
                                    <td><?= htmlspecialchars($row['OLT']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_pon']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_odp']); ?></td>
                                    <td>
                                        <?php
                                        $olt_file = '';
                                        if ($row['OLT'] == 'OLT_MSN') $olt_file = 'OLT_MSN/olt_msn.php';
                                        elseif ($row['OLT'] == 'OLT_BAGONG') $olt_file = 'OLT_BAGONG/olt_bagong.php';
                                        elseif ($row['OLT'] == 'OLT_SOREANG') $olt_file = 'OLT_SOREANG/olt_soreang.php';

                                        $link = $olt_file . '?pon_id=' . urlencode($row['pon_id']) . '&odp_id=' . urlencode($row['odp_id']);
                                        ?>
                                        <a href="<?= $link ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-users"></i> Lihat User
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-4">Tidak ditemukan hasil untuk kata kunci tersebut.</div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn btn-secondary mt-4">Kembali</a>
        </div>
    </div>
</body>

</html>