<?php
session_start();
include("../config/koneksi.php");
if(isset($_GET['id'])){
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM pesan WHERE id_konsultasi=$id");
    mysqli_query($koneksi, "DELETE FROM konsultasi WHERE id_konsultasi=$id AND id_pasien=".$_SESSION['id_user']);
    header("Location: ../pasien/riwayat_konsultasi.php");
}
?>