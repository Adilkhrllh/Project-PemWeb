<?php
session_start();
include("../config/koneksi.php");

if (!isset($_SESSION['id_user']) || !isset($_POST['send_message'])) {
    header("Location: ../login.php?status=login_dulu");
    exit;
}

$id_konsultasi = mysqli_real_escape_string($koneksi, $_POST['id_konsultasi']);
$id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
$pesan_teks = mysqli_real_escape_string($koneksi, $_POST['pesan_teks']);
$pesan_attachment = NULL;

if (!empty($pesan_teks)) {
    $check_status = mysqli_query($koneksi, "SELECT status_konsul FROM konsultasi WHERE id_konsultasi = '$id_konsultasi' AND id_pasien = '$id_user'");
    $status_row = mysqli_fetch_assoc($check_status);

    if ($status_row && ($status_row['status_konsul'] == 'pending' || $status_row['status_konsul'] == 'process')) {

        $sql_insert = "INSERT INTO pesan (id_konsultasi, id_user, pesan_teks, pesan_attachment) 
                       VALUES ('$id_konsultasi', '$id_user', '$pesan_teks', " . ($pesan_attachment ? "'$pesan_attachment'" : "NULL") . ")";
        
        if (mysqli_query($koneksi, $sql_insert)) {
            if ($status_row['status_konsul'] == 'pending') {
                 mysqli_query($koneksi, "UPDATE konsultasi SET status_konsul = 'process' WHERE id_konsultasi = '$id_konsultasi'");
            }
            
            header("Location: chat.php?id_konsultasi=$id_konsultasi");
            exit;
        } else {
            echo "Error: " . mysqli_error($koneksi);
        }
        
    } else {
        header("Location: chat.php?id_konsultasi=$id_konsultasi");
        exit;
    }
} else {
    header("Location: chat.php?id_konsultasi=$id_konsultasi");
    exit;
}
?>