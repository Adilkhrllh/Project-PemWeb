<?php
session_start();
include("../config/koneksi.php");
if($_SESSION['role']!=='admin') exit;

if(isset($_GET['id'])){
    $id_user = intval($_GET['id']);
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user=$id_user AND role='pasien'");
    header("Location: ../admin/dashboard.php");
    exit;
}
?>
