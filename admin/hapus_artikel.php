<?php
session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Validasi input
if(!isset($_GET['id'])){
    header("Location: ../admin/dashboard.php?page=artikel&status=error");
    exit;
}

$id_artikel = intval($_GET['id']);

// Hapus artikel dari database
$query = "DELETE FROM artikel WHERE id_artikel = $id_artikel";

if(mysqli_query($koneksi, $query)){
    // Berhasil
    header("Location: ../admin/dashboard.php?page=artikel&status=deleted");
    exit;
} else {
    // Gagal
    error_log("Error hapus artikel: " . mysqli_error($koneksi));
    header("Location: ../admin/dashboard.php?page=artikel&status=delete_error");
    exit;
}
?>