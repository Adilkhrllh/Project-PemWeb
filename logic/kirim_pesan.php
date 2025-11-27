<?php
session_start();
include("../config/koneksi.php");

if(isset($_POST['id_konsultasi']) && isset($_POST['pesan'])){
    $id_konsultasi = intval($_POST['id_konsultasi']);
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan']);
    $id_user = $_SESSION['id_user'];

    $cek = mysqli_query($koneksi, "SELECT * FROM konsultasi WHERE id_konsultasi=$id_konsultasi AND id_pasien=$id_user");
    if(mysqli_num_rows($cek)==0) exit;

    mysqli_query($koneksi, "INSERT INTO pesan (id_konsultasi, pengirim, pesan, created_at)
                            VALUES ($id_konsultasi, 'pasien', '$pesan', NOW())");
}
?>
