<?php
try {
    $pdo_log = new PDO("mysql:host=localhost;dbname=msn_db", "root", "");
    $pdo_log->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Koneksi log gagal: " . $e->getMessage());
    $pdo_log = null;
}
