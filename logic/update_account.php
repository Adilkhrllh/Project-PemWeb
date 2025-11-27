<?php
session_start();
include("../config/koneksi.php");

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php?status=login_dulu");
    exit;
}

$id_user = $_SESSION['id_user'];

$username   = $_POST['username'];
$tgl_lahir  = $_POST['tgl_lahir'];
$telp_user    = $_POST['telp_user'];
$jk         = $_POST['jk'];

$sql = "UPDATE users SET 
            username = '$username',
            tgl_lahir = '$tgl_lahir',
            telp_user = '$telp_user',
            jk = '$jk'
        WHERE id_user = '$id_user'";

$query = mysqli_query($koneksi, $sql);

if ($query) {
    $_SESSION['username'] = $username;
    header("Location: ../pasien/account.php?status=berhasil");
} else {
    header("Location: ../pasien/account.php?status=gagal");
}
exit;
