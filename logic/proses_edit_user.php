<?php
session_start();
include("../config/koneksi.php");

// Pastikan user adalah admin
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

// Cek apakah form disubmit dengan method POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../admin/dashboard.php?page=user");
    exit;
}

// Validasi input
if(!isset($_POST['id_user']) || !isset($_POST['username']) || !isset($_POST['email'])){
    header("Location: ../admin/dashboard.php?page=user&status=missing_data");
    exit;
}

// Ambil data dari form
$id_user = intval($_POST['id_user']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$telp_user = isset($_POST['telp_user']) ? trim($_POST['telp_user']) : null;
$tgl_lahir = isset($_POST['tgl_lahir']) && !empty($_POST['tgl_lahir']) ? $_POST['tgl_lahir'] : null;
$jk = isset($_POST['jk']) && !empty($_POST['jk']) ? $_POST['jk'] : null;

// Validasi ID user
if($id_user <= 0){
    header("Location: ../admin/dashboard.php?page=user&status=invalid_id");
    exit;
}

// Validasi input kosong
if(empty($username) || empty($email)){
    header("Location: ../admin/edit_user.php?id=$id_user&status=empty");
    exit;
}

// Validasi format email
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header("Location: ../admin/edit_user.php?id=$id_user&status=invalid_email");
    exit;
}

// Cek apakah user dengan ID tersebut ada dan role-nya user
$check_query = "SELECT id_user, email FROM users WHERE id_user = $id_user AND role = 'user'";
$check_result = mysqli_query($koneksi, $check_query);

if(mysqli_num_rows($check_result) == 0){
    header("Location: ../admin/dashboard.php?page=user&status=user_not_found");
    exit;
}

$existing_user = mysqli_fetch_assoc($check_result);

// Cek apakah email sudah digunakan oleh user lain
$email_check = "SELECT id_user FROM users WHERE email = '" . mysqli_real_escape_string($koneksi, $email) . "' AND id_user != $id_user";
$email_result = mysqli_query($koneksi, $email_check);

if(mysqli_num_rows($email_result) > 0){
    header("Location: ../admin/edit_user.php?id=$id_user&status=email_exists");
    exit;
}

// Escape karakter khusus untuk keamanan
$username = mysqli_real_escape_string($koneksi, $username);
$email = mysqli_real_escape_string($koneksi, $email);
$telp_user = $telp_user ? mysqli_real_escape_string($koneksi, $telp_user) : null;

// Build query update
$query = "UPDATE users SET 
          username = '$username', 
          email = '$email'";

// Tambahkan field opsional jika ada
if($telp_user !== null && $telp_user !== ''){
    $query .= ", telp_user = '$telp_user'";
} else {
    $query .= ", telp_user = NULL";
}

if($tgl_lahir !== null){
    $query .= ", tgl_lahir = '$tgl_lahir'";
} else {
    $query .= ", tgl_lahir = NULL";
}

if($jk !== null){
    $jk_escaped = mysqli_real_escape_string($koneksi, $jk);
    $query .= ", jk = '$jk_escaped'";
} else {
    $query .= ", jk = NULL";
}

$query .= " WHERE id_user = $id_user";

// Eksekusi query
if(mysqli_query($koneksi, $query)){
    // Berhasil update
    $affected_rows = mysqli_affected_rows($koneksi);
    
    mysqli_close($koneksi);
    
    if($affected_rows > 0){
        // Ada data yang berubah
        header("Location: ../admin/edit_user.php?id=$id_user&status=success");
    } else {
        // Tidak ada data yang berubah
        header("Location: ../admin/edit_user.php?id=$id_user&status=no_changes");
    }
    exit;
} else {
    // Gagal update
    $error = mysqli_error($koneksi);
    error_log("Error update user ID $id_user: $error");
    
    mysqli_close($koneksi);
    
    header("Location: ../admin/edit_user.php?id=$id_user&status=error");
    exit;
}
?>