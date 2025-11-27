<!-- RIWAYAT KONSULTASI -->
<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='user'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_pasien = $_SESSION['id_user'];

$sql = "SELECT k.id_konsultasi, d.nama AS dokter, d.spesialis, k.topik_konsul, k.status_konsul, k.konsultasi_date
        FROM konsultasi k
        JOIN dokter d ON k.id_dokter = d.id_dokter
        WHERE k.id_pasien = $id_pasien
        ORDER BY k.created_at DESC";

$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Konsultasi</title>
    <link rel="stylesheet" href="../css/user.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-selesai {
            background: #d4edda;
            color: #155724;
        }
        
        .konsultasi-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .konsultasi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .konsultasi-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .konsultasi-info h3 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #333;
        }
        
        .konsultasi-info .spesialis-tag {
            background: #512da8;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .konsultasi-body {
            margin: 15px 0;
        }
        
        .konsultasi-body p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .konsultasi-body strong {
            color: #333;
            font-weight: 600;
        }
        
        .konsultasi-footer {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-chat {
            background: linear-gradient(135deg, #512da8, #6a3fc4);
            color: white;
        }
        
        .btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(81, 45, 168, 0.4);
        }
        
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #e0e0e0;
        }
        
        .btn-cancel:hover {
            background: #ff9800;
            color: white;
            border-color: #ff9800;
        }
        
        .btn-delete {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #e0e0e0;
        }
        
        .btn-delete:hover {
            background: #f44336;
            color: white;
            border-color: #f44336;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        }
        
        .empty-state p {
            font-size: 16px;
            color: #666;
            margin: 15px 0;
        }
        
        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .konsultasi-header {
                flex-direction: column;
            }
            
            .konsultasi-footer {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="mirza"></div>
<div class="adil"></div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">Konsulin Aja</div>
    <div class="menu">
        <a href="dashboard.php">HOME</a>
        <a href="#">ABOUT</a>
        <a href="account.php">ACCOUNT</a>
        <a href="../logic/logout.php">LOGOUT</a>
    </div>
</div>

<div class="hero">
    <h1>Riwayat Konsultasi</h1>
    <p>Lihat semua riwayat konsultasi Anda dengan dokter</p>
</div>

<div class="container">
    <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="konsultasi-card">
                <div class="konsultasi-header">
                    <div class="konsultasi-info">
                        <h3>👨‍⚕️ Dr. <?= htmlspecialchars($row['dokter']) ?></h3>
                        <span class="spesialis-tag"><?= htmlspecialchars($row['spesialis']) ?></span>
                    </div>
                    <div>
                        <?php if($row['status_konsul'] == 'pending'): ?>
                            <span class="status-badge status-pending">⏳ Pending</span>
                        <?php else: ?>
                            <span class="status-badge status-selesai">✅ Selesai</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="konsultasi-body">
                    <p><strong>Topik:</strong> <?= htmlspecialchars($row['topik_konsul']) ?></p>
                    <p><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($row['konsultasi_date'])) ?></p>
                </div>
                
                <div class="konsultasi-footer">
                    <?php if($row['status_konsul'] == 'pending'): ?>
                        <a href="chat.php?id=<?= $row['id_konsultasi'] ?>" class="btn-action btn-chat">
                            💬 Chat
                        </a>
                        <a href="../logic/batal_konsultasi.php?id=<?= $row['id_konsultasi'] ?>" 
                           class="btn-action btn-cancel"
                           onclick="return confirm('Yakin ingin membatalkan konsultasi ini?')">
                            🚫 Batalkan
                        </a>
                        <a href="../logic/hapus_konsultasi.php?id=<?= $row['id_konsultasi'] ?>" 
                           class="btn-action btn-delete"
                           onclick="return confirm('Yakin ingin menghapus konsultasi ini?')">
                            🗑️ Hapus
                        </a>
                    <?php else: ?>
                        <a href="chat.php?id=<?= $row['id_konsultasi'] ?>" class="btn-action btn-chat">
                            👁️ Lihat Chat
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p><strong>Belum ada riwayat konsultasi</strong></p>
            <p>Mulai konsultasi dengan dokter untuk melihat riwayat di sini</p>
            <a href="konsultasi_add.php" class="btn-action btn-chat" style="margin-top: 20px;">
                💬 Mulai Konsultasi
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>