<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $old_password = mysqli_real_escape_string($koneksi, $_POST['old_password']);
    $new_password = mysqli_real_escape_string($koneksi, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($koneksi, $_POST['confirm_password']);

    $check_sql = "SELECT password FROM users WHERE id_user = $id_user";
    $check_result = mysqli_query($koneksi, $check_sql);
    $current = mysqli_fetch_assoc($check_result);
    
    if($current['password'] != $old_password){
        $error = "Password lama salah!";
    } elseif($new_password != $confirm_password){
        $error = "Password baru tidak cocok!";
    } elseif(strlen($new_password) < 6){
        $error = "Password minimal 6 karakter!";
    } else {
        $update_sql = "UPDATE users SET password = '$new_password' WHERE id_user = $id_user";
        
        if(mysqli_query($koneksi, $update_sql)){
            header("Location: dashboard.php?status=password_changed");
            exit;
        } else {
            $error = "Gagal update password: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ganti Password</title>
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
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus {
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
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error::before {
            content: "⚠️";
            font-size: 18px;
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .password-requirements {
            background: #f8f9fa;
            border-left: 4px solid #512da8;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .security-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="top-navbar">
    <div class="greeting">Ganti Password</div>
    <a href="../logic/logout.php" class="btn-logout">Logout</a>
</div>

<div class="main-content">
    <div class="security-icon">🔒</div>
    <h2>Ganti Password</h2>
    <p class="subtitle">Pastikan password baru Anda kuat dan aman</p>
    
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="password-requirements">
        <h4>Persyaratan Password:</h4>
        <ul>
            <li>Minimal 6 karakter</li>
            <li>Gunakan kombinasi huruf dan angka</li>
            <li>Hindari password yang mudah ditebak</li>
        </ul>
    </div>
    
    <form method="POST" action="edit_password.php">
        <div class="form-group">
            <label>Password Lama</label>
            <input type="password" name="old_password" required placeholder="Masukkan password lama">
        </div>
        
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="new_password" required placeholder="Masukkan password baru" minlength="6">
            <div class="password-hint">Minimal 6 karakter</div>
        </div>
        
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" required placeholder="Ketik ulang password baru" minlength="6">
            <div class="password-hint">Pastikan password sama dengan yang di atas</div>
        </div>
        
        <div class="btn-container">
            <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
    </form>
</div>

</body>
</html>