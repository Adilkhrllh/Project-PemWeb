<?php
session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Validasi input
if(!isset($_POST['id_artikel']) || !isset($_POST['judul']) || !isset($_POST['konten'])){
    header("Location: ../admin/dashboard.php?page=artikel&status=error");
    exit;
}

$id_artikel = intval($_POST['id_artikel']);
$judul = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
$konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));

if(empty($judul) || empty($konten)){
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=empty");
    exit;
}

$query = "UPDATE artikel 
          SET judul = '$judul', 
              konten = '$konten'
          WHERE id_artikel = $id_artikel";

if(mysqli_query($koneksi, $query)){
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=success");
    exit;
} else {
    error_log("Error update artikel: " . mysqli_error($koneksi));
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=error");
    exit;
}
?>