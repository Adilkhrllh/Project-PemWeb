<?php
session_start();

// Cek apakah user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Cek apakah form disubmit dengan method POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../admin/tambah_artikel.php");
    exit;
}

// Include koneksi database - sesuaikan dengan path Anda
// Coba beberapa kemungkinan path koneksi
if(file_exists('../config/koneksi.php')) {
    require_once '../config/koneksi.php';
    $conn = $koneksi; // Sesuaikan dengan nama variabel di koneksi.php
} elseif(file_exists('koneksi.php')) {
    require_once 'koneksi.php';
    // $conn sudah didefinisikan di koneksi.php
} else {
    die("File koneksi database tidak ditemukan!");
}

// Ambil data dari form dan bersihkan
$judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$konten = isset($_POST['konten']) ? trim($_POST['konten']) : '';

// Validasi input kosong
if(empty($judul) || empty($konten)){
    header("Location: ../admin/tambah_artikel.php?status=empty");
    exit;
}

// Escape karakter khusus untuk keamanan
$judul = mysqli_real_escape_string($conn, $judul);
$konten = mysqli_real_escape_string($conn, $konten);

// Query untuk insert artikel
$query = "INSERT INTO artikel (judul, konten, created_at) VALUES ('$judul', '$konten', NOW())";

// Eksekusi query
if(mysqli_query($conn, $query)){
    // Berhasil
    mysqli_close($conn);
    header("Location: ../admin/tambah_artikel.php?status=success");
    exit;
} else {
    // Gagal - tampilkan error untuk debugging
    $error = mysqli_error($conn);
    mysqli_close($conn);
    
    // Untuk production, gunakan ini:
    header("Location: ../admin/tambah_artikel.php?status=error");
    
    // Untuk debugging, uncomment baris di bawah:
    // die("Error: " . $error . "<br>Query: " . $query);
    
    exit;
}
?>