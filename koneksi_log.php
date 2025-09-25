<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "msn_db"; // sesuai database kamu di phpMyAdmin

try {
    $pdo_log = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo_log->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database log gagal: " . $e->getMessage());
}
