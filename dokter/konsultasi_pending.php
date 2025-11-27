<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

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

if(!$id_dokter){
    die("Data dokter tidak ditemukan. Silakan hubungi administrator.");
}

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

$sql_konsultasi = "SELECT k.*, u.username as nama_pasien, u.email as email_pasien, u.telp_user, u.jk, u.tgl_lahir,
                   (SELECT COUNT(*) FROM pesan WHERE id_konsultasi = k.id_konsultasi) as jumlah_pesan
                   FROM konsultasi k
                   JOIN users u ON k.id_pasien = u.id_user
                   WHERE k.id_dokter = $id_dokter AND k.status_konsul = 'pending'
                   ORDER BY k.konsultasi_date DESC";
$result_konsultasi = mysqli_query($koneksi, $sql_konsultasi);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi Pending</title>
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

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0 0 10px 0;
        }

        .page-subtitle {
            font-size: 14px;
            color: #666;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table thead {
            background: #f8f9fa;
        }

        .data-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }

        .data-table td {
            padding: 18px 15px;
            color: #666;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-chat {
            padding: 8px 16px;
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-right: 5px;
        }

        .btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }

        .btn-selesai {
            padding: 8px 16px;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-selesai:hover {
            background: #38a169;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #666;
            margin: 0 0 10px 0;
        }

        .empty-state p {
            font-size: 14px;
            color: #999;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .patient-details {
            flex: 1;
        }

        .patient-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .patient-meta {
            font-size: 12px;
            color: #999;
        }

        .message-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            color: #666;
        }

        .urgent-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-left: 5px;
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

            .content-card {
                padding: 20px;
            }

            .data-table {
                font-size: 13px;
            }

            .data-table th, .data-table td {
                padding: 12px 10px;
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
        <a href="dashboard.php" class="sidebar-item">
            <span>👤 Account</span>
        </a>
        <a href="konsultasi_pending.php" class="sidebar-item active">
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
        <div class="page-header">
            <h1 class="page-title">📋 Konsultasi Pending</h1>
            <p class="page-subtitle">Daftar konsultasi yang menunggu untuk ditangani</p>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">✓ Konsultasi berhasil ditandai selesai!</div>
        <?php endif; ?>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'error'): ?>
            <div class="alert alert-error">✗ Gagal menandai konsultasi selesai. Silakan coba lagi.</div>
        <?php endif; ?>

        <div class="content-card">
            <div class="table-container">
                <?php if(mysqli_num_rows($result_konsultasi) > 0): ?>
                    <table class="data-table" id="konsultasiTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Pasien</th>
                                <th>Topik Konsultasi</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="width: 100px;">Pesan</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 200px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result_konsultasi)): 
                                // Hitung umur jika ada tanggal lahir
                                $umur = '';
                                if($row['tgl_lahir']){
                                    $birthDate = new DateTime($row['tgl_lahir']);
                                    $today = new DateTime();
                                    $age = $birthDate->diff($today);
                                    $umur = $age->y . ' th';
                                }
                                
                                // Cek apakah konsultasi urgent (lebih dari 1 hari belum ditangani)
                                $konsulDate = new DateTime($row['konsultasi_date']);
                                $now = new DateTime();
                                $diff = $konsulDate->diff($now);
                                $isUrgent = $diff->days >= 1;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="patient-info">
                                        <div class="patient-avatar">
                                            <?= strtoupper(substr($row['nama_pasien'], 0, 1)) ?>
                                        </div>
                                        <div class="patient-details">
                                            <div class="patient-name">
                                                <?= htmlspecialchars($row['nama_pasien']) ?>
                                                <?php if($isUrgent): ?>
                                                    <span class="urgent-badge">🔥 URGENT</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="patient-meta">
                                                <?php if($row['jk']): ?>
                                                    <?= $row['jk'] == 'Laki-laki' ? '♂' : '♀' ?> 
                                                <?php endif; ?>
                                                <?= $umur ? $umur : '' ?>
                                                <?php if($row['telp_user']): ?>
                                                    | <?= htmlspecialchars($row['telp_user']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['topik_konsul']) ?></td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <?= date('d M Y', strtotime($row['konsultasi_date'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #999; margin-top: 2px;">
                                        <?= date('H:i', strtotime($row['konsultasi_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="message-count">
                                        💬 <?= $row['jumlah_pesan'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-pending">
                                        ⏳ PENDING
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="chat.php?id=<?= $row['id_konsultasi'] ?>" class="btn-chat">
                                        💬 Balas
                                    </a>
                                    <a href="../logic/selesai_konsultasi_dokter.php?id=<?= $row['id_konsultasi'] ?>" 
                                       class="btn-selesai"
                                       onclick="return confirm('Apakah Anda yakin ingin menandai konsultasi ini sebagai selesai?')">
                                        ✓ Selesai
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">🎉</div>
                        <h3>Semua Konsultasi Sudah Ditangani!</h3>
                        <p>Tidak ada konsultasi pending saat ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let table = document.getElementById('konsultasiTable');
    
    if(!table) return;
    
    let rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for(let i = 0; i < rows.length; i++) {
        let patientName = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        let topik = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
        
        if(patientName.includes(searchValue) || topik.includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
});
</script>

</body>
</html>