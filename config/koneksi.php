<!-- KONEKSI.PHP -->
<?php
$host = "localhost";
$user = "root";
$pass = "";
$database = "klinik_bantul";

$koneksi = mysqli_connect($host, $user, $pass, $database) or die("koneksi gagal");
?>