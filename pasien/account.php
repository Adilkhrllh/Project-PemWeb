<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

$id_user = $_SESSION['id_user'];
$sql = "SELECT * FROM users WHERE id_user = $id_user";
$query = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_assoc($query);

$edit_mode = isset($_GET['edit']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    <link rel="stylesheet" href="../css/styleAccountUser.css">
</head>
<body>
    <div class="mirza"></div>
    <div class="adil"></div>

<div class="navbar">
    <div class="logo">Konsulin Aja</div>
    <div>
        <a href="dashboard.php">HOME</a>
        <a href="../about.php">ABOUT</a>
        <a href="account.php"><b>ACCOUNT</b></a>
        <a href="../logic/logout.php">LOGOUT</a>
    </div>
</div>

<div class="profile-container">
    <div class="profile-picture">👤</div>
    <h2>Pengaturan Akun</h2>

    <?php if (!$edit_mode): ?>

        <div class="profile-info">
            <p><b>Username:</b> <?= $data['username'] ?></p>
            <p><b>Tanggal Lahir:</b> <?= $data['tgl_lahir'] ?></p>
            <p><b>No. Telepon:</b> <?= $data['telp_user'] ?></p>
            <p><b>Jenis Kelamin:</b> <?= $data['jk'] ?></p>
        </div>

        <a href="account.php?edit=1">
            <button class="btn btn-edit">Edit Profil</button>
        </a>

    <?php else: ?>

        <form action="../logic/update_account.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= $data['username'] ?>" required>

            <label>Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" value="<?= $data['tgl_lahir'] ?>">

            <label>Telepon</label>
            <input type="text" name="telp_user" value="<?= $data['telp_user'] ?>">

            <label>Jenis Kelamin</label>
            <select name="jk">
                <option value="">-- Pilih --</option>
                <option value="Laki-laki" <?= ($data['jk']=="Laki-laki" ? "selected" : "") ?>>Laki-laki</option>
                <option value="Perempuan" <?= ($data['jk']=="Perempuan" ? "selected" : "") ?>>Perempuan</option>
            </select>

            <button type="submit" class="btn btn-save">Simpan Perubahan</button>
        </form>

        <a href="account.php">
            <button class="btn btn-back">Kembali</button>
        </a>

    <?php endif; ?>
</div>

</body>
</html>
