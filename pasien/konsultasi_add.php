<!-- KONSULTASI ADD -->
<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='user'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");
$id_user = $_SESSION['id_user'];

$filter_spesialis = isset($_GET['spesialis']) ? $_GET['spesialis'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_konsultasi'])) {
    $id_dokter = mysqli_real_escape_string($koneksi, $_POST['id_dokter']);
    $topik_konsul = mysqli_real_escape_string($koneksi, $_POST['topik_konsul']);
    $pesan_awal = mysqli_real_escape_string($koneksi, $_POST['pesan']);
    
    mysqli_begin_transaction($koneksi);
    
    $sql_konsultasi = "INSERT INTO konsultasi (id_dokter, id_pasien, topik_konsul, status_konsul) 
                       VALUES ('$id_dokter', '$id_user', '$topik_konsul', 'pending')";
    
    if (mysqli_query($koneksi, $sql_konsultasi)) {
        $id_konsultasi = mysqli_insert_id($koneksi);

        $sql_pesan = "INSERT INTO pesan (id_konsultasi, pengirim, pesan) 
                      VALUES ('$id_konsultasi', 'pasien', '$pesan_awal')";
        
        if (mysqli_query($koneksi, $sql_pesan)) {
            mysqli_commit($koneksi);
            header("Location: dashboard.php?status=konsultasi_dibuat");
            exit;
        } else {
            mysqli_rollback($koneksi);
            header("Location: konsultasi_add.php?status=error_pesan");
            exit;
        }
    } else {
        mysqli_rollback($koneksi);
        header("Location: konsultasi_add.php?status=error_konsultasi");
        exit;
    }
}

$sql = "SELECT d.id_dokter, d.nama, d.spesialis, d.telp_dokter 
        FROM dokter d 
        JOIN users u ON d.id_user = u.id_user
        WHERE 1=1";

if($filter_spesialis != '') {
    $sql .= " AND d.spesialis = '" . mysqli_real_escape_string($koneksi, $filter_spesialis) . "'";
}

if($search != '') {
    $sql .= " AND d.nama LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
}

$sql .= " ORDER BY d.nama ASC";
$query = mysqli_query($koneksi, $sql);
$total_dokter = mysqli_num_rows($query);

$sql_spesialis = "SELECT DISTINCT spesialis FROM dokter ORDER BY spesialis";
$result_spesialis = mysqli_query($koneksi, $sql_spesialis);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Dokter</title>
    <link rel="stylesheet" href="../css/styleKonsultasi.css">
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
    <h1>Pilih Dokter Konsultasi</h1>
</div>

<div class="container">
    <div class="filter-section">
        <form method="GET" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Spesialis</label>
                    <select name="spesialis" id="spesialisSelect">
                        <option value="">Semua Spesialis</option>
                        <?php while($spesialis = mysqli_fetch_assoc($result_spesialis)): ?>
                            <option value="<?= htmlspecialchars($spesialis['spesialis']) ?>"
                                <?= ($filter_spesialis == $spesialis['spesialis']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spesialis['spesialis']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Cari Nama Dokter</label>
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               placeholder="Ketik nama dokter..." 
                               value="<?= htmlspecialchars($search) ?>">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn-filter">Cari Dokter</button>
            </div>
        </form>
    </div>

    <?php if ($filter_spesialis !== "" || $search !== "") : ?>
        <div class="result-info">
            <strong>Hasil:</strong> <?= $total_dokter ?> dokter ditemukan
            <?php if ($filter_spesialis !== "") : ?>
                dengan spesialis <strong><?= htmlspecialchars($filter_spesialis) ?></strong>
            <?php endif; ?>
            <?php if ($search !== "") : ?>
                <?= ($filter_spesialis !== "" ? "dan" : "") ?> nama "<strong><?= htmlspecialchars($search) ?></strong>"
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($total_dokter > 0) : ?>
        <?php 
        mysqli_data_seek($query, 0);
        while ($dokter = mysqli_fetch_assoc($query)) : 
        ?>
            <div class="doctor-card">                
                <div class="doctor-info">
                    <h3>Dr. <?= htmlspecialchars($dokter['nama']) ?></h3>
                    <span class="badge">
                        <?= htmlspecialchars($dokter['spesialis']) ?>
                    </span>
                    <p>📞 <?= htmlspecialchars($dokter['telp_dokter']) ?></p>
                    <p>Siap membantu konsultasi kesehatan Anda</p>
                    <button class="btn" onclick="openModal(
                        <?= $dokter['id_dokter'] ?>, 
                        '<?= htmlspecialchars($dokter['nama'], ENT_QUOTES) ?>', 
                        '<?= htmlspecialchars($dokter['spesialis'], ENT_QUOTES) ?>'
                    )">
                        💬 Mulai Konsultasi
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="no-data">
            <p style="font-size: 40px; margin-bottom: 10px;">😔</p>
            <p><strong>Dokter tidak ditemukan</strong></p>
            <p>
                <?php if ($filter_spesialis !== "" || $search !== "") : ?>
                    Coba ubah filter atau kata kunci pencarian
                <?php else : ?>
                    Belum ada dokter yang tersedia
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

</div>

<div class="modal" id="konsultasiModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📋 Form Konsultasi</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form method="POST">
            <div class="modal-body">

                <div class="doctor-modal-info">
                    <div>
                        <h4 id="modalDokterNama" style="margin:0 0 5px;font-size:18px;"></h4>
                        <span class="badge" id="modalDokterSpesialis"></span>
                    </div>
                </div>

                <input type="hidden" name="id_dokter" id="modalIdDokter">
                <input type="hidden" name="submit_konsultasi" value="1">

                <div class="form-group">
                    <label>Topik Konsultasi <span style="color: red;">*</span></label>
                    <input type="text" name="topik_konsul" placeholder="Contoh: Sakit kepala berkepanjangan" required>
                </div>

                <div class="form-group">
                    <label>Keluhan <span style="color: red;">*</span></label>
                    <textarea name="pesan" 
                            placeholder="Tuliskan keluhan Anda secara detail..."
                            rows="6"
                            required></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit">Mulai Konsultasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(idDokter, namaDokter, spesialis) {
    document.getElementById('modalIdDokter').value = idDokter;
    document.getElementById('modalDokterNama').textContent = "Dr. " + namaDokter;
    document.getElementById('modalDokterSpesialis').textContent = spesialis;

    document.getElementById('konsultasiModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('konsultasiModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.getElementById('konsultasiModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        document.getElementById('filterForm').submit();
    }
});
</script>

</body>
</html>