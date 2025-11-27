<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

// Tentukan halaman yang aktif
$page = isset($_GET['page']) ? $_GET['page'] : 'user';

// Query berdasarkan halaman
if($page == 'account') {
    $accounts = mysqli_query($koneksi, "SELECT id_user, username, email, role, telp_user FROM users WHERE role='admin' ORDER BY id_user ASC");
} elseif($page == 'artikel') {
    $artikels = mysqli_query($koneksi, "SELECT id_artikel, judul, created_at FROM artikel ORDER BY created_at DESC");
} elseif($page == 'dokter') {
    $dokters = mysqli_query($koneksi, "SELECT d.id_dokter, d.nama, d.spesialis, d.telp_dokter, u.email, u.telp_user, u.spesialis as user_spesialis
                                        FROM dokter d 
                                        JOIN users u ON d.id_user = u.id_user 
                                        ORDER BY d.id_dokter ASC");
} else {
    // Default: Tabel User
    $users = mysqli_query($koneksi, "SELECT id_user, username, tgl_lahir, jk FROM users WHERE role='user' ORDER BY id_user ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
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

        /* Navbar */
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
        }

        /* Container */
        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        /* Sidebar */
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
            position: relative;
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

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .main-content h1 {
            color: #666;
            font-size: 28px;
            font-weight: normal;
            letter-spacing: 1px;
            margin: 0;
        }

        .btn-add {
            background-color: #512da8;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-add:hover {
            background-color: #16a085;
        }

        /* Table */
        table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table thead {
            background-color: #512da8;
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: normal;
            font-size: 14px;
        }

        table th:first-child {
            width: 80px;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #555;
        }

        table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Buttons */
        .btn-delete {
            background-color: white;
            color: #e74c3c;
            border: 1px solid #e74c3c;
            padding: 8px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-right: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-detail {
            background-color: white;
            color: #555;
            border: 1px solid #ccc;
            padding: 8px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background-color: #e74c3c;
            color: white;
        }

        .btn-detail:hover {
            background-color: #f0f0f0;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .modal-header h2 {
            color: #333;
            font-size: 20px;
            font-weight: normal;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            line-height: 1;
        }

        .close-btn:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }

        .form-group input:focus {
            outline: none;
            border-color: #512da8;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-submit {
            background-color: #512da8;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-submit:hover {
            background-color: #16a085;
        }

        .btn-cancel {
            background-color: white;
            color: #555;
            border: 1px solid #ccc;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cancel:hover {
            background-color: #f0f0f0;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="greeting">Hallo Admin!</div>
        <button class="logout-btn" onclick="location.href='../logic/logout.php'">Logout</button>
    </div>

    <!-- Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="?page=account" class="<?= $page == 'account' ? 'active' : '' ?>">Account</a>
            <a href="tambah_artikel.php">Tambah Artikel</a>
            <a href="?page=artikel" class="<?= $page == 'artikel' ? 'active' : '' ?>">Lihat Artikel</a>
            <a href="?page=dokter" class="<?= $page == 'dokter' ? 'active' : '' ?>">Tabel Dokter</a>
            <a href="?page=user" class="<?= $page == 'user' ? 'active' : '' ?>">Tabel User</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if($page == 'account'): ?>
                <!-- TABEL ACCOUNT (Hanya Admin) -->
                <div class="content-header">
                    <h1>ACCOUNT</h1>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($acc = mysqli_fetch_assoc($accounts)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($acc['username']) ?></td>
                            <td><?= htmlspecialchars($acc['email']) ?></td>
                            <td><?= htmlspecialchars($acc['telp_user'] ?? '-') ?></td>
                            <td>
                                <a href="../logic/hapus_user.php?id=<?= $acc['id_user'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus account ini?')">Delete</a>
                                <a href="detail_account.php?id=<?= $acc['id_user'] ?>" 
                                   class="btn-detail">Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php elseif($page == 'artikel'): ?>
                <!-- TABEL ARTIKEL -->
                <div class="content-header">
                    <h1>TABEL ARTIKEL</h1>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Artikel</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($art = mysqli_fetch_assoc($artikels)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($art['judul']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($art['created_at'])) ?></td>
                            <td>
                                <a href="hapus_artikel.php?id=<?= $art['id_artikel'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus artikel ini?')">Delete</a>
                                <a href="edit_artikel.php?id=<?= $art['id_artikel'] ?>" class="btn-detail">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php elseif($page == 'dokter'): ?>
                <!-- TABEL DOKTER -->
                <?php if(isset($_GET['status'])): ?>
                    <?php if($_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">
                            ✓ Dokter berhasil ditambahkan!
                        </div>
                    <?php elseif($_GET['status'] == 'deleted'): ?>
                        <div class="alert alert-success">
                            ✓ Dokter berhasil dihapus!
                        </div>
                    <?php elseif($_GET['status'] == 'error'): ?>
                        <div class="alert alert-error">
                            ✗ Gagal menambahkan dokter. Silakan coba lagi.
                        </div>
                    <?php elseif($_GET['status'] == 'delete_error'): ?>
                        <div class="alert alert-error">
                            ✗ Gagal menghapus dokter. Silakan coba lagi.
                        </div>
                    <?php elseif($_GET['status'] == 'not_found'): ?>
                        <div class="alert alert-error">
                            ✗ Data dokter tidak ditemukan.
                        </div>
                    <?php elseif($_GET['status'] == 'email_exists'): ?>
                        <div class="alert alert-error">
                            ✗ Email sudah terdaftar. Gunakan email lain.
                        </div>
                    <?php elseif($_GET['status'] == 'empty'): ?>
                        <div class="alert alert-error">
                            ✗ Semua field wajib diisi!
                        </div>
                    <?php elseif($_GET['status'] == 'invalid_email'): ?>
                        <div class="alert alert-error">
                            ✗ Format email tidak valid.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="content-header">
                    <h1>TABEL DOKTER</h1>
                    <button class="btn-add" onclick="openModal()">+ Tambah Dokter</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Dokter</th>
                            <th>Spesialis</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($dok = mysqli_fetch_assoc($dokters)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($dok['nama']) ?></td>
                            <td><?= htmlspecialchars($dok['spesialis']) ?></td>
                            <td><?= htmlspecialchars($dok['email']) ?></td>
                            <td><?= htmlspecialchars($dok['telp_dokter'] ?? '-') ?></td>
                            <td>
                                <a href="hapus_dokter.php?id=<?= $dok['id_dokter'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus dokter ini?')">Delete</a>
                                <a href="#" 
                                   class="btn-detail"
                                   onclick="viewDokter(<?= $dok['id_dokter'] ?>, '<?= htmlspecialchars($dok['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($dok['spesialis'], ENT_QUOTES) ?>', '<?= htmlspecialchars($dok['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($dok['telp_dokter'] ?? '-', ENT_QUOTES) ?>'); return false;">Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Modal Tambah Dokter -->
                <div id="modalDokter" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Tambah Dokter</h2>
                            <button class="close-btn" onclick="closeModal()">&times;</button>
                        </div>
                        <form action="../logic/tambah_dokter.php" method="POST">
                            <div class="form-group">
                                <label for="nama">Nama Dokter *</label>
                                <input type="text" id="nama" name="nama" required placeholder="Masukkan nama dokter">
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="contoh@email.com">
                            </div>

                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required placeholder="Masukkan password">
                            </div>

                            <div class="form-group">
                                <label for="telp_dokter">Telepon *</label>
                                <input type="text" id="telp_dokter" name="telp_dokter" required placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="form-group">
                                <label for="spesialis">Spesialis *</label>
                                <input type="text" id="spesialis" name="spesialis" required placeholder="Contoh: Jantung, Hati, Gigi">
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                                <button type="submit" class="btn-submit">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Detail Dokter -->
                <div id="modalDetail" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Detail Dokter</h2>
                            <button class="close-btn" onclick="closeDetailModal()">&times;</button>
                        </div>
                        <div style="padding: 10px 0;">
                            <div class="form-group">
                                <label>Nama Dokter</label>
                                <input type="text" id="detail_nama" readonly style="background-color: #f5f5f5;">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" id="detail_email" readonly style="background-color: #f5f5f5;">
                            </div>
                            <div class="form-group">
                                <label>Spesialis</label>
                                <input type="text" id="detail_spesialis" readonly style="background-color: #f5f5f5;">
                            </div>
                            <div class="form-group">
                                <label>Telepon</label>
                                <input type="text" id="detail_telepon" readonly style="background-color: #f5f5f5;">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="closeDetailModal()">Tutup</button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- TABEL USER (Default) -->
                <div class="content-header">
                    <h1>TABEL USER</h1>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama User</th>
                            <th>Umur</th>
                            <th>Jenis Kelamin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($u = mysqli_fetch_assoc($users)): 
                            // Hitung umur dari tanggal lahir
                            $umur = '';
                            if($u['tgl_lahir']) {
                                $lahir = new DateTime($u['tgl_lahir']);
                                $sekarang = new DateTime();
                                $umur = $sekarang->diff($lahir)->y;
                            }
                            
                            // Singkatan jenis kelamin
                            $jk = $u['jk'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= $umur ?></td>
                            <td><?= htmlspecialchars($jk) ?></td>
                            <td>
                                <a href="../logic/hapus_pasien.php?id=<?= $u['id_user'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus user ini?')">Delete</a>
                                <a href="edit_user.php?id=<?= $u['id_user'] ?>" 
                                   class="btn-detail">Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalDokter').classList.add('show');
        }

        function closeModal() {
            document.getElementById('modalDokter').classList.remove('show');
        }

        function viewDokter(id, nama, spesialis, email, telepon) {
            document.getElementById('detail_nama').value = nama;
            document.getElementById('detail_email').value = email;
            document.getElementById('detail_spesialis').value = spesialis;
            document.getElementById('detail_telepon').value = telepon;
            document.getElementById('modalDetail').classList.add('show');
        }

        function closeDetailModal() {
            document.getElementById('modalDetail').classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('modalDokter');
            const modalDetail = document.getElementById('modalDetail');
            if (event.target == modal) {
                closeModal();
            }
            if (event.target == modalDetail) {
                closeDetailModal();
            }
        }
    </script>
</body>
</html>