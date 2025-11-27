<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

// Pagination
$limit = 9; // Artikel per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total artikel
$countQuery = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM artikel");
$countResult = mysqli_fetch_assoc($countQuery);
$totalArtikel = $countResult['total'];
$totalPages = ceil($totalArtikel / $limit);

// Query artikel dengan pagination
$artikelQuery = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Kesehatan - Konsulin Aja</title>
    <link rel="stylesheet" href="../css/user.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .navbar {
            background-color: #512da8;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .menu a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
            transition: opacity 0.3s;
        }

        .navbar .menu a:hover {
            opacity: 0.8;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .page-header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .artikel-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            color: #512da8;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 30px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            color: #673ab7;
            transform: translateX(-5px);
        }

        .artikel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .artikel-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .artikel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .artikel-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 25px;
            color: white;
            min-height: 140px;
            display: flex;
            align-items: center;
        }

        .artikel-header h3 {
            font-size: 20px;
            line-height: 1.4;
            margin: 0;
        }

        .artikel-body {
            padding: 25px;
        }

        .artikel-preview {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            /* -webkit-line-clamp: 3; */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 15px;
        }

        .artikel-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .artikel-date {
            font-size: 13px;
            color: #999;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-read {
            background-color: #512da8;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-read:hover {
            background-color: #673ab7;
            transform: scale(1.05);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 40px 0;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: #512da8;
            border: 1px solid #e0e0e0;
            background: white;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background-color: #512da8;
            color: white;
            border-color: #512da8;
        }

        .pagination .active {
            background-color: #512da8;
            color: white;
            border-color: #512da8;
            font-weight: bold;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .no-artikel {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .no-artikel-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .no-artikel p {
            color: #666;
            font-size: 18px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .artikel-container {
                padding: 0 15px;
            }

            .artikel-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .page-header p {
                font-size: 16px;
            }

            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">Konsulin Aja</div>
    <div class="menu">
        <a href="dashboard.php">HOME</a>
        <a href="#">ABOUT</a>
        <a href="account.php">ACCOUNT</a>
        <a href="../logic/logout.php">LOGOUT</a>
    </div>
</div>

<div class="page-header">
    <h1>📰 Artikel Kesehatan</h1>
    <p>Temukan informasi kesehatan terpercaya dan tips hidup sehat</p>
</div>

<div class="artikel-container">
    <a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'not_found'): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span>Artikel yang Anda cari tidak ditemukan.</span>
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($artikelQuery) > 0): ?>
        <div class="artikel-grid">
            <?php while($artikel = mysqli_fetch_assoc($artikelQuery)): ?>
                <div class="artikel-card" onclick="window.location.href='baca_artikel.php?id=<?= $artikel['id_artikel'] ?>'">
                    <div class="artikel-header">
                        <h3><?= htmlspecialchars($artikel['judul']) ?></h3>
                    </div>
                    <div class="artikel-body">
                        <div class="artikel-preview">
                            <?= htmlspecialchars(substr(strip_tags($artikel['konten']), 0, 180)) ?>...
                        </div>
                        <div class="artikel-footer">
                            <span class="artikel-date">
                                <span>📅</span>
                                <span><?= date('d M Y', strtotime($artikel['created_at'])) ?></span>
                            </span>
                            <a href="baca_artikel.php?id=<?= $artikel['id_artikel'] ?>" 
                               class="btn-read" 
                               onclick="event.stopPropagation()">
                                Baca Artikel →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">← Sebelumnya</a>
            <?php else: ?>
                <span class="disabled">← Sebelumnya</span>
            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Selanjutnya →</a>
            <?php else: ?>
                <span class="disabled">Selanjutnya →</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-artikel">
            <div class="no-artikel-icon">📝</div>
            <p>Belum ada artikel yang tersedia saat ini.</p>
            <p style="margin-top: 10px; font-size: 14px; color: #999;">
                Silakan cek kembali nanti untuk artikel kesehatan terbaru.
            </p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>