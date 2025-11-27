<?php
session_start();
include("../config/koneksi.php");

if(!isset($_SESSION['id_user'])){
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

if(!isset($_POST['id_konsultasi']) || !isset($_POST['pesan']) || !isset($_POST['pengirim'])){
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$id_konsultasi = intval($_POST['id_konsultasi']);
$pesan = mysqli_real_escape_string($koneksi, trim($_POST['pesan']));
$pengirim = mysqli_real_escape_string($koneksi, $_POST['pengirim']); // 'dokter' atau 'pasien'

if(empty($pesan)){
    echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
    exit;
}

if($pengirim !== 'dokter' && $pengirim !== 'pasien'){
    echo json_encode(['status' => 'error', 'message' => 'Pengirim tidak valid']);
    exit;
}

$role = $_SESSION['role'];
if(($pengirim == 'dokter' && $role !== 'dokter') || ($pengirim == 'pasien' && $role !== 'user')){
    echo json_encode(['status' => 'error', 'message' => 'Role tidak sesuai']);
    exit;
}

$id_user = $_SESSION['id_user'];

if($pengirim == 'dokter'){

    $cek = mysqli_query($koneksi, "SELECT k.* FROM konsultasi k 
                                    JOIN dokter d ON k.id_dokter = d.id_dokter 
                                    WHERE k.id_konsultasi = $id_konsultasi 
                                    AND d.id_user = $id_user");
} else {
    $cek = mysqli_query($koneksi, "SELECT * FROM konsultasi 
                                    WHERE id_konsultasi = $id_konsultasi 
                                    AND id_pasien = $id_user");
}

if(!$cek || mysqli_num_rows($cek) == 0){
    echo json_encode(['status' => 'error', 'message' => 'Konsultasi tidak ditemukan atau tidak sesuai']);
    exit;
}

$query = "INSERT INTO pesan (id_konsultasi, pengirim, pesan, created_at) 
          VALUES ($id_konsultasi, '$pengirim', '$pesan', NOW())";

if(mysqli_query($koneksi, $query)){
    echo json_encode(['status' => 'success', 'message' => 'Pesan berhasil dikirim']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . mysqli_error($koneksi)]);
}
?>