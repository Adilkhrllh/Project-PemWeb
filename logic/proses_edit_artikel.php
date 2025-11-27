<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Cek apakah form disubmit dengan method POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../admin/dashboard.php?page=artikel");
    exit;
}

// Validasi input - pastikan semua field ada
if(!isset($_POST['id_artikel']) || !isset($_POST['judul']) || !isset($_POST['konten'])){
    header("Location: ../admin/dashboard.php?page=artikel&status=missing_data");
    exit;
}

// Ambil dan validasi ID artikel
$id_artikel = intval($_POST['id_artikel']);

if($id_artikel <= 0){
    header("Location: ../admin/dashboard.php?page=artikel&status=invalid_id");
    exit;
}

// Ambil data dari form
$judul = trim($_POST['judul']);
$konten = trim($_POST['konten']);

// Validasi input kosong
if(empty($judul) || empty($konten)){
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=empty");
    exit;
}

// Validasi panjang judul
if(strlen($judul) > 200) {
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=judul_terlalu_panjang");
    exit;
}

// Cek apakah artikel dengan ID tersebut ada
$check_query = "SELECT id_artikel FROM artikel WHERE id_artikel = $id_artikel";
$check_result = mysqli_query($koneksi, $check_query);

if(mysqli_num_rows($check_result) == 0){
    header("Location: ../admin/dashboard.php?page=artikel&status=artikel_not_found");
    exit;
}

// Escape karakter khusus untuk keamanan
$judul = mysqli_real_escape_string($koneksi, $judul);
$konten = mysqli_real_escape_string($koneksi, $konten);

// Query update artikel
$query = "UPDATE artikel 
          SET judul = '$judul', 
              konten = '$konten'
          WHERE id_artikel = $id_artikel";

// Eksekusi query
if(mysqli_query($koneksi, $query)){
    // Berhasil update
    $affected_rows = mysqli_affected_rows($koneksi);
    
    mysqli_close($koneksi);
    
    if($affected_rows > 0){
        // Ada data yang berubah
        header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=success");
    } else {
        // Tidak ada data yang berubah (data sama)
        header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=no_changes");
    }
    exit;
} else {
    // Gagal update
    $error = mysqli_error($koneksi);
    $errno = mysqli_errno($koneksi);
    
    // Log error ke file
    error_log("Error update artikel ID $id_artikel: [$errno] $error");
    
    mysqli_close($koneksi);
    
    // Untuk debugging - UNCOMMENT untuk melihat error detail
    // die("Error [$errno]: " . htmlspecialchars($error) . "<br><br>Query: " . htmlspecialchars($query));
    
    // Untuk production - COMMENT line di atas dan UNCOMMENT line di bawah
    header("Location: ../admin/edit_artikel.php?id=$id_artikel&status=error&errno=$errno");
    exit;
}
?>