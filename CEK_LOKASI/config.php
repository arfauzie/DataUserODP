<?php
// config.php
$host = "localhost";
$user = "root";       // sesuaikan username MySQL kamu
$pass = "";           // sesuaikan password MySQL kamu
$db   = "msn_db";     // database utama

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
