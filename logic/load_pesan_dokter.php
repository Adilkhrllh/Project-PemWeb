<?php
session_start();
include("../config/koneksi.php");

if(!isset($_GET['id_konsultasi'])){
    echo '<div class="empty-state"><div class="icon">❌</div><p>ID konsultasi tidak ditemukan</p></div>';
    exit;
}

$id_konsultasi = intval($_GET['id_konsultasi']);

$query = "SELECT p.*, u.username 
          FROM pesan p
          LEFT JOIN konsultasi k ON p.id_konsultasi = k.id_konsultasi
          LEFT JOIN users u ON (
              CASE 
                  WHEN p.pengirim = 'pasien' THEN k.id_pasien = u.id_user
                  WHEN p.pengirim = 'dokter' THEN k.id_dokter IN (SELECT id_dokter FROM dokter WHERE id_user = u.id_user)
              END
          )
          WHERE p.id_konsultasi = $id_konsultasi 
          ORDER BY p.created_at ASC";

$result = mysqli_query($koneksi, $query);

if(!$result){
    echo '<div class="empty-state"><div class="icon">❌</div><p>Error: ' . mysqli_error($koneksi) . '</p></div>';
    exit;
}

if(mysqli_num_rows($result) == 0){
    echo '<div class="empty-state"><div class="icon">💬</div><p>Belum ada pesan.</p></div>';
    exit;
}

while($row = mysqli_fetch_assoc($result)){
    $pengirim = htmlspecialchars($row['pengirim']);
    $pesan = htmlspecialchars($row['pesan']);
    $waktu = date('H:i', strtotime($row['created_at']));
    $tanggal = date('d M Y', strtotime($row['created_at']));
    $nama = $row['username'] ? htmlspecialchars($row['username']) : ucfirst($pengirim);
    
    echo '<div class="pesan ' . $pengirim . '">';
    echo '    <div class="pesan-sender">' . $nama . '</div>';
    echo '    <div class="bubble">' . nl2br($pesan) . '</div>';
    echo '    <div class="pesan-time">Hari ini ' . $waktu . '</div>';
    echo '</div>';
}
?>