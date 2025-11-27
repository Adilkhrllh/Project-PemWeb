<?php
include("../config/koneksi.php");
session_start();

if(!isset($_SESSION['id_user'])){
    header("Location: ../login.php");
    exit;
}

$id_konsultasi = $_GET['id'];
$id_user = $_SESSION['id_user'];

$cek = mysqli_query($koneksi, "SELECT * FROM konsultasi WHERE id_konsultasi='$id_konsultasi' AND id_pasien='$id_user'");

if(mysqli_num_rows($cek) > 0){
    $data = mysqli_fetch_assoc($cek);

    if($data['status_konsul'] == "pending"){
        mysqli_query($koneksi, "UPDATE konsultasi SET status_konsul='canceled' WHERE id_konsultasi='$id_konsultasi'");
        header("Location: ../user.chat.php?status=berhasil_batal");
        exit;
    } else {
        header("Location: riwayat_konsultasi.php?status=tanggal_ditolak");
        exit;
    }
}
?>