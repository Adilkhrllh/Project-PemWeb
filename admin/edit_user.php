<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

// Ambil ID user dari URL
$id_user = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id_user <= 0){
    header("Location: dashboard.php?page=user&status=invalid_id");
    exit;
}

// Query user berdasarkan ID
$query = "SELECT * FROM users WHERE id_user = $id_user AND role = 'user'";
$result = mysqli_query($koneksi, $query);

if(mysqli_num_rows($result) == 0){
    header("Location: dashboard.php?page=user&status=user_not_found");
    exit;
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .navbar {
            background-color: #512da8;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .greeting {
            font-size: 16px;
            font-weight: bold;
        }

        .navbar .logout-btn {
            background-color: white;
            color: #512da8;
            border: none;
            padding: 8px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
        }

        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background-color: white;
            padding: 20px 0;
            border-right: 1px solid #ddd;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .sidebar a:before {
            content: '○';
            margin-right: 10px;
            font-size: 12px;
            color: #999;
        }

        .sidebar a:hover {
            background-color: #f0f0f0;
        }

        .sidebar a.active {
            background-color: #e8f8f5;
            border-left: 4px solid #512da8;
            font-weight: bold;
            color: #512da8;
        }

        .sidebar a.active:before {
            content: '●';
            color: #512da8;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            color: #333;
            font-size: 28px;
            font-weight: normal;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 14px;
        }

        .back-btn {
            display: inline-block;
            color: #512da8;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .form-card {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .required {
            color: #e74c3c;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="date"],
        .form-group input[type="tel"],
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            font-family: Arial, sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #512da8;
            box-shadow: 0 0 0 3px rgba(81, 45, 168, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-submit {
            background-color: #512da8;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #673ab7;
        }

        .btn-cancel {
            background-color: white;
            color: #555;
            border: 1px solid #ccc;
            padding: 12px 40px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background-color: #f0f0f0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .user-info {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #666;
        }

        .user-info strong {
            color: #333;
        }

        .form-help {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }

            .form-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="greeting">Hallo Admin!</div>
        <a href="../logic/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="dashboard.php?page=account">Account</a>
            <a href="tambah_artikel.php">Tambah Artikel</a>
            <a href="dashboard.php?page=artikel">Lihat Artikel</a>
            <a href="dashboard.php?page=dokter">Tabel Dokter</a>
            <a href="dashboard.php?page=user" class="active">Tabel User</a>
        </div>

        <div class="main-content">
            <a href="dashboard.php?page=user" class="back-btn">← Kembali ke Tabel User</a>

            <div class="page-header">
                <h1 class="page-title">EDIT USER / PASIEN</h1>
                <p class="page-subtitle">Perbarui data user / pasien</p>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">
                        <span style="font-size: 20px;">✓</span>
                        <span>Data user berhasil diperbarui!</span>
                    </div>
                <?php elseif($_GET['status'] == 'error'): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 20px;">✗</span>
                        <span>Gagal memperbarui data user. Silakan coba lagi.</span>
                    </div>
                <?php elseif($_GET['status'] == 'empty'): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 20px;">⚠</span>
                        <span>Nama user wajib diisi!</span>
                    </div>
                <?php elseif($_GET['status'] == 'email_exists'): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 20px;">⚠</span>
                        <span>Email sudah digunakan oleh user lain!</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="user-info">
                <strong>ID User:</strong> <?= $user['id_user'] ?> &nbsp;|&nbsp;
                <strong>Role:</strong> <?= ucfirst($user['role']) ?>
            </div>

            <div class="form-card">
                <form action="../logic/proses_edit_user.php" method="POST" id="userForm">
                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                    
                    <div class="form-group">
                        <label for="username">
                            Nama User <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Masukkan nama user" 
                            required
                            value="<?= htmlspecialchars($user['username']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            Email <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="contoh@email.com" 
                            required
                            value="<?= htmlspecialchars($user['email']) ?>">
                        <div class="form-help">Email harus unik dan belum terdaftar</div>
                    </div>

                    <div class="form-group">
                        <label for="telp_user">
                            Nomor Telepon
                        </label>
                        <input 
                            type="tel" 
                            id="telp_user" 
                            name="telp_user" 
                            placeholder="08xxxxxxxxxx"
                            value="<?= htmlspecialchars($user['telp_user'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="tgl_lahir">
                            Tanggal Lahir
                        </label>
                        <input 
                            type="date" 
                            id="tgl_lahir" 
                            name="tgl_lahir"
                            value="<?= $user['tgl_lahir'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="jk">
                            Jenis Kelamin
                        </label>
                        <select id="jk" name="jk">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" <?= $user['jk'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $user['jk'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            💾 Simpan Perubahan
                        </button>
                        <a href="dashboard.php?page=user" class="btn-cancel">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let formChanged = false;
        document.getElementById('userForm').addEventListener('input', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if(formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.getElementById('userForm').addEventListener('submit', function() {
            formChanged = false;
        });
    </script>
</body>
</html>