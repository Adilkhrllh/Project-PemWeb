<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../admin/dashboard.php?page=dokter&status=error");
    exit;
}

$id_dokter = mysqli_real_escape_string($koneksi, $_GET['id']);

$query_get = mysqli_query($koneksi, "SELECT id_user FROM dokter WHERE id_dokter='$id_dokter'");

if(mysqli_num_rows($query_get) == 0) {
    header("Location: ../admin/dashboard.php?page=dokter&status=not_found");
    exit;
}

$data = mysqli_fetch_assoc($query_get);
$id_user = $data['id_user'];

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Hapus dari tabel dokter terlebih dahulu
    $query_delete_dokter = "DELETE FROM dokter WHERE id_dokter='$id_dokter'";
    if(!mysqli_query($koneksi, $query_delete_dokter)) {
        throw new Exception("Gagal menghapus dari tabel dokter");
    }
    
    // Hapus dari tabel users
    $query_delete_user = "DELETE FROM users WHERE id_user='$id_user'";
    if(!mysqli_query($koneksi, $query_delete_user)) {
        throw new Exception("Gagal menghapus dari tabel users");
    }
    
    // Commit transaksi jika berhasil
    mysqli_commit($koneksi);
    header("Location: ../admin/dashboard.php?page=dokter&status=deleted");
    exit;
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($koneksi);
    header("Location: ../admin/dashboard.php?page=dokter&status=delete_error");
    exit;
}
?>