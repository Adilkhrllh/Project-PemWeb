<?php
session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Ambil ID user dari parameter GET
$id_user = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID
if($id_user <= 0){
    header("Location: ../admin/dashboard.php?page=user&status=invalid_id");
    exit;
}

// Cek apakah user dengan ID tersebut ada dan role-nya user (bukan admin/dokter)
$check_query = "SELECT id_user, username, role FROM users WHERE id_user = $id_user";
$check_result = mysqli_query($koneksi, $check_query);

if(mysqli_num_rows($check_result) == 0){
    header("Location: ../admin/dashboard.php?page=user&status=user_not_found");
    exit;
}

$user = mysqli_fetch_assoc($check_result);

// Pastikan yang dihapus adalah user biasa (pasien), bukan admin atau dokter
if($user['role'] !== 'user'){
    header("Location: ../admin/dashboard.php?page=user&status=not_allowed");
    exit;
}

// Pastikan tidak menghapus diri sendiri (jika admin login sebagai user)
if($id_user == $_SESSION['id_user']){
    header("Location: ../admin/dashboard.php?page=user&status=cannot_delete_self");
    exit;
}

// Mulai transaction untuk memastikan data konsisten
mysqli_begin_transaction($koneksi);

try {
    // Hapus data terkait di tabel pesan terlebih dahulu
    // Ambil semua konsultasi dari user ini
    $konsultasi_query = "SELECT id_konsultasi FROM konsultasi WHERE id_pasien = $id_user";
    $konsultasi_result = mysqli_query($koneksi, $konsultasi_query);
    
    while($konsul = mysqli_fetch_assoc($konsultasi_result)){
        $id_konsultasi = $konsul['id_konsultasi'];
        // Hapus pesan terkait konsultasi ini
        mysqli_query($koneksi, "DELETE FROM pesan WHERE id_konsultasi = $id_konsultasi");
    }
    
    // Hapus data konsultasi user
    mysqli_query($koneksi, "DELETE FROM konsultasi WHERE id_pasien = $id_user");
    
    // Hapus user dari tabel users
    $delete_query = "DELETE FROM users WHERE id_user = $id_user AND role = 'user'";
    
    if(mysqli_query($koneksi, $delete_query)){
        // Cek apakah ada baris yang terhapus
        $affected_rows = mysqli_affected_rows($koneksi);
        
        if($affected_rows > 0){
            // Commit transaction
            mysqli_commit($koneksi);
            mysqli_close($koneksi);
            
            // Redirect dengan status success
            header("Location: ../admin/dashboard.php?page=user&status=deleted");
            exit;
        } else {
            // Rollback jika tidak ada yang terhapus
            mysqli_rollback($koneksi);
            mysqli_close($koneksi);
            
            header("Location: ../admin/dashboard.php?page=user&status=delete_failed");
            exit;
        }
    } else {
        // Rollback jika query gagal
        mysqli_rollback($koneksi);
        throw new Exception(mysqli_error($koneksi));
    }
    
} catch (Exception $e) {
    // Rollback transaction jika ada error
    mysqli_rollback($koneksi);
    
    // Log error
    error_log("Error hapus user ID $id_user: " . $e->getMessage());
    
    mysqli_close($koneksi);
    
    // Redirect dengan status error
    header("Location: ../admin/dashboard.php?page=user&status=delete_error");
    exit;
}
?>