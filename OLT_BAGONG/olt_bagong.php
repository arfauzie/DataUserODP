<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

include 'config2.php';
include '../navbar.php';
include 'log_helper.php';

$olt_id      = 2;
$pon_table   = "pon2";
$odp_table   = "odp2";
$users_table = "users2";

$pon_id = $_GET['pon_id'] ?? null;
$odp_id = $_GET['odp_id'] ?? null;

// nama pon & odp
$pon_name = '';
$odp_name = '';

if ($pon_id) {
    $stmt = $pdo2->prepare("SELECT nama_pon FROM $pon_table WHERE id = ?");
    $stmt->execute([$pon_id]);
    $pon_name = $stmt->fetchColumn() ?? '';
}

if ($odp_id) {
    $stmt = $pdo2->prepare("SELECT nama_odp FROM $odp_table WHERE id = ?");
    $stmt->execute([$odp_id]);
    $odp_name = $stmt->fetchColumn() ?? '';
}

// siapa adminnya
$oleh = is_array($_SESSION['admin']) ? ($_SESSION['admin']['username'] ?? 'admin') : $_SESSION['admin'];

/* TAMBAH PON */
if (isset($_POST['tambah_pon'])) {
    $nama_pon = trim($_POST['nama_pon']);
    $port_max = trim($_POST['port_max']);

    $stmt = $pdo2->prepare("INSERT INTO $pon_table (olt_id, nama_pon, port_max) VALUES (2, ?, ?)");
    if ($stmt->execute([$nama_pon, $port_max])) {
        $last_id = $pdo2->lastInsertId();
        $log = "ID PON: $last_id | Nama PON: $nama_pon | Port Max: $port_max | OLT ID: 2";

        tambahRiwayatBagong($pdo2, "Tambah PON", $oleh, $log);

        header("Location: olt_bagong.php?success=pon_added");
        exit();
    } else {
        echo "<script>alert('Gagal menambahkan PON!'); window.location='olt_bagong.php';</script>";
        exit();
    }
}

/* TAMBAH ODP */
if (isset($_POST['tambah_odp'])) {
    $pon_id   = (int) ($_POST['pon_id'] ?? 0);
    $nama_odp = trim($_POST['nama_odp']);
    $port_max = trim($_POST['port_max']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        echo "<script>alert('Latitude & Longitude harus angka!'); window.location='olt_bagong.php?pon_id={$pon_id}';</script>";
        exit();
    }

    $stmt = $pdo2->prepare("
        SELECT port_max, 
               (SELECT COUNT(*) FROM $odp_table WHERE pon_id = ?) as jumlah_odp 
        FROM $pon_table 
        WHERE id = ?
    ");
    $stmt->execute([$pon_id, $pon_id]);
    $result = $stmt->fetch();

    if ($result && $result['jumlah_odp'] < $result['port_max']) {
        $stmt = $pdo2->prepare("
            INSERT INTO $odp_table (pon_id, nama_odp, port_max, latitude, longitude) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$pon_id, $nama_odp, $port_max, $latitude, $longitude])) {
            $stmtPon = $pdo2->prepare("SELECT nama_pon FROM $pon_table WHERE id = ?");
            $stmtPon->execute([$pon_id]);
            $nama_pon = $stmtPon->fetchColumn() ?? '(unknown)';
            $log = "Nama ODP: $nama_odp | Port Max: $port_max | PON: $nama_pon | Lat: $latitude | Long: $longitude";

            tambahRiwayatBagong($pdo2, "Tambah ODP", $oleh, $log);

            header("Location: olt_bagong.php?pon_id={$pon_id}&success=odp_added");
            exit();
        } else {
            echo "<script>alert('Gagal menambahkan ODP!'); window.location='olt_bagong.php?pon_id={$pon_id}';</script>";
        }
    } else {
        echo "<script>alert('Gagal! ODP sudah penuh.'); window.location='olt_bagong.php?pon_id={$pon_id}';</script>";
    }
}

/* TAMBAH USER */
if (isset($_POST['tambah_user'])) {
    $odp_id         = $_POST['odp_id'];
    $nama_user      = trim($_POST['nama_user']);
    $nomor_internet = trim($_POST['nomor_internet']);
    $alamat         = trim($_POST['alamat']);

    $stmt = $pdo2->prepare("SELECT port_max, COUNT($users_table.id) as jumlah_user 
        FROM $odp_table 
        LEFT JOIN $users_table ON $odp_table.id = $users_table.odp_id 
        WHERE $odp_table.id = ? 
        GROUP BY $odp_table.id");
    $stmt->execute([$odp_id]);
    $result = $stmt->fetch();

    if (!$result || $result['jumlah_user'] < $result['port_max']) {
        $stmt = $pdo2->prepare("INSERT INTO $users_table (odp_id, nama_user, nomor_internet, alamat) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$odp_id, $nama_user, $nomor_internet, $alamat])) {
            $stmtOdp = $pdo2->prepare("SELECT nama_odp FROM $odp_table WHERE id = ?");
            $stmtOdp->execute([$odp_id]);
            $nama_odp = $stmtOdp->fetchColumn() ?? '(unknown)';

            $log = "Nama User: $nama_user | Nomor Internet: $nomor_internet | Alamat: $alamat | ODP: $nama_odp";
            tambahRiwayatBagong($pdo2, "Tambah User", $oleh, $log);

            header("Location: olt_bagong.php?pon_id=$pon_id&odp_id=$odp_id&success=user_added");
            exit();
        }
    } else {
        echo "<script>alert('Gagal! ODP penuh.'); window.location='olt_bagong.php?odp_id={$odp_id}';</script>";
    }
}

/* UPDATE USER */
if (isset($_POST['update_user'])) {
    $user_id        = $_POST['user_id'];
    $nama_user      = trim($_POST['nama_user']);
    $nomor_internet = trim($_POST['nomor_internet']);
    $alamat         = trim($_POST['alamat']);

    $stmt = $pdo2->prepare("UPDATE $users_table SET nama_user = ?, nomor_internet = ?, alamat = ? WHERE id = ?");
    if ($stmt->execute([$nama_user, $nomor_internet, $alamat, $user_id])) {
        $log = "Nama User: $nama_user | Nomor Internet: $nomor_internet | Alamat: $alamat";
        tambahRiwayatBagong($pdo2, "Update User", $oleh, $log);

        echo "<script>alert('Data berhasil diperbarui!'); window.location='olt_bagong.php?odp_id=" . $_GET['odp_id'] . "';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLT BAGONG</title>
    <link rel="icon" href="logo-msn2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Background abu full */
        html,
        body {
            height: 100%;
            width: 100%;
            background-color: #f8fcff;
            /* abu full */
        }

        /* Konten utama */
        .content {
            position: relative;
            margin-left: 200px;
            /* biar ga nabrak sidebar */
            padding: 40px;
            min-height: 100vh;
            width: calc(100% - 200px);
            /* isi sisa layar setelah sidebar */
            flex: 1;
            overflow-x: hidden;
            background-color: #f8fcff;
            /* abu juga di dalam konten */
        }

        .custom-breadcrumb {
            background: #fff;
            padding: 8px 16px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            display: inline-block;
        }

        .custom-breadcrumb ol {
            list-style: none;
            display: flex;
            gap: 8px;
            margin: 0;
            padding: 0;
            font-size: 14px;
            font-weight: 500;
            color: #444;
        }

        .custom-breadcrumb li {
            display: flex;
            align-items: center;
        }

        .custom-breadcrumb li:not(:last-child)::after {
            content: "›";
            margin-left: 8px;
            margin-right: 4px;
            color: #999;
        }

        .custom-breadcrumb li:last-child {
            font-weight: 600;
            color: #5b4bdb;
            /* warna ungu untuk yang aktif */
        }



        /* Bungkus utama */
        .main-wrapper {
            display: flex;
            width: 100%;
        }

        /* Card */
        .card-box {
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            overflow-x: auto;
        }

        /* ===== Tabel ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        .table th,
        .table td {
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            font-size: 14px;
        }

        .table th {
            background-color: #fff;
            font-weight: bold;
            color: #333;
            border-top: none;
        }

        .table tbody tr:hover {
            background-color: #e5e5e5;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .table td:first-child,
        .table th:first-child {
            border-left: none !important;
        }

        .table td:last-child,
        .table th:last-child {
            border-right: none !important;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                flex-direction: column;
            }

            .content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="content">
            <?php
            echo '<h1><i class="fas fa-server me-2"></i>OLT BAGONG</h1>';
            $pon_id = isset($_GET['pon_id']) ? (int)$_GET['pon_id'] : null;
            $odp_id = isset($_GET['odp_id']) ? $_GET['odp_id'] : null;

            /*siapkan $pon_name untuk dipakai di bawah */
            $pon_name = null;
            if ($pon_id) {
                $stmtPon = $pdo2->prepare("SELECT nama_pon FROM pon2 WHERE id = ?");
                $stmtPon->execute([$pon_id]);
                $pon_name = $stmtPon->fetchColumn();
            }

            if (!$pon_id && !$odp_id) {
                // SweetAlert Notifikasi
                if (isset($_GET['success']) && $_GET['success'] === 'pon_added') {
                    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'PON berhasil ditambahkan!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_bagong.php';
                });
            });
        </script>";
                }

                // Pesan sukses umum
                if (isset($_GET['success'])) {
                    $message = '';
                    switch ($_GET['success']) {
                        case 'pon_updated':
                            $message = 'PON berhasil diperbarui!';
                            break;
                        case 'odp_added':
                            $message = 'ODP berhasil ditambahkan!';
                            break;
                        case 'user_added':
                            $message = 'User berhasil ditambahkan!';
                            break;
                    }
                    if (!empty($message)) {
                        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '$message' });
                });
            </script>";
                    }
                }
            ?>
                <div class="card-box">
                    <h4 class="mt-5">Data PON</h4>
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPON">
                            <i class="fas fa-plus"></i> Tambah PON
                        </button>
                        <a href="riwayat2.php" class="btn btn-warning">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </div>

                    <div class="modal fade" id="modalTambahPON" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" class="modal-content border-0 shadow rounded-3">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-semibold">Tambah Data PON</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="nama_pon" class="form-label">Nama PON</label>
                                        <input type="text" id="nama_pon" name="nama_pon" class="form-control" placeholder="Nama PON" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="port_max" class="form-label">Maksimal Port</label>
                                        <select id="port_max" name="port_max" class="form-control" required>
                                            <option disabled selected>Pilih Maks Port</option>
                                            <option value="2">2 Port</option>
                                            <option value="4">4 Port</option>
                                            <option value="8">8 Port</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" name="tambah_pon" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama PON</th>
                                    <th>Port</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo2->query("SELECT pon2.*, (SELECT COUNT(*) FROM odp2 WHERE odp2.pon_id = pon2.id) AS jumlah_odp FROM pon2 ORDER BY CAST(TRIM(REPLACE(nama_pon, 'PON', '')) AS UNSIGNED)");
                                while ($row = $stmt->fetch()) {
                                    $port_info = $row['jumlah_odp'] . '/' . $row['port_max'];
                                    $port_color = 'text-success';
                                    if ($row['jumlah_odp'] >= $row['port_max']) {
                                        $port_color = 'text-danger';
                                    } elseif ($row['jumlah_odp'] >= $row['port_max'] * 0.75) {
                                        $port_color = 'text-warning';
                                    }
                                    echo "<tr>
                            <td>{$row['nama_pon']}</td>
                            <td class='$port_color'><strong>$port_info</strong></td>
                            <td>
                                <a href='olt_bagong.php?pon_id={$row['id']}' class='btn btn-primary btn-sm'>Lihat ODP</a>
                                <a href='edit_pon.php?id={$row['id']}' class='btn btn-warning btn-sm'>
                                    <i class='fas fa-edit'></i> Edit
                                </a>
                                <button class='btn btn-danger btn-sm' onclick=\"hapusItem('pon', {$row['id']})\">
                                    <i class='fas fa-trash'></i> Delete
                                </button>
                            </td>
                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php
            } // endif !$pon_id && !$odp_id

            if ($pon_id && !$odp_id) {
                if (isset($_GET['success']) && $_GET['success'] == 'odp_added') {
                    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'ODP berhasil ditambahkan!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location = 'olt_bagong.php?pon_id={$pon_id}';
                });
            });
        </script>";
                }

            ?>
                <div class="card-box">
                    <h4 class="mt-5">Data ODP</h4>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahODP">
                            <i class="fas fa-plus"></i> Tambah ODP
                        </button>
                        <a href="olt_bagong.php" class="btn btn-secondary">Kembali</a>
                    </div>

                    <!-- Modal Tambah ODP -->
                    <div class="modal fade" id="modalTambahODP" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" class="modal-content border-0 shadow rounded-3">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-semibold">Tambah Data ODP</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="pon_id" value="<?= $pon_id ?>">

                                    <div class="mb-3">
                                        <label for="nama_odp" class="form-label">Nama ODP</label>
                                        <input type="text" id="nama_odp" name="nama_odp" class="form-control" placeholder="Masukkan Nama ODP" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="port_max" class="form-label">Maksimal Port</label>
                                        <select id="port_max" name="port_max" class="form-select" required>
                                            <option disabled selected>Pilih Maks Port</option>
                                            <option value="8">8 Port</option>
                                            <option value="16">16 Port</option>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" id="latitude" name="latitude" class="form-control" placeholder="latitude" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="text" id="longitude" name="longitude" class="form-control" placeholder="longitude" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" name="tambah_odp" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($pon_id): ?>
                        <nav aria-label="breadcrumb" class="custom-breadcrumb">
                            <ol>
                                <li><?= htmlspecialchars($pon_name); ?></li>
                            </ol>
                        </nav>
                    <?php endif; ?>


                    <div class="table-responsive mt-3">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama ODP</th>
                                    <th>Port</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo2->prepare("SELECT odp2.*, (SELECT COUNT(*) FROM users2 WHERE users2.odp_id = odp2.id) AS jumlah_user FROM odp2 WHERE odp2.pon_id = ?");
                                $stmt->execute([$pon_id]);
                                while ($row = $stmt->fetch()) {
                                    $port_info = $row['jumlah_user'] . '/' . $row['port_max'];
                                    $port_color = 'text-success';
                                    if ($row['jumlah_user'] >= $row['port_max']) {
                                        $port_color = 'text-danger';
                                    } elseif ($row['jumlah_user'] >= $row['port_max'] * 0.75) {
                                        $port_color = 'text-warning';
                                    }

                                    $lat = $row['latitude'] ?? '-';
                                    $lon = $row['longitude'] ?? '-';

                                    echo "<tr>
                            <td>{$row['nama_odp']}</td>
                            <td class='$port_color'><strong>$port_info</strong></td>
                            <td>$lat</td>
                            <td>$lon</td>
                            <td>
                                <a href='olt_bagong.php?pon_id={$pon_id}&odp_id={$row['id']}' class='btn btn-primary btn-sm'>Lihat User</a>
                                <a href='edit_odp.php?id={$row['id']}' class='btn btn-warning btn-sm'>
                                    <i class='fas fa-edit'></i> Edit</a>
                                <button class='btn btn-danger btn-sm' onclick=\"hapusItem('odp', {$row['id']}, {$pon_id})\">
                                    <i class='fas fa-trash'></i> Delete
                                </button>
                            </td>
                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                // PROSES TAMBAH ODP
                if (isset($_POST['tambah_odp'])) {
                    $nama_odp = $_POST['nama_odp'];
                    $port_max = $_POST['port_max'];
                    $latitude = $_POST['latitude'];
                    $longitude = $_POST['longitude'];

                    $stmt = $pdo2->prepare("INSERT INTO odp2 (pon_id, nama_odp, port_max, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$pon_id, $nama_odp, $port_max, $latitude, $longitude]);

                    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'ODP berhasil ditambahkan!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'olt_bagong.php?pon_id={$pon_id}';
            });
        </script>";
                }
                ?>


            <?php
            }

            if ($odp_id) {
                if (isset($_GET['success']) && $_GET['success'] == 'user_added') {
                    echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'User berhasil ditambahkan!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location = 'olt_bagong.php?pon_id=$pon_id&odp_id=$odp_id';
                        });
                    });
                </script>";
                }

            ?>
                <div class="card-box">
                    <h4 class="mt-5">Data User</h4>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                        <a href="olt_bagong.php?pon_id=<?= $pon_id ?>" class="btn btn-secondary">Kembali</a>
                    </div>

                    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" class="modal-content border-0 shadow rounded-3">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-semibold">Tambah User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="odp_id" value="<?= $odp_id ?>">

                                    <div class="mb-3">
                                        <label for="nama_user" class="form-label">Nama User</label>
                                        <input type="text" id="nama_user" name="nama_user" class="form-control" placeholder="Masukkan Nama User" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nomor_internet" class="form-label">Nomor Internet</label>
                                        <input type="text" id="nomor_internet" name="nomor_internet" class="form-control" placeholder="Nomor Internet">
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea id="alamat" name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" name="tambah_user" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($pon_id || $odp_id): ?>
                        <nav aria-label="breadcrumb" class="custom-breadcrumb">
                            <ol>
                                <?php if ($pon_id): ?>
                                    <li>
                                        <?= htmlspecialchars($pon_name); ?>
                                    </li>
                                <?php endif; ?>

                                <?php if ($odp_id): ?>
                                    <li>
                                        <?= "ODP " . htmlspecialchars($odp_name); ?>
                                    </li>
                                <?php endif; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>



                    <div class="table-responsive mt-3">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama User</th>
                                        <th>Nomor Internet</th>
                                        <th>Alamat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo2->prepare("SELECT * FROM users2 WHERE odp_id = ?");
                                    $stmt->execute([$odp_id]);
                                    while ($row = $stmt->fetch()) {
                                        $user_json = htmlspecialchars(json_encode([
                                            'nama_user' => $row['nama_user'],
                                            'nomor_internet' => $row['nomor_internet'],
                                            'alamat' => $row['alamat'],
                                            'id' => $row['id']
                                        ]));
                                        echo "<tr>
                                            <td>{$row['nama_user']}</td>
                                            <td>" . (!empty($row['nomor_internet']) ? $row['nomor_internet'] : 'Belum ada') . "</td>
                                            <td>{$row['alamat']}</td>
                                            <td>
                                                <a href='edit_user.php?id={$row['id']}&odp_id={$odp_id}&pon_id={$pon_id}' class='btn btn-warning btn-sm'>
                                                    <i class='fas fa-edit'></i> Edit
                                                </a>
                                                <button class='btn btn-danger btn-sm' onclick=\"hapusItem('user', '{$row['id']}', '{$odp_id}', '{$pon_id}')\">
                                                    <i class='fas fa-trash'></i> Delete
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <!-- Notifikasi -->
        <div id="notif-container" class="position-fixed top-50 start-50 translate-middle text-center" style="z-index: 1050;"></div>
        <script>
            // Tambahkan JavaScript ini di bagian bawah halaman Anda
            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('form');
                forms.forEach(function(form) {
                    form.addEventListener('submit', function() {
                        const submitBtn = form.querySelector('button[type="tambah_pon"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                        }
                    });
                });
            });


            function hapusItem(type, id, odp_id, pon_id) {
                Swal.fire({
                    title: 'Yakin?',
                    text: "Data akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `delete_${type}.php?id=${id}&odp_id=${odp_id}&pon_id=${pon_id}`;
                    }
                });
            }

            function confirmDelete(id, name, type) {
                $('#deleteItemName').text(name);
                $('#deleteLink').attr('href', `delete_${type}.php?id=${id}`);
                $('#deleteModal').modal('show');

                $('#deleteLink').off('click').on('click', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: `delete_${type}.php`,
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#deleteModal').modal('hide');

                            let notifContainer = document.getElementById('notif-container');
                            let alertClass = response.success ? 'alert-success' : 'alert-danger';
                            let message = response.success ?
                                `${type.toUpperCase()} dengan ID ${id} berhasil dihapus.` :
                                `Gagal menghapus ${type.toUpperCase()} dengan ID ${id}.`;

                            let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show shadow-lg p-3 mb-2 bg-body rounded" role="alert">
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            `;

                            notifContainer.innerHTML = alertHtml;
                            setTimeout(() => {
                                notifContainer.innerHTML = '';
                            }, 3000);
                        },
                        error: function() {
                            $('#deleteModal').modal('hide');

                            let notifContainer = document.getElementById('notif-container');
                            notifContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show shadow-lg p-3 mb-2 bg-body rounded" role="alert">
                <strong>Terjadi kesalahan saat menghapus data.</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            `;
                            setTimeout(() => {
                                notifContainer.innerHTML = '';
                            }, 3000);
                        }
                    });
                });
            }
        </script>
</body>

</html>