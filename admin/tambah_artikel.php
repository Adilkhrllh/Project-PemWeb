<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='admin'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - Admin</title>
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
            text-decoration: none;
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
            max-width: 1200px;
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

        /* Form Card */
        .form-card {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
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
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            font-family: Arial, sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #512da8;
            box-shadow: 0 0 0 3px rgba(81, 45, 168, 0.1);
        }

        .form-group textarea {
            min-height: 400px;
            resize: vertical;
            line-height: 1.6;
        }

        .char-count {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
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

        .form-help {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }

        /* Toolbar untuk formatting text */
        .editor-toolbar {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .toolbar-btn {
            background-color: white;
            border: 1px solid #ddd;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            color: #333;
            transition: all 0.2s;
        }

        .toolbar-btn:hover {
            background-color: #e9ecef;
            border-color: #512da8;
        }

        .preview-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .preview-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .preview-content {
            background-color: white;
            padding: 20px;
            border-radius: 4px;
            min-height: 200px;
            line-height: 1.8;
            color: #555;
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
    <!-- Navbar -->
    <div class="navbar">
        <div class="greeting">Hallo Admin!</div>
        <a href="../logic/logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="dashboard.php?page=account">Account</a>
            <a href="tambah_artikel.php" class="active">Tambah Artikel</a>
            <a href="dashboard.php?page=artikel">Lihat Artikel</a>
            <a href="dashboard.php?page=dokter">Tabel Dokter</a>
            <a href="dashboard.php?page=user">Tabel User</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <a href="dashboard.php?page=artikel" class="back-btn">← Kembali ke Daftar Artikel</a>

            <div class="page-header">
                <h1 class="page-title">TAMBAH ARTIKEL</h1>
                <p class="page-subtitle">Buat artikel baru untuk ditampilkan di website</p>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">
                        ✓ Artikel berhasil ditambahkan!
                    </div>
                <?php elseif($_GET['status'] == 'error'): ?>
                    <div class="alert alert-error">
                        ✗ Gagal menambahkan artikel. Silakan coba lagi.
                    </div>
                <?php elseif($_GET['status'] == 'empty'): ?>
                    <div class="alert alert-error">
                        ✗ Judul dan konten artikel wajib diisi!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-card">
                <form action="../logic/proses_tambah_artikel.php" method="POST" id="artikelForm">
                    <div class="form-group">
                        <label for="judul">
                            Judul Artikel <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="judul" 
                            name="judul" 
                            placeholder="Masukkan judul artikel yang menarik" 
                            required
                            maxlength="200"
                            oninput="updateCharCount('judul', 'judulCount', 200)">
                        <div class="char-count">
                            <span id="judulCount">0</span> / 200 karakter
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="konten">
                            Konten Artikel <span class="required">*</span>
                        </label>
                        <p class="form-help">
                            💡 Tips: Gunakan paragraf untuk memisahkan topik. Tekan Enter dua kali untuk membuat paragraf baru.
                        </p>
                        
                        <div class="editor-toolbar">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('**', '**')" title="Bold">
                                <strong>B</strong>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('*', '*')" title="Italic">
                                <em>I</em>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('\n### ', '')" title="Heading">
                                H
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('\n- ', '')" title="List">
                                • List
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('\n\n', '\n\n')" title="Paragraph">
                                ¶ Paragraf
                            </button>
                        </div>
                        
                        <textarea 
                            id="konten" 
                            name="konten" 
                            placeholder="Tulis konten artikel di sini... 

Anda bisa menulis beberapa paragraf untuk menjelaskan topik dengan detail.

Gunakan toolbar di atas untuk memformat teks Anda."
                            required
                            oninput="updateCharCount('konten', 'kontenCount', 10000); updatePreview()"></textarea>
                        <div class="char-count">
                            <span id="kontenCount">0</span> / 10000 karakter
                        </div>
                    </div>

                    <div class="preview-section">
                        <div class="preview-title">📄 Preview Artikel</div>
                        <div class="preview-content" id="previewContent">
                            <em style="color: #999;">Preview akan muncul di sini saat Anda mengetik...</em>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            📝 Publikasikan Artikel
                        </button>
                        <a href="dashboard.php?page=artikel" class="btn-cancel">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateCharCount(inputId, countId, maxLength) {
            const input = document.getElementById(inputId);
            const count = document.getElementById(countId);
            const length = input.value.length;
            
            count.textContent = length;
            

            if(length > maxLength * 0.9) {
                count.style.color = '#e74c3c';
            } else if(length > maxLength * 0.7) {
                count.style.color = '#f39c12';
            } else {
                count.style.color = '#999';
            }
        }

        // Insert formatting
        function insertFormat(before, after) {
            const textarea = document.getElementById('konten');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);
            
            const newText = text.substring(0, start) + before + selectedText + after + text.substring(end);
            
            textarea.value = newText;
            textarea.focus();

            const newPos = start + before.length + selectedText.length;
            textarea.setSelectionRange(newPos, newPos);
            
            updatePreview();
            updateCharCount('konten', 'kontenCount', 10000);
        }

        function updatePreview() {
            const judul = document.getElementById('judul').value;
            const konten = document.getElementById('konten').value;
            const preview = document.getElementById('previewContent');
            
            if(!judul && !konten) {
                preview.innerHTML = '<em style="color: #999;">Preview akan muncul di sini saat Anda mengetik...</em>';
                return;
            }
            
            let html = '';
            
            if(judul) {
                html += `<h2 style="color: #333; margin-bottom: 20px; font-size: 24px;">${escapeHtml(judul)}</h2>`;
            }
            
            if(konten) {

                let formattedContent = escapeHtml(konten);
                

                formattedContent = formattedContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                formattedContent = formattedContent.replace(/\*(.*?)\*/g, '<em>$1</em>');
  
                formattedContent = formattedContent.replace(/###\s+(.*?)(\n|$)/g, '<h3 style="color: #555; margin: 15px 0 10px 0; font-size: 18px;">$1</h3>');
   
                formattedContent = formattedContent.replace(/^-\s+(.*)$/gm, '<li>$1</li>');
                formattedContent = formattedContent.replace(/(<li>.*<\/li>)/s, '<ul style="margin: 10px 0; padding-left: 25px;">$1</ul>');

                formattedContent = formattedContent.replace(/\n\n/g, '</p><p style="margin-bottom: 15px;">');
                formattedContent = '<p style="margin-bottom: 15px;">' + formattedContent + '</p>';

                formattedContent = formattedContent.replace(/\n/g, '<br>');
                
                html += formattedContent;
            }
            
            preview.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const judulInput = document.getElementById('judul');
            const kontenInput = document.getElementById('konten');
            
            if(judulInput.value) {
                updateCharCount('judul', 'judulCount', 200);
            }
            
            if(kontenInput.value) {
                updateCharCount('konten', 'kontenCount', 10000);
                updatePreview();
            }

            judulInput.addEventListener('input', updatePreview);
        });

        let formChanged = false;
        document.getElementById('artikelForm').addEventListener('input', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if(formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.getElementById('artikelForm').addEventListener('submit', function() {
            formChanged = false;
        });
    </script>
</body>
</html>