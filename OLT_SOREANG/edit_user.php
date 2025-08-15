<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}

require_once '../log_helper.php';
require_once 'config3.php'; // pastikan $pdo3 sudah dibuat di sini (ERRMODE_EXCEPTION, dsb)

// ---------------------------------------------------------
// 1) AJAX endpoint: ambil ODP by PON (HARUS diletakkan
//    SEBELUM ada output/HTML/echo/include navbar).
// ---------------------------------------------------------
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_odp') {
    header('Content-Type: application/json; charset=utf-8');

    $pon_id = isset($_GET['pon_id']) ? (int)$_GET['pon_id'] : 0;

    $stmt = $pdo3->prepare("SELECT id, nama_odp FROM odp3 WHERE pon_id = ? ORDER BY nama_odp ASC");
    $stmt->execute([$pon_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit();
}

// ---------------------------------------------------------
// 2) Ambil parameter dasar
// ---------------------------------------------------------
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$odp_id = isset($_GET['odp_id']) ? (int)$_GET['odp_id'] : 0; // opsional
$pon_id = isset($_GET['pon_id']) ? (int)$_GET['pon_id'] : 0; // opsional

if ($id <= 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Parameter tidak lengkap!',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

// ---------------------------------------------------------
// 3) Ambil data user lama
// ---------------------------------------------------------
$stmt = $pdo3->prepare("SELECT * FROM users3 WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data tidak ditemukan!',
                text: 'User tidak ditemukan di database.',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = 'olt_soreang.php';
            });
        });
    </script>";
    exit();
}

// Derivasi pon_id dari odp_id user bila tidak dikirim
if ($odp_id <= 0) {
    $odp_id = (int)($user['odp_id'] ?? 0);
}
if ($pon_id <= 0 && $odp_id > 0) {
    $stmt = $pdo3->prepare("SELECT pon_id FROM odp3 WHERE id = ?");
    $stmt->execute([$odp_id]);
    $pon_id = (int)$stmt->fetchColumn();
}

// ---------------------------------------------------------
// 4) Ambil semua PON dan ODP awal (berdasarkan PON terpilih)
// ---------------------------------------------------------
$pon_stmt = $pdo3->query("
    SELECT id, nama_pon
    FROM pon3
    ORDER BY CAST(TRIM(REPLACE(nama_pon, 'PON', '')) AS UNSIGNED), id ASC
");
$all_pons = $pon_stmt->fetchAll(PDO::FETCH_ASSOC);

$all_odps = [];
if ($pon_id > 0) {
    $odp_stmt = $pdo3->prepare("SELECT id, nama_odp FROM odp3 WHERE pon_id = ? ORDER BY nama_odp ASC");
    $odp_stmt->execute([$pon_id]);
    $all_odps = $odp_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---------------------------------------------------------
// 5) Proses UPDATE
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_user      = trim($_POST['nama_user'] ?? '');
    $nomor_internet = trim($_POST['nomor_internet'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $pon_id_new     = (int)($_POST['pon_id'] ?? 0);
    $odp_id_new     = (int)($_POST['odp_id'] ?? 0);

    if ($nama_user && $nomor_internet && $alamat && $pon_id_new > 0 && $odp_id_new > 0) {
        // Validasi relasi PON-ODP
        $stmt = $pdo3->prepare("
            SELECT o.id, o.nama_odp, o.pon_id, p.nama_pon, o.port_max
            FROM odp3 o
            JOIN pon3 p ON p.id = o.pon_id
            WHERE o.id = ?
        ");
        $stmt->execute([$odp_id_new]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target || (int)$target['pon_id'] !== $pon_id_new) {
            $msg = "ODP tidak sesuai dengan PON yang dipilih.";
            echo "<script>alert(" . json_encode($msg) . ");</script>";
        } else {
            // Cek kapasitas bila pindah ODP
            $pindahOdp = ((int)$user['odp_id'] !== $odp_id_new);
            if ($pindahOdp) {
                $cek = $pdo3->prepare("
                    SELECT o.port_max, COUNT(u.id) AS jumlah_user
                    FROM odp3 o
                    LEFT JOIN users3 u ON u.odp_id = o.id
                    WHERE o.id = ?
                    GROUP BY o.id, o.port_max
                ");
                $cek->execute([$odp_id_new]);
                $kap = $cek->fetch(PDO::FETCH_ASSOC);

                $penuh = $kap && (int)$kap['jumlah_user'] >= (int)$kap['port_max'];
                if ($penuh) {
                    $msg = "ODP tujuan sudah penuh. Pilih ODP lain.";
                    echo "<script>alert(" . json_encode($msg) . ");</script>";
                } else {
                    // Lanjut simpan
                    $upd = $pdo3->prepare("
                        UPDATE users3
                        SET nama_user = ?, nomor_internet = ?, alamat = ?, odp_id = ?
                        WHERE id = ?
                    ");
                    $ok = $upd->execute([$nama_user, $nomor_internet, $alamat, $odp_id_new, $id]);

                    if ($ok) {
                        // Logging
                        $oleh = $_SESSION['admin']['username'] ?? 'unknown';
                        $log_parts = [];

                        if ($user['nama_user'] !== $nama_user) {
                            $log_parts[] = "Nama: {$user['nama_user']} → $nama_user";
                        }
                        if ($user['nomor_internet'] !== $nomor_internet) {
                            $log_parts[] = "Nomor Internet: {$user['nomor_internet']} → $nomor_internet";
                        }
                        if ($user['alamat'] !== $alamat) {
                            $log_parts[] = "Alamat: {$user['alamat']} → $alamat";
                        }

                        // Info lama
                        $old = $pdo3->prepare("
                            SELECT o.nama_odp, p.nama_pon
                            FROM odp3 o
                            JOIN pon3 p ON p.id = o.pon_id
                            WHERE o.id = ?
                        ");
                        $old->execute([(int)$user['odp_id']]);
                        $oldRow = $old->fetch(PDO::FETCH_ASSOC);

                        $old_odp = $oldRow['nama_odp'] ?? '(tidak diketahui)';
                        $old_pon = $oldRow['nama_pon'] ?? '(tidak diketahui)';

                        // Info baru
                        $new_odp = $target['nama_odp'] ?? '(tidak diketahui)';
                        $new_pon = $target['nama_pon'] ?? '(tidak diketahui)';

                        if ($pindahOdp) {
                            $log_parts[] = "Pindah: $old_pon / $old_odp → $new_pon / $new_odp";
                        }

                        if (!empty($log_parts)) {
                            tambahRiwayat("Edit User", $oleh, "Edit User\n" . implode("\n", $log_parts));
                        }

                        // Redirect sesuai lokasi baru
                        $redir_pon = (int)$target['pon_id'];
                        $redir_odp = $odp_id_new;
                        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Data user berhasil diperbarui.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        window.location.href = 'olt_soreang.php?pon_id={$redir_pon}&odp_id={$redir_odp}';
                                    });
                                });
                            </script>";
                        exit();
                    } else {
                        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: 'Gagal memperbarui data user.',
                                        showConfirmButton: true
                                    });
                                });
                            </script>";
                    }
                }
            } else {
                // Tidak pindah ODP → langsung update field text
                $upd = $pdo3->prepare("
                    UPDATE users3
                    SET nama_user = ?, nomor_internet = ?, alamat = ?
                    WHERE id = ?
                ");
                $ok = $upd->execute([$nama_user, $nomor_internet, $alamat, $id]);

                if ($ok) {
                    $oleh = $_SESSION['admin']['username'] ?? 'unknown';
                    $log_parts = [];
                    if ($user['nama_user'] !== $nama_user) $log_parts[] = "Nama: {$user['nama_user']} → $nama_user";
                    if ($user['nomor_internet'] !== $nomor_internet) $log_parts[] = "Nomor Internet: {$user['nomor_internet']} → $nomor_internet";
                    if ($user['alamat'] !== $alamat) $log_parts[] = "Alamat: {$user['alamat']} → $alamat";
                    if (!empty($log_parts)) {
                        tambahRiwayat("Edit User", $oleh, "Edit User\n" . implode("\n", $log_parts));
                    }

                    echo "<script>
                        alert('Data user berhasil diperbarui.');
                        window.location.href='olt_soreang.php?pon_id={$pon_id}&odp_id={$odp_id}';
                    </script>";
                    exit();
                } else {
                    echo "<script>alert('Gagal memperbarui data user.');</script>";
                }
            }
        }
    } else {
        echo "<script>alert('Form belum lengkap.');</script>";
    }
}

// ---------------------------------------------------------
// 6) Tampilkan halaman (navbar dll) SETELAH semua proses di atas
// ---------------------------------------------------------
include '../navbar.php';

// SweetAlert (opsional)
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Edit User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 260px;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="card-box">
            <h3 class="text-center mb-4">Edit User</h3>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama User:</label>
                    <input type="text" name="nama_user" value="<?= htmlspecialchars($user['nama_user'] ?? '') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Internet:</label>
                    <input type="text" name="nomor_internet" value="<?= htmlspecialchars($user['nomor_internet'] ?? '') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat:</label>
                    <input type="text" name="alamat" value="<?= htmlspecialchars($user['alamat'] ?? '') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="pon_id" class="form-label">Pilih PON:</label>
                    <select name="pon_id" id="pon_id" class="form-control" required>
                        <option value="">-- Pilih PON --</option>
                        <?php foreach ($all_pons as $pon): ?>
                            <option value="<?= (int)$pon['id']; ?>" <?= ((int)$pon['id'] === (int)$pon_id) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($pon['nama_pon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="odp_id" class="form-label">Pilih ODP:</label>
                    <select name="odp_id" id="odp_id" class="form-control" required>
                        <?php if (!empty($all_odps)): ?>
                            <?php foreach ($all_odps as $o): ?>
                                <option value="<?= (int)$o['id']; ?>" <?= ((int)$o['id'] === (int)$odp_id) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($o['nama_odp']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">-- Pilih PON terlebih dahulu --</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                    <a href="olt_soreang.php?pon_id=<?= (int)$pon_id ?>&odp_id=<?= (int)$odp_id ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Saat PON berubah, muat ODP via AJAX (endpoint file ini sendiri)
        document.getElementById('pon_id').addEventListener('change', function() {
            var ponId = this.value;
            var odpSelect = document.getElementById('odp_id');
            odpSelect.innerHTML = '<option value="">Memuat ODP...</option>';

            if (!ponId) {
                odpSelect.innerHTML = '<option value="">-- Pilih PON terlebih dahulu --</option>';
                return;
            }

            fetch('?ajax=get_odp&pon_id=' + encodeURIComponent(ponId), {
                    cache: 'no-store'
                })
                .then(function(resp) {
                    return resp.json();
                })
                .then(function(data) {
                    odpSelect.innerHTML = '';
                    if (!Array.isArray(data) || data.length === 0) {
                        var op = document.createElement('option');
                        op.value = '';
                        op.textContent = 'ODP tidak tersedia untuk PON ini';
                        odpSelect.appendChild(op);
                        return;
                    }
                    data.forEach(function(odp) {
                        var op = document.createElement('option');
                        op.value = odp.id;
                        op.textContent = odp.nama_odp;
                        odpSelect.appendChild(op);
                    });
                })
                .catch(function() {
                    odpSelect.innerHTML = '<option value="">Gagal memuat ODP</option>';
                });
        });
    </script>
</body>

</html>