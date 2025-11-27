<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='user'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

// Ambil daftar dokter sesuai spesialis
$sql = "SELECT d.id_dokter, d.nama, d.spesialis 
        FROM dokter d 
        JOIN users u ON d.id_user = u.id_user";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konsultasi Baru</title>
    <link rel="stylesheet" href="../css/user.css">
</head>
<body>
<div class="navbar">
    <a href="dashboard.php">HOME</a>
    <a href="../logic/logout.php">LOGOUT</a>
</div>

<h2>Buat Konsultasi Baru</h2>
<form action="../logic/buat_konsultasi.php" method="POST">
    <label>Pilih Dokter:</label>
    <select name="id_dokter" required>
        <option value="">--Pilih Dokter--</option>
        <?php while($dokter = mysqli_fetch_assoc($result)): ?>
            <option value="<?= $dokter['id_dokter'] ?>">
                <?= $dokter['nama'] ?> (<?= $dokter['spesialis'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Topik Konsultasi:</label>
    <input type="text" name="topik_konsul" required>

    <label>Pesan Awal:</label>
    <textarea name="pesan" required></textarea>

    <button type="submit" name="buat_konsultasi">Kirim</button>
</form>
</body>
</html>