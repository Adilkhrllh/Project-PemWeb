<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

if(!isset($_GET['id'])){
    die("ID konsultasi tidak ditemukan.");
}

$id_konsultasi = intval($_GET['id']);
$id_user = $_SESSION['id_user'];

// Ambil id_dokter dari user yang login
$query_dokter = mysqli_query($koneksi, "SELECT id_dokter, nama, spesialis FROM dokter WHERE id_user=$id_user");
if(!$query_dokter || mysqli_num_rows($query_dokter) == 0){
    die("Data dokter tidak ditemukan.");
}
$data_dokter = mysqli_fetch_assoc($query_dokter);
$id_dokter = $data_dokter['id_dokter'];
$nama_dokter = $data_dokter['nama'];
$spesialis_dokter = $data_dokter['spesialis'];

// Pastikan konsultasi ditangani dokter ini dan ambil info pasien
$cek = mysqli_query($koneksi, "SELECT k.*, u.username as nama_pasien, k.topik_konsul, k.status_konsul 
                                FROM konsultasi k 
                                JOIN users u ON k.id_pasien = u.id_user
                                WHERE k.id_konsultasi=$id_konsultasi AND k.id_dokter=$id_dokter");
if(!$cek || mysqli_num_rows($cek) == 0){
    die("Konsultasi tidak ditemukan atau bukan tanggung jawab Anda.");
}

$konsul = mysqli_fetch_assoc($cek);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan <?= htmlspecialchars($konsul['nama_pasien']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 12px 24px;
            background: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
            align-self: flex-start;
        }
        
        .back-btn:hover {
            background: #4b5563;
            transform: translateX(-5px);
        }
        
        .container { 
            max-width: 600px; 
            width: 100%;
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        
        .header { 
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); 
            color: white; 
            padding: 24px; 
            text-align: center;
        }
        
        .header h2 { 
            margin-bottom: 8px; 
            font-size: 22px;
            font-weight: 600;
        }
        
        .header .info { 
            font-size: 14px; 
            opacity: 0.95; 
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-selesai {
            background: #d1fae5;
            color: #065f46;
        }
        
        #chatBox { 
            height: 480px; 
            overflow-y: auto; 
            padding: 20px; 
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        #chatBox::-webkit-scrollbar {
            width: 6px;
        }
        
        #chatBox::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #chatBox::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 3px;
        }
        
        #chatBox::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }
        
        .pesan { 
            display: flex;
            flex-direction: column;
            max-width: 75%;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateY(15px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        /* Pesan dari pasien - di kiri */
        .pesan.pasien { 
            align-self: flex-start;
        }
        
        /* Pesan dari dokter - di kanan */
        .pesan.dokter { 
            align-self: flex-end;
        }
        
        .pesan-sender {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            opacity: 0.8;
        }
        
        .pesan.pasien .pesan-sender {
            color: #374151;
        }
        
        .pesan.dokter .pesan-sender {
            color: #7c3aed;
            text-align: right;
        }
        
        .pesan .bubble {
            padding: 14px 16px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.5;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .pesan.pasien .bubble {
            background: white;
            color: #1f2937;
            border-bottom-left-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        
        .pesan.dokter .bubble {
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .pesan-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 6px;
        }
        
        .pesan.pasien .pesan-time {
            color: #6b7280;
        }
        
        .pesan.dokter .pesan-time {
            color: #7c3aed;
            text-align: right;
        }
        
        .chat-form { 
            padding: 20px; 
            background: white;
            border-top: 1px solid #e5e7eb;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        #pesan {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            outline: none;
        }
        
        #pesan:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        #pesan::placeholder {
            color: #9ca3af;
        }
        
        .btn-send {
            padding: 14px 24px;
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }
        
        .btn-send:active {
            transform: translateY(0);
        }
        
        .btn-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        
        .empty-state p {
            font-size: 14px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
        
        @media (max-width: 768px) {
            body { padding: 10px; }
            .container { border-radius: 12px; }
            .pesan { max-width: 85%; }
            #chatBox { height: 400px; }
        }
    </style>
</head>
<body>

<a href="konsultasi_pending.php" class="back-btn">
    ← Kembali ke Dashboard
</a>

<div class="container">
    <div class="header">
        <h2>💬 Chat dengan <?= htmlspecialchars($konsul['nama_pasien']) ?></h2>
        <div class="info">
            <span>Spesialis: <?= htmlspecialchars($spesialis_dokter) ?></span>
            <span>|</span>
            <span>Topik: <?= htmlspecialchars($konsul['topik_konsul']) ?></span>
            <span class="status-badge status-<?= $konsul['status_konsul'] ?>">
                <?= $konsul['status_konsul'] ?>
            </span>
        </div>
    </div>

    <div id="chatBox">
        <div class="loading">⏳ Memuat pesan...</div>
    </div>

    <div class="chat-form">
        <form id="chatForm">
            <input type="hidden" name="id_konsultasi" value="<?= $id_konsultasi ?>">
            <input type="hidden" name="pengirim" value="dokter">
            <div class="input-group">
                <input type="text" name="pesan" id="pesan" placeholder="Ketik pesan Anda..." required autocomplete="off">
                <button type="submit" class="btn-send" id="btnKirim">
                    <span>📤</span>
                    <span>Kirim</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let lastMessageCount = 0;

function loadChat(){
    $.ajax({
        url: '../logic/load_pesan_dokter.php',
        type: 'GET',
        data: {id_konsultasi: <?= $id_konsultasi ?>},
        success: function(data){
            if(data.trim() === '') {
                $('#chatBox').html('<div class="empty-state"><div class="icon">💬</div><p>Belum ada pesan. Mulai percakapan dengan pasien.</p></div>');
            } else {
                $('#chatBox').html(data);
                
                // Auto scroll ke bawah jika ada pesan baru
                const chatBox = $('#chatBox')[0];
                const currentMessageCount = $('.pesan').length;
                
                if(currentMessageCount !== lastMessageCount) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                    lastMessageCount = currentMessageCount;
                }
            }
        },
        error: function(xhr, status, error){
            console.error('Error loading chat:', error);
            $('#chatBox').html('<div class="empty-state"><div class="icon">❌</div><p>Gagal memuat pesan. Silakan refresh halaman.</p></div>');
        }
    });
}

// Load chat pertama kali
loadChat();

// Polling tiap 2 detik
setInterval(loadChat, 2000);

// Kirim pesan
$('#chatForm').submit(function(e){
    e.preventDefault();
    
    var pesan = $('#pesan').val().trim();
    if(pesan === ''){
        alert('Pesan tidak boleh kosong!');
        return;
    }
    
    // Disable tombol saat mengirim
    $('#btnKirim').prop('disabled', true).html('<span>⏳</span><span>Mengirim...</span>');
    
    $.ajax({
        url: '../logic/kirim_pesan_dokter.php',
        type: 'POST',
        data: {
            id_konsultasi: <?= $id_konsultasi ?>,
            pesan: pesan,
            pengirim: 'dokter'
        },
        success: function(response){
            console.log('Response:', response);
            $('#pesan').val('');
            loadChat();
        },
        error: function(xhr, status, error){
            console.error('Error:', error);
            alert('Gagal mengirim pesan. Silakan coba lagi.');
        },
        complete: function(){
            // Enable kembali tombol
            $('#btnKirim').prop('disabled', false).html('<span>📤</span><span>Kirim</span>');
            $('#pesan').focus();
        }
    });
});

// Focus pada input saat halaman load
$(document).ready(function(){
    $('#pesan').focus();
});

// Enter to send
$('#pesan').on('keypress', function(e){
    if(e.which === 13){
        e.preventDefault();
        $('#chatForm').submit();
    }
});
</script>
</body>
</html>