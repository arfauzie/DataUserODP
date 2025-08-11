<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'msn_db';

try {
    $pdo_log = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo_log->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi log gagal: " . $e->getMessage());
}
