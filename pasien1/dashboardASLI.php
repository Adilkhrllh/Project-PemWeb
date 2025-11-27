<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = intval($_SESSION['id_user']);

// Ambil riwayat konsultasi pasien
$sql = "SELECT k.id_konsultasi, d.nama AS dokter, k.topik_konsul, k.status_konsul, k.konsultasi_date
        FROM konsultasi k
        JOIN dokter d ON k.id_dokter = d.id_dokter
        WHERE k.id_pasien = $id_user
        ORDER BY k.created_at DESC";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pasien</title>
    <link rel="stylesheet" href="../css/user.css">
</head>
<body>
<div class="mirza"></div>
<div class="adil"></div>

<div class="navbar">
    <div class="logo">Konsulin Aja</div>
    <div class="menu">
        <a href="dashboard.php">HOME</a>
        <a href="#">ABOUT</a>
        <a href="account.php">ACCOUNT</a>
        <a href="../logic/logout.php">LOGOUT</a>
    </div>
</div>

<?php if(isset($_GET['status']) && $_GET['status'] == 'konsultasi_dibuat') : ?>
<div class="alert success" id="alertBox">
    <span class="alert-icon">💬</span>
    <span class="alert-text">Konsultasi berhasil dibuat! Tunggu dokter merespon.</span>
    <button class="alert-close" onclick="closeAlert()">×</button>
</div>
<?php endif; ?>

<div class="hero">
    <h1>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
</div>

<div class="menu-container">
    <div class="card">
        <h3>CHAT DOKTER</h3>
        <p>Tanyakan keluhan anda</p>
        <a href="konsultasi_add.php" class="btn btn-primary">Mulai</a>
    </div>

    <div class="card">
        <h3>PESAN TERAKHIR</h3>
        <p>Lihat semua pesan dokter</p>
        <a href="riwayat_konsultasi.php" class="btn btn-primary">Buka</a>
    </div>

    <div class="card">
        <h3>ARTIKEL</h3>
        <p>Baca info kesehatan</p>
        <a href="../artikel.php" class="btn btn-primary">Lihat</a>
    </div>
</div>

<script>
function closeAlert() {
    let alert = document.getElementById("alertBox");
    alert.style.animation = "fadeOut 0.5s forwards";
    setTimeout(() => alert.remove(), 500);
}

// Auto close after 4s
setTimeout(() => {
    if(document.getElementById("alertBox")) {
        closeAlert();
    }
}, 4000);
</script>

</body>
</html>
