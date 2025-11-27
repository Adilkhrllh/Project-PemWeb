<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

// Ambil data user dan dokter dari database
$sql = "SELECT u.*, d.id_dokter, d.nama as nama_dokter, d.spesialis as spesialis_dokter, d.telp_dokter 
        FROM users u 
        LEFT JOIN dokter d ON u.id_user = d.id_user 
        WHERE u.id_user = $id_user";
$result = mysqli_query($koneksi, $sql);

if(!$result){
    die("Query Error: " . mysqli_error($koneksi));
}

if(mysqli_num_rows($result) == 0){
    die("User tidak ditemukan");
}

$user = mysqli_fetch_assoc($result);
$id_dokter = $user['id_dokter'];

// Jika id_dokter tidak ada, redirect atau tampilkan error
if(!$id_dokter){
    die("Data dokter tidak ditemukan. Silakan hubungi administrator.");
}

// Hitung jumlah konsultasi untuk DOKTER
$sql_pending = "SELECT COUNT(*) as total FROM konsultasi 
                WHERE id_dokter = $id_dokter AND status_konsul = 'pending'";
$result_pending = mysqli_query($koneksi, $sql_pending);
$pending = $result_pending ? mysqli_fetch_assoc($result_pending)['total'] : 0;

$sql_selesai = "SELECT COUNT(*) as total FROM konsultasi 
                WHERE id_dokter = $id_dokter AND status_konsul = 'selesai'";
$result_selesai = mysqli_query($koneksi, $sql_selesai);
$selesai = $result_selesai ? mysqli_fetch_assoc($result_selesai)['total'] : 0;

$sql_total = "SELECT COUNT(*) as total FROM konsultasi WHERE id_dokter = $id_dokter";
$result_total = mysqli_query($koneksi, $sql_total);
$total = $result_total ? mysqli_fetch_assoc($result_total)['total'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Dokter</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
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
            transition: all 0.3s ease;
            display: inline-block;
        }

        .top-navbar .btn-logout:hover {
            background: #f0f0f0;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: calc(100vh - 60px);
        }

        .sidebar {
            background: #f5f5f5;
            padding: 20px 0;
        }

        .sidebar-item {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        .sidebar-item:hover {
            background: #e0e0e0;
            border-left-color: #512da8;
        }

        .sidebar-item.active {
            background: white;
            border-left-color: #512da8;
            color: #512da8;
        }

        .sidebar-badge {
            background: #f44336;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .sidebar-badge.success {
            background: #4caf50;
        }

        .sidebar-badge.info {
            background: #2196f3;
        }

        .main-content {
            background: #fafafa;
            padding: 40px;
        }

        .profile-container {
            max-width: 1200px;
            background: white;
            border-radius: 12px;
            padding: 50px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .profile-header {
            display: flex;
            align-items: start;
            gap: 50px;
            margin-bottom: 50px;
        }

        .profile-avatar {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f0f0f0;
            background: linear-gradient(135deg, rgba(49, 178, 152, 0.8), #512da8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 100px;
            color: white;
            flex-shrink: 0;
        }

        .profile-info {
            flex: 1;
        }

        .profile-role {
            background: #512da8;
            color: white;
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .profile-name {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin: 0 0 10px 0;
        }

        .profile-specialty {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .profile-detail {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            color: #666;
            font-size: 16px;
        }

        .profile-detail-icon {
            width: 20px;
            display: inline-block;
            color: #999;
        }

        .btn-edit {
            margin-top: 25px;
            padding: 10px 30px;
            background: white;
            color: #333;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #f5f5f5;
            border-color: #512da8;
            color: #512da8;
        }

        .privacy-section {
            background: #512da8;
            color: white;
            padding: 15px 0;
            text-align: center;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 0;
            margin-top: 40px;
            border-radius: 8px 8px 0 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 25px 30px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            width: 150px;
        }

        .info-value {
            color: #666;
            font-size: 15px;
        }

        .btn-edit-password {
            padding: 8px 20px;
            background: white;
            color: #333;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit-password:hover {
            border-color: #512da8;
            color: #512da8;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 968px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-bottom: 2px solid #e0e0e0;
            }

            .top-navbar {
                flex-wrap: wrap;
                padding: 15px 20px;
            }

            .main-content {
                padding: 20px;
            }

            .profile-container {
                padding: 30px 20px;
            }

            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 30px;
            }

            .profile-avatar {
                width: 200px;
                height: 200px;
                font-size: 80px;
            }
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<div class="top-navbar">
    <div class="greeting">Hallo Dr. <?= htmlspecialchars($user['nama_dokter'] ?? $user['username']) ?>!</div>
    <a href="../logic/logout.php" class="btn-logout">Logout</a>
</div>

<div class="dashboard-layout">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-item active">
            <span>👤 Account</span>
        </a>
        <a href="konsultasi_pending.php" class="sidebar-item">
            <span>📋 Konsultasi Pending</span>
            <?php if($pending > 0): ?>
                <span class="sidebar-badge"><?= $pending ?></span>
            <?php endif; ?>
        </a>
        <a href="../logic/riwayat_konsultasi.php" class="sidebar-item">
            <span>✅ Konsultasi Selesai</span>
            <?php if($selesai > 0): ?>
                <span class="sidebar-badge success"><?= $selesai ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">✓ Profil berhasil diperbarui!</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'password_changed'): ?>
            <div class="alert alert-success">✓ Password berhasil diubah!</div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    👨‍⚕️
                </div>
                <div class="profile-info">
                    <span class="profile-role">DOKTER</span>
                    <h1 class="profile-name">Dr. <?= htmlspecialchars($user['nama_dokter'] ?? $user['username']) ?></h1>
                    <div class="profile-specialty">
                        Spesialis: <?= htmlspecialchars($user['spesialis_dokter'] ?? 'Umum') ?>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="profile-detail-icon">📧</span>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="profile-detail-icon">📞</span>
                        <span><?= htmlspecialchars($user['telp_dokter'] ?? $user['telp_user'] ?? 'Belum diisi') ?></span>
                    </div>
                    
                    <?php if($user['tgl_lahir']): ?>
                    <div class="profile-detail">
                        <span class="profile-detail-icon">🎂</span>
                        <span><?= date('d F Y', strtotime($user['tgl_lahir'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if($user['jk']): ?>
                    <div class="profile-detail">
                        <span class="profile-detail-icon">⚧</span>
                        <span><?= htmlspecialchars($user['jk']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <a href="edit_profile.php" class="btn-edit">Edit Profil</a>
                </div>
            </div>

            <div class="privacy-section">
                Privacy & Security
            </div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Email</td>
                    <td class="info-value"><?= htmlspecialchars($user['email']) ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="info-label">Password</td>
                    <td class="info-value">************</td>
                    <td style="text-align: right;">
                        <a href="edit_password.php" class="btn-edit-password">Edit Password</a>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">ID Dokter</td>
                    <td class="info-value">#<?= htmlspecialchars($user['id_dokter']) ?></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
</div>

</body>
</html>