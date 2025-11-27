<?php
session_start();
include("../config/koneksi.php");
if(isset($_GET['id'])){
    $id = $_GET['id'];
    mysqli_query($koneksi, "UPDATE konsultasi SET status_konsul='dibatalkan' WHERE id_konsultasi=$id AND id_pasien=".$_SESSION['id_user']);
    header("Location: ../pasien/riwayat_konsultasi.php");
}
?>