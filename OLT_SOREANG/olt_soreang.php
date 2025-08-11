<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

// Koneksi ke database
include 'config3.php';
include '../navbar.php';

// Pastikan database terhubung
try {
    $pdo3 = new PDO("mysql:host=localhost;dbname=msn_db", "root", "");
    $pdo3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Definisi tabel
$olt_id   = 1;  // Nama tabel OLT
$pon_table   = "pon3"; // Nama tabel PON
$odp_table   = "odp3"; // Nama tabel ODP
$users_table = "users3";   // Nama tabel USERS

// Ambil parameter dari URL
$pon_id = isset($_GET['pon_id']) ? $_GET['pon_id'] : null;
$odp_id = isset($_GET['odp_id']) ? $_GET['odp_id'] : null;

// Ambil nama PON
if ($pon_id) {
    $stmt = $pdo3->prepare("SELECT nama_pon FROM pon3 WHERE id = ?");
    $stmt->execute([$pon_id]);
    $pon_name = $stmt->fetchColumn();
}

// Ambil nama ODP
if ($odp_id) {
    $stmt = $pdo3->prepare("SELECT nama_odp FROM odp3 WHERE id = ?");
    $stmt->execute([$odp_id]);
    $odp_name = $stmt->fetchColumn();
}

// **Tambah PON**
if (isset($_POST['tambah_pon'])) {
    $nama_pon = $_POST['nama_pon'];
    $port_max = $_POST['port_max'];
    $olt_id = 1; // Wajib untuk INSERT

    $stmt = $pdo3->prepare("INSERT INTO $pon_table (olt_id, nama_pon, port_max) VALUES (?, ?, ?)");
    if ($stmt->execute([$olt_id, $nama_pon, $port_max])) {
        header("Location: olt_soreang.php?success=pon_added");
        exit();
    } else {
        echo "<script>alert('Gagal menambahkan PON!'); window.location='olt_soreang.php';</script>";
        exit();
    }
}


// **Tambah ODP**
if (isset($_POST['tambah_odp'])) {
    $pon_id   = $_POST['pon_id'];
    $nama_odp = $_POST['nama_odp'];
    $port_max = $_POST['port_max'];

    $stmt = $pdo3->prepare("SELECT port_max, (SELECT COUNT(*) FROM $odp_table WHERE pon_id = ?) as jumlah_odp FROM $pon_table WHERE id = ?");
    $stmt->execute([$pon_id, $pon_id]);
    $result = $stmt->fetch();

    if ($result['jumlah_odp'] < $result['port_max']) {
        $stmt = $pdo3->prepare("INSERT INTO $odp_table (pon_id, nama_odp, port_max) VALUES (?, ?, ?)");
        if ($stmt->execute([$pon_id, $nama_odp, $port_max])) {
            header("Location: olt_soreang.php?pon_id={$pon_id}&success=odp_added");
            exit();
        }
    } else {
        echo "<script>alert('Gagal! Jumlah ODP sudah mencapai batas maksimum.'); window.location='olt_soreang.php?pon_id={$pon_id}';</script>";
    }
}

// **Tambah User**
if (isset($_POST['tambah_user'])) {
    $odp_id = $_POST['odp_id'];
    $nama_user = $_POST['nama_user'];
    $nomor_internet = $_POST['nomor_internet'];
    $alamat = $_POST['alamat'];

    $stmt = $pdo3->prepare("SELECT port_max, COUNT($users_table.id) as jumlah_user FROM $odp_table LEFT JOIN $users_table ON $odp_table.id = $users_table.odp_id WHERE $odp_table.id = ? GROUP BY $odp_table.id");
    $stmt->execute([$odp_id]);
    $result = $stmt->fetch();

    if (!$result || $result['jumlah_user'] < $result['port_max']) {
        $stmt = $pdo3->prepare("INSERT INTO $users_table (odp_id, nama_user, nomor_internet, alamat) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$odp_id, $nama_user, $nomor_internet, $alamat])) {
            header("Location: olt_soreang.php?pon_id=$pon_id&odp_id=$odp_id&success=user_added");

            exit();
        }
    } else {
        echo "<script>alert('Gagal! ODP sudah penuh.'); window.location='olt_soreang.php?odp_id={$odp_id}';</script>";
    }
}

// **Update User**
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $nama_user = $_POST['nama_user'];
    $nomor_internet = $_POST['nomor_internet'];
    $alamat = $_POST['alamat'];

    $stmt = $pdo3->prepare("UPDATE $users_table SET nama_user = ?, nomor_internet = ? WHERE id = ?");
    if ($stmt->execute([$nama_user, $nomor_internet, $user_id, $alamat])) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='olt_soreang.php?odp_id=" . $_GET['odp_id'] . "';</script>";
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
    <title>OLT SOREANG</title>
    <link rel="icon" href="    <link href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .content {
            margin-left: 260px;
            padding: 40px;
            background-color: #f9f9f9;
            min-height: 100vh;
        }

        .card-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }

        h1,
        h2,
        h3 {
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn {
            margin: 2px;
        }

        /* Tambahkan/replace di <style> */
        @media (max-width: 768px) {
            .content {
                margin-left: 0 !important;
                padding: 56px 5px 10px 5px !important;
                /* padding-top menyesuaikan tinggi navbar */
                min-width: 100vw;
            }

            .card-box {
                padding: 6px !important;
                margin-bottom: 10px !important;
            }

            .table-responsive {
                overflow-x: unset !important;
            }

            .table,
            .table th,
            .table td {
                font-size: 11px !important;
                padding: 3px 4px !important;
                white-space: normal !important;
                word-break: break-word !important;
            }

            .btn {
                font-size: 11px !important;
                padding: 2px 6px !important;
            }

            h1,
            h2,
            h3 {
                font-size: 1rem !important;
                margin-bottom: 7px !important;
            }
        }

        /* Agar sidebar (jika ada) tidak mengganggu */
        @media (max-width: 768px) {
            #sidebar {
                position: static !important;
                width: 100% !important;
                min-height: auto !important;
                height: auto !important;
                display: block !important;
                margin-bottom: 10px;
                z-index: 100;
            }
        }
    </style>



</head>

<body>

    <div class="container mt-4">
        <div class="content">
            <?php
            echo "<h1><i>1.OLT SOREANG</i></h1>";
            $pon_id = isset($_GET['pon_id']) ? (int)$_GET['pon_id'] : null;
            $odp_id = isset($_GET['odp_id']) ? $_GET['odp_id'] : null;

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
                            window.location = 'olt_soreang.php';
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
                    <button class="btn btn-primary btn-lg mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahPON">
                        <i class="fas fa-plus"></i> Tambah PON
                    </button>

                    <!-- Modal Tambah PON -->
                    <div class="modal fade" id="modalTambahPON" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Tambah PON</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="text" name="nama_pon" class="form-control mb-3" placeholder="Nama PON" required>
                                    <select name="port_max" class="form-control" required>
                                        <option disabled selected>Pilih Maks Port</option>
                                        <option value="2">2 Port</option>
                                        <option value="4">4 Port</option>
                                        <option value="8">8 Port</option>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="tambah_pon" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama PON</th>
                                            <th>Port</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo3->query("SELECT pon3.*, (SELECT COUNT(*) FROM odp3 WHERE odp3.pon_id = pon3.id) AS jumlah_odp FROM pon3 ORDER BY CAST(TRIM(REPLACE(nama_pon, 'PON', '')) AS UNSIGNED)");
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
                                                        <a href='olt_soreang.php?pon_id={$row['id']}' class='btn btn-primary btn-sm'>Lihat ODP</a>
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
                        </table>
                    </div>


                </div>
            <?php
            }

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
                            window.location = 'olt_soreang.php?pon_id={$pon_id}';
                        });
                    });
                </script>";
                }

            ?>
                <div class="card-box">
                    <h4 class="mt-5">Data ODP</h4>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalTambahODP">
                            <i class="fas fa-plus"></i> Tambah ODP
                        </button>
                        <a href="olt_soreang.php" class="btn btn-secondary btn-lg">
                            Kembali
                        </a>
                    </div>


                    <!-- Modal Tambah ODP -->
                    <div class="modal fade" id="modalTambahODP" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Tambah ODP</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="pon_id" value="<?= $pon_id ?>">
                                    <input type="text" name="nama_odp" class="form-control mb-3" placeholder="Nama ODP" required>
                                    <select name="port_max" class="form-control" required>
                                        <option disabled selected>Pilih Maks Port</option>
                                        <option value="8">8 Port</option>
                                        <option value="16">16 Port</option>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="tambah_odp" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <?php if ($pon_id): ?>
                        <p><i> >> <?php echo htmlspecialchars($pon_name); ?></i></p>
                    <?php endif; ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama ODP</th>
                                            <th>Port</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo3->prepare("SELECT odp3.*, (SELECT COUNT(*) FROM users3 WHERE users3.odp_id = odp3.id) AS jumlah_user FROM odp3 WHERE odp3.pon_id = ?");
                                        $stmt->execute([$pon_id]);
                                        while ($row = $stmt->fetch()) {
                                            $port_info = $row['jumlah_user'] . '/' . $row['port_max'];
                                            $port_color = 'text-success';
                                            if ($row['jumlah_user'] >= $row['port_max']) {
                                                $port_color = 'text-danger';
                                            } elseif ($row['jumlah_user'] >= $row['port_max'] * 0.75) {
                                                $port_color = 'text-warning';
                                            }
                                            echo "<tr>
                        <td>{$row['nama_odp']}</td>
                        <td class='$port_color'><strong>$port_info</strong></td>
                        <td>
                            <a href='olt_soreang.php?pon_id={$pon_id}&odp_id={$row['id']}' class='btn btn-primary btn-sm'>Lihat User</a>
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
                        </table>
                    </div>


                </div>
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
                            window.location = 'olt_soreang.php?pon_id=$pon_id&odp_id=$odp_id';
                        });
                    });
                </script>";
                }

            ?>
                <div class="card-box">
                    <h4 class="mt-5">Data User</h4>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                        <a href="olt_soreang.php?pon_id=<?= $pon_id ?>" class="btn btn-secondary btn-lg">Kembali</a>
                    </div>

                    <!-- Modal Tambah User -->
                    <div class="modal fade" id="modalTambahUser" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Tambah User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="odp_id" value="<?= $odp_id ?>">
                                    <input type="text" name="nama_user" class="form-control mb-3" placeholder="Nama User" required>
                                    <input type="text" name="nomor_internet" class="form-control mb-3" placeholder="Nomor Internet">
                                    <textarea name="alamat" class="form-control" placeholder="Alamat" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="tambah_user" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <?php if ($odp_id || $pon_id): ?>
                        <p>
                            <?php if ($pon_id): ?>
                                <i>>> <?php echo htmlspecialchars($pon_name); ?></i>
                            <?php endif; ?>
                            <?php if ($odp_id): ?>
                                &nbsp;&nbsp;<i>>> ODP <?php echo htmlspecialchars($odp_name); ?></i>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <div class="table-responsive">
                                <table class="table table-bordered">
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
                                        $stmt = $pdo3->prepare("SELECT * FROM users3 WHERE odp_id = ?");
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
                        </table>
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