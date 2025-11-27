<?php
session_start();
include("../config/koneksi.php");

if(isset($_POST['buat_konsultasi'])){
    $id_pasien = $_SESSION['id_user'];
    $id_dokter = $_POST['id_dokter'];
    $topik = mysqli_real_escape_string($koneksi, $_POST['topik_konsul']);
    $pesan_awal = mysqli_real_escape_string($koneksi, $_POST['pesan']);

    $query = "INSERT INTO konsultasi (id_pasien, id_dokter, topik_konsul, status_konsul, konsultasi_date, created_at)
              VALUES ($id_pasien, $id_dokter, '$topik', 'pending', NOW(), NOW())";
    mysqli_query($koneksi, $query);
    $id_konsultasi = mysqli_insert_id($koneksi);

    mysqli_query($koneksi, "INSERT INTO pesan (id_konsultasi, pengirim, pesan, created_at)
                            VALUES ($id_konsultasi, 'pasien', '$pesan_awal', NOW())");

    header("Location: ../pasien/dashboard.php?status=konsultasi_dibuat");
    exit;
}
?>