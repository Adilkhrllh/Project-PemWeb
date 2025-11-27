<?php
include("../config/koneksi.php");
session_start();

if($_SESSION['role'] != 'dokter'){
    die("Akses ditolak!");
}

$id = $_GET['id'];
$status = $_GET['status'];

mysqli_query($koneksi, "UPDATE konsultasi SET status_konsul='$status' WHERE id_konsultasi='$id'");

header("Location: konsultasi_dokter.php");
?>
