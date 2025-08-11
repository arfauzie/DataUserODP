<?php

// Konfigurasi Database
$host = 'localhost';
$dbname = 'msn_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Handle tambah PON
if (isset($_POST['tambah_pon'])) {
    $nama_pon = trim(htmlspecialchars($_POST['nama_pon']));
    $port_max = (int) $_POST['port_max'];

    try {
        $stmt = $pdo->prepare("INSERT INTO pon1 (nama_pon, port_max) VALUES (?, ?)");
        if ($stmt->execute([$nama_pon, $port_max])) {
            header("Location: olt_msn.php?success=pon_added");
            exit;
        } else {
            echo "<script>alert('Gagal menambahkan PON. Silakan coba lagi.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');</script>";
    }
}
