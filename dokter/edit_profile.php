<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

// Ambil data user
$sql = "SELECT * FROM users WHERE id_user = $id_user";
$result = mysqli_query($koneksi, $sql);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $tgl_lahir = !empty($_POST['tgl_lahir']) ? mysqli_real_escape_string($koneksi, $_POST['tgl_lahir']) : NULL;
    $telp_user = !empty($_POST['telp_user']) ? mysqli_real_escape_string($koneksi, $_POST['telp_user']) : NULL;
    $jk = !empty($_POST['jk']) ? mysqli_real_escape_string($koneksi, $_POST['jk']) : NULL;
    
    // Build update query untuk users
    $update_sql = "UPDATE users SET username = '$username'";
    
    if($tgl_lahir !== NULL){
        $update_sql .= ", tgl_lahir = '$tgl_lahir'";
    }
    
    if($telp_user !== NULL){
        $update_sql .= ", telp_user = '$telp_user'";
    }
    
    if($jk !== NULL){
        $update_sql .= ", jk = '$jk'";
    }
    
    $update_sql .= " WHERE id_user = $id_user";
    
    // Execute update users
    if(mysqli_query($koneksi, $update_sql)){
        // Update juga di tabel dokter jika ada
        if($_SESSION['role'] == 'dokter'){
            $update_dokter = "UPDATE dokter SET nama = '$username'";
            
            if($telp_user !== NULL){
                $update_dokter .= ", telp_dokter = '$telp_user'";
            }
            
            $update_dokter .= " WHERE id_user = $id_user";
            
            // Execute update dokter
            if(mysqli_query($koneksi, $update_dokter)){
                header("Location: dashboard.php?status=success");
                exit;
            } else {
                $error = "Gagal update tabel dokter: " . mysqli_error($koneksi);
            }
        } else {
            header("Location: dashboard.php?status=success");
            exit;
        }
    } else {
        $error = "Gagal update profil: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
    <link rel="stylesheet" href="../css/user.css">
    <style>
        body {
            margin: 0;
            background: #fafafa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .top-navbar {
            width: 100%;
            background: #512da8;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .top-navbar .greeting {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        .top-navbar .btn-logout {
            background: white;
            color: #512da8;
            padding: 8px 25px;
            border: none;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .main-content {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #512da8;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #512da8;
            color: white;
        }

        .btn-primary:hover {
            background: #16a085;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .error {
            background: #ffe0e0;
            color: #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="top-navbar">
    <div class="greeting">Edit Profil</div>
    <a href="../logic/logout.php" class="btn-logout">Logout</a>
</div>

<div class="main-content">
    <h2>Edit Profil Anda</h2>
    
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" action="edit_profile.php">
        <div class="form-group">
            <label>Username / Nama</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email (tidak bisa diubah)</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: #f5f5f5;">
        </div>
        
        <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" value="<?= $user['tgl_lahir'] ?>">
        </div>
        
        <div class="form-group">
            <label>No. Telepon</label>
            <input type="tel" name="telp_user" value="<?= htmlspecialchars($user['telp_user']) ?>" placeholder="08xxxxxxxxxx">
        </div>
        
        <div class="form-group">
            <label>Jenis Kelamin</label>
            <select name="jk">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="Laki-laki" <?= $user['jk'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="Perempuan" <?= $user['jk'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>
        
        <div class="btn-container">
            <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

</body>
</html>