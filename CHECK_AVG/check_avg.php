<?php
include 'config.php';
include '../navbar.php';

$olt_mapping = [
    'olt_msn'     => 'OLT MSN',
    'olt_bagong'  => 'OLT Bagong',
    'olt_soreang' => 'OLT Soreang'
];

// mapping OLT ke file php tujuan
$file_mapping = [
    'olt_msn'     => '../OLT_MSN/olt_msn.php',
    'olt_bagong'  => '../OLT_BAGONG/olt_bagong.php',
    'olt_soreang' => '../OLT_SOREANG/olt_soreang.php'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masukan Lokasi</title>
    <link rel="icon" href="logo-msn2.png">

    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 60px;
        }

        .form-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            margin-top: 20px;
        }

        .form-card {
            max-width: 450px;
            width: 100%;
        }

        .form-label {
            margin-bottom: 3px;
            font-weight: 500;
        }

        .form-control {
            padding: 8px 10px;
        }

        /* 🔹 Fix modal biar auto tinggi untuk 2 ODP, tapi tetap ada scroll kalau lebih banyak */
        .modal-dialog {
            max-width: 600px;
            margin: 1.75rem auto;
        }

        .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body {
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
                margin-top: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <h1 class="text-center mb-4">
            <i class="fas fa-map-marker-alt me-2"></i>MASUKAN LOKASI
        </h1>

        <!-- Form Cari -->
        <div class="form-wrapper">
            <div class="card shadow rounded-3 p-4 form-card">
                <h5 class="fw-semibold mb-3 text-center">Cari ODP Terdekat</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Masukkan latitude" required>
                    </div>
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Masukkan longitude" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="cek_odp_terdekat" class="btn btn-primary px-4">Cari ODP</button>
                    </div>
                </form>
            </div>
        </div>

        <?php
        if (isset($_POST['cek_odp_terdekat'])) {
            $lat_user = floatval($_POST['latitude']);
            $lon_user = floatval($_POST['longitude']);

            $all_odp = [];
            $odp_tables = ['odp1', 'odp2', 'odp3'];
            $pon_tables = ['pon1', 'pon2', 'pon3'];

            foreach ($odp_tables as $index => $odp_table) {
                $pon_table = $pon_tables[$index];

                // JOIN: ODP - PON - OLT
                $stmt = $pdo->query("
                    SELECT o.*, p.nama_pon, p.olt_id, olt.nama_olt,
                        (SELECT COUNT(*) FROM users" . ($index + 1) . " u WHERE u.odp_id = o.id) AS jumlah_user
                    FROM $odp_table o
                    JOIN $pon_table p ON o.pon_id = p.id
                    JOIN olt ON p.olt_id = olt.olt_id
                ");

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $lat_odp = floatval($row['latitude']);
                    $lon_odp = floatval($row['longitude']);
                    $earthRadius = 6371000; // meter

                    $dLat = deg2rad($lat_odp - $lat_user);
                    $dLon = deg2rad($lon_odp - $lon_user);
                    $a = sin($dLat / 2) * sin($dLat / 2) +
                        cos(deg2rad($lat_user)) * cos(deg2rad($lat_odp)) *
                        sin($dLon / 2) * sin($dLon / 2);
                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                    $distance = $earthRadius * $c;

                    $row['distance'] = round($distance);
                    $row['table_index'] = $index + 1;
                    $all_odp[] = $row;
                }
            }
            usort($all_odp, fn($a, $b) => $a['distance'] <=> $b['distance']);
            $top_odp = array_slice($all_odp, 0, 2);
        ?>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="modalHasilODP" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content shadow rounded-3">
                        <div class="modal-body">
                            <h5 class="fw-bold text-center mb-3">ODP Terdekat</h5>
                            <?php if (count($top_odp) > 0): ?>
                                <?php foreach ($top_odp as $odp): ?>
                                    <?php
                                    $port_info = $odp['jumlah_user'] . '/' . $odp['port_max'];
                                    $port_color = 'text-success';
                                    if ($odp['jumlah_user'] >= $odp['port_max']) {
                                        $port_color = 'text-danger';
                                    } elseif ($odp['jumlah_user'] >= $odp['port_max'] * 0.75) {
                                        $port_color = 'text-warning';
                                    }

                                    // mapping file tujuan OLT
                                    $olt_file = $file_mapping[$odp['nama_olt']] ?? 'index.php';

                                    // link ke OLT spesifik
                                    $link = "{$olt_file}?odp_id={$odp['id']}&pon_id={$odp['pon_id']}&tbl={$odp['table_index']}";
                                    $olt_display = $olt_mapping[$odp['nama_olt']] ?? $odp['nama_olt'];
                                    ?>
                                    <div class="mb-3 p-3 border rounded-3">
                                        <h6 class="fw-bold mb-1"><?= $odp['nama_odp'] ?></h6>
                                        <p class="mb-1">
                                            <strong>OLT:</strong> <?= $olt_display ?><br>
                                            <strong>PON:</strong> <?= $odp['nama_pon'] ?><br>
                                            <strong>Port:</strong> <span class="<?= $port_color ?>"><?= $port_info ?></span><br>
                                            <strong>Jarak:</strong> <?= $odp['distance'] ?> meter
                                        </p>
                                        <a href="<?= $link ?>" class="btn btn-primary btn-sm">Lihat ODP</a>

                                        <?php if ($odp['distance'] <= 300): ?>
                                            <p>Tercov​er</p>
                                        <?php else: ?>
                                            <p>Tidak Tercov​er</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach ?>
                            <?php else: ?>
                                <p class="text-center text-muted">Tidak ada ODP ditemukan.</p>
                            <?php endif ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                var modal = new bootstrap.Modal(document.getElementById('modalHasilODP'));
                modal.show();
            </script>
        <?php } ?>
    </div>
</body>

</html>