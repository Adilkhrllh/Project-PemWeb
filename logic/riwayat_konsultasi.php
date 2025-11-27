<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

$query_dokter = mysqli_query($koneksi, "SELECT id_dokter, nama FROM dokter WHERE id_user=$id_user");
if(!$query_dokter || mysqli_num_rows($query_dokter) == 0){
    die("Data dokter tidak ditemukan.");
}
$data_dokter = mysqli_fetch_assoc($query_dokter);
$id_dokter = $data_dokter['id_dokter'];
$nama_dokter = $data_dokter['nama'];

$query = "SELECT k.*, u.username as nama_pasien, u.email as email_pasien, u.telp_user as telp_pasien
          FROM konsultasi k
          JOIN users u ON k.id_pasien = u.id_user
          WHERE k.id_dokter = $id_dokter
          ORDER BY k.konsultasi_date DESC";
$result = mysqli_query($koneksi, $query);

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
    <title>Riwayat Konsultasi - Dokter</title>
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
        }

        .top-navbar .btn-logout:hover {
            background: #512da8;
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

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
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

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #512da8;
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-selesai {
            background: #d4edda;
            color: #155724;
        }

        .btn-chat {
            padding: 6px 15px;
            background: #512da8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-chat:hover {
            background: #16a085;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state-text {
            font-size: 20px;
            color: #666;
            font-weight: 600;
        }

        @media (max-width: 968px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-bottom: 2px solid #e0e0e0;
            }

            .main-content {
                padding: 20px;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>

<div class="top-navbar">
    <div class="greeting">Hallo Dr. <?= htmlspecialchars($nama_dokter) ?>!</div>
    <a href="../logic/logout.php" class="btn-logout">Logout</a>
</div>

<div class="dashboard-layout">
    <div class="sidebar">
        <a href="../dokter/konsultasi_pending.php" class="sidebar-item">
            <span>👤 Account</span>
        </a>
        <a href="../dokter/konsultasi_pending.php" class="sidebar-item">
            <span>📋 Konsultasi Pending</span>
            <?php if($pending > 0): ?>
                <span class="sidebar-badge"><?= $pending ?></span>
            <?php endif; ?>
        </a>
        <a href="riwayat_konsultasi.php" class="sidebar-item active">
            <span>✅ Riwayat Konsultasi</span>
        </a>
    </div>

    <div class="main-content">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">✓ Konsultasi berhasil ditandai sebagai selesai!</div>
        <?php endif; ?>

        <h1 class="page-title">📜 Riwayat Konsultasi</h1>

        <?php if($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pasien</th>
                            <th>Topik</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)): 
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_pasien']) ?></strong>
                                    <?php if($row['telp_pasien']): ?>
                                        <br><small style="color: #999;">📞 <?= htmlspecialchars($row['telp_pasien']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['topik_konsul']) ?></td>
                                <td><?= htmlspecialchars($row['email_pasien']) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($row['konsultasi_date'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $row['status_konsul'] ?>">
                                        <?= strtoupper($row['status_konsul']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../dokter/chat.php?id=<?= $row['id_konsultasi'] ?>" class="btn-chat">
                                        💬 Chat
                                    </a>
                                    <?php if($row['status_konsul'] == 'pending'): ?>
                                        <a href="../logic/selesai_konsultasi_dokter.php?id=<?= $row['id_konsultasi'] ?>" 
                                           class="btn-chat" 
                                           style="background: #4caf50; margin-left: 5px;"
                                           onclick="return confirm('Tandai sebagai selesai?')">
                                            ✓
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-text">Belum ada riwayat konsultasi</div>
                    <p style="color: #999; margin-top: 10px;">Konsultasi akan muncul di sini</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>