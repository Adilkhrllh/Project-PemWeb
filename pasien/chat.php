<!-- CHAT.PHP -->
<?php
session_start();
if(!isset($_SESSION['id_user']) || $_SESSION['role']!=='user'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

include("../config/koneksi.php");

if(!isset($_GET['id'])){
    die("ID konsultasi tidak ditemukan.");
}

$id_konsultasi = intval($_GET['id']);
$id_user = $_SESSION['id_user'];

$cek = mysqli_query($koneksi, "SELECT k.*, d.nama as nama_dokter, d.spesialis, k.topik_konsul, k.status_konsul 
                                FROM konsultasi k 
                                JOIN dokter d ON k.id_dokter = d.id_dokter
                                WHERE k.id_konsultasi=$id_konsultasi AND k.id_pasien=$id_user");
if(mysqli_num_rows($cek) == 0){
    die("Konsultasi tidak ditemukan atau bukan milik Anda.");
}

$konsul = mysqli_fetch_assoc($cek);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat dengan Dokter</title>
    <link rel="stylesheet" href="../css/user.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        
        .container { max-width: 900px; margin: 20px auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .header h2 { margin-bottom: 5px; }
        .header .info { font-size: 14px; opacity: 0.9; }
        
        #chatBox { 
            height: 450px; 
            overflow-y: auto; 
            padding: 20px; 
            background: #f9f9f9;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        
        .pesan { 
            margin-bottom: 15px; 
            display: flex;
            flex-direction: column;
            max-width: 70%;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pasien { 
            align-self: flex-end;
        }
        
        .dokter { 
            align-self: flex-start;
        }
        
        .pesan .bubble {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .pasien .bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .dokter .bubble {
            background: white;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
        }
        
        .pesan strong {
            font-size: 12px;
            margin-bottom: 4px;
            display: block;
            opacity: 0.8;
        }
        
        .pesan small {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
            display: block;
        }
        
        .chat-form { 
            padding: 20px; 
            background: white;
            border-radius: 0 0 10px 10px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        #pesan {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        #pesan:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: scale(1.05);
        }
        
        button:active {
            transform: scale(0.95);
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-selesai {
            background: #d4edda;
            color: #155724;
        }
        
        .back-btn {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>

<div class="container">
    <div class="header">
        <h2>💬 Chat dengan Dr. <?= htmlspecialchars($konsul['nama_dokter']) ?></h2>
        <div class="info">
            Spesialis: <?= htmlspecialchars($konsul['spesialis']) ?> | 
            Topik: <?= htmlspecialchars($konsul['topik_konsul']) ?>
            <span class="status-badge status-<?= $konsul['status_konsul'] ?>">
                <?= strtoupper($konsul['status_konsul']) ?>
            </span>
        </div>
    </div>

    <div id="chatBox"></div>

    <div class="chat-form">
        <form id="chatForm">
            <input type="hidden" name="id_konsultasi" value="<?= $id_konsultasi ?>">
            <div class="input-group">
                <input type="text" name="pesan" id="pesan" placeholder="Ketik pesan Anda..." required autocomplete="off">
                <button type="submit">📤 Kirim</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadChat(){
    $.ajax({
        url: '../logic/load_pesan.php',
        type: 'GET',
        data: {id_konsultasi: <?= $id_konsultasi ?>},
        success: function(data){
            $('#chatBox').html(data);
            $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
        }
    });
}

loadChat();

setInterval(loadChat, 2000);

$('#chatForm').submit(function(e){
    e.preventDefault();
    var pesan = $('#pesan').val();
    if(pesan.trim() == '') return;
    
    $.post('../logic/kirim_pesan.php', $(this).serialize(), function(response){
        (response.includes('success'))
            $('#pesan').val('');
            loadChat();
    });
});

$('#pesan').focus();
</script>
</body>
</html>
