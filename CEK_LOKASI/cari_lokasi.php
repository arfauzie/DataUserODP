<?php
include 'config.php';
include '../navbar.php'; // tetap pakai navbar biar konsisten layout
?>

<style>
    /* Supaya form bener-bener di tengah area konten */
    .form-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        /* tinggi penuh minus header/navbar */
    }

    .form-card {
        max-width: 450px;
        width: 100%;
    }

    /* Table juga ditengah */
    .table-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    .table-wrapper .table-responsive {
        max-width: 900px;
        width: 100%;
    }
</style>

<div class="content">
    <h1 class="text-center mb-4">
        <i class="fas fa-map-marker-alt me-2"></i>Cek Lokasi
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
            $stmt = $pdo->query("SELECT o.*, p.nama_pon, 
                    (SELECT COUNT(*) FROM users" . ($index + 1) . " u WHERE u.odp_id = o.id) as jumlah_user
                FROM $odp_table o
                JOIN $pon_table p ON o.pon_id = p.id");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lat_odp = floatval($row['latitude']);
                $lon_odp = floatval($row['longitude']);
                $earthRadius = 6371;

                $dLat = deg2rad($lat_odp - $lat_user);
                $dLon = deg2rad($lon_odp - $lon_user);
                $a = sin($dLat / 2) * sin($dLat / 2) +
                    cos(deg2rad($lat_user)) * cos(deg2rad($lat_odp)) *
                    sin($dLon / 2) * sin($dLon / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;

                $row['distance'] = round($distance, 2);
                $all_odp[] = $row;
            }
        }

        usort($all_odp, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        echo '<div class="table-wrapper">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-bordered text-center align-middle">';
        echo '<thead class="table-light">
                <tr>
                    <th>Nama ODP</th>
                    <th>PON</th>
                    <th>Port</th>
                    <th>Jarak (km)</th>
                </tr>
              </thead>';
        echo '<tbody>';

        foreach ($all_odp as $odp) {
            $port_info = $odp['jumlah_user'] . '/' . $odp['port_max'];
            $port_color = 'text-success';
            if ($odp['jumlah_user'] >= $odp['port_max']) {
                $port_color = 'text-danger';
            } elseif ($odp['jumlah_user'] >= $odp['port_max'] * 0.75) {
                $port_color = 'text-warning';
            }

            echo "<tr>
                    <td>{$odp['nama_odp']}</td>
                    <td>{$odp['nama_pon']}</td>
                    <td class='$port_color'><strong>$port_info</strong></td>
                    <td>{$odp['distance']}</td>
                  </tr>";
        }

        echo '</tbody></table></div></div>';
    }
    ?>
</div>