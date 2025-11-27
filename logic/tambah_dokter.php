<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $telp_dokter = mysqli_real_escape_string($koneksi, trim($_POST['telp_dokter']));
    $spesialis = mysqli_real_escape_string($koneksi, trim($_POST['spesialis']));

    if(empty($nama) || empty($email) || empty($password) || empty($telp_dokter) || empty($spesialis)) {
        header("Location: ../admin/dashboard.php?page=dokter&status=empty");
        exit;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/dashboard.php?page=dokter&status=invalid_email");
        exit;
    }

    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($cek_email) > 0) {
        header("Location: ../admin/dashboard.php?page=dokter&status=email_exists");
        exit;
    }

    mysqli_begin_transaction($koneksi);
    
    try {
        $query_user = "INSERT INTO users (username, email, password, telp_user, spesialis, role) 
                       VALUES ('$nama', '$email', '$password', '$telp_dokter', '$spesialis', 'dokter')";
        
        if(!mysqli_query($koneksi, $query_user)) {
            throw new Exception("Gagal insert ke tabel users");
        }

        $id_user = mysqli_insert_id($koneksi);
        
        // Insert ke tabel dokter
        $query_dokter = "INSERT INTO dokter (nama, spesialis, telp_dokter, id_user) 
                         VALUES ('$nama', '$spesialis', '$telp_dokter', '$id_user')";
        
        if(!mysqli_query($koneksi, $query_dokter)) {
            throw new Exception("Gagal insert ke tabel dokter");
        }

        mysqli_commit($koneksi);
        header("Location: ../admin/dashboard.php?page=dokter&status=success");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: ../admin/dashboard.php?page=dokter&status=error");
        exit;
    }
    
} else {
    header("Location: ../admin/dashboard.php?page=dokter");
    exit;
}
?>