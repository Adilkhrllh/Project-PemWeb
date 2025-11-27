<?php
session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Validasi input
if(!isset($_POST['judul']) || !isset($_POST['konten'])){
    header("Location: ../admin/tambah_artikel.php?status=empty");
    exit;
}

$judul = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
$konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));

// Validasi tidak boleh kosong
if(empty($judul) || empty($konten)){
    header("Location: ../admin/tambah_artikel.php?status=empty");
    exit;
}

// Insert artikel ke database
$query = "INSERT INTO artikel (judul, konten, created_at) 
          VALUES ('$judul', '$konten', NOW())";

if(mysqli_query($koneksi, $query)){
    // Berhasil
    header("Location: ../admin/tambah_artikel.php?status=success");
    exit;
} else {
    // Gagal
    error_log("Error insert artikel: " . mysqli_error($koneksi));
    header("Location: ../admin/tambah_artikel.php?status=error");
    exit;
}
?>