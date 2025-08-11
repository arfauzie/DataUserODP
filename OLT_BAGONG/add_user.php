<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /DataUserODP/login.php");
    exit();
}
require 'config2.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['odp_id'], $_POST['nama_user'])) {
        $odp_id = $_POST['odp_id'];
        $nama_user = $_POST['nama_user'];

        $stmt = $pdo2->prepare("INSERT INTO users (odp_id, nama_user) VALUES (?, ?)");
        $stmt->execute([$odp_id, $nama_user]);

        header("Location: olt_bagong.php?odp_id=$odp_id");
        exit();
    } else {
        echo "Data tidak lengkap!";
    }
}
?>
