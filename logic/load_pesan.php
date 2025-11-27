<?php
session_start();
include("../config/koneksi.php");

if(!isset($_GET['id_konsultasi'])){
    die("ID konsultasi tidak ada");
}

$id_konsultasi = intval($_GET['id_konsultasi']);
$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

// Ambil data konsultasi untuk verifikasi
$cek_konsul = mysqli_query($koneksi, "SELECT * FROM konsultasi WHERE id_konsultasi=$id_konsultasi");
$konsul_data = mysqli_fetch_assoc($cek_konsul);

// Verifikasi akses
if($role == 'user' && $konsul_data['id_pasien'] != $id_user){
    die("Akses ditolak");
} elseif($role == 'dokter'){
    // Cek apakah dokter ini yang menangani konsultasi
    $cek_dokter = mysqli_query($koneksi, "SELECT d.id_dokter FROM dokter d WHERE d.id_user=$id_user");
    $dokter_data = mysqli_fetch_assoc($cek_dokter);
    if($konsul_data['id_dokter'] != $dokter_data['id_dokter']){
        die("Akses ditolak");
    }
}

// Ambil semua pesan dengan join yang lebih baik
$query = "SELECT 
            p.id_pesan,
            p.pengirim,
            p.pesan,
            p.created_at,
            CASE 
                WHEN p.pengirim = 'pasien' THEN u_pasien.username
                WHEN p.pengirim = 'dokter' THEN d.nama
            END as nama_pengirim
          FROM pesan p
          JOIN konsultasi k ON p.id_konsultasi = k.id_konsultasi
          LEFT JOIN users u_pasien ON k.id_pasien = u_pasien.id_user AND p.pengirim = 'pasien'
          LEFT JOIN dokter d ON k.id_dokter = d.id_dokter AND p.pengirim = 'dokter'
          WHERE p.id_konsultasi = $id_konsultasi 
          ORDER BY p.created_at ASC";

$result = mysqli_query($koneksi, $query);

if(mysqli_num_rows($result) == 0){
    echo "<div class='empty-state'>Belum ada pesan. Mulai percakapan! 💬</div>";
} else {
    while($row = mysqli_fetch_assoc($result)){
        $class = $row['pengirim'];
        $nama_pengirim = htmlspecialchars($row['nama_pengirim']);
        $pesan_text = htmlspecialchars($row['pesan']);

        $waktu_obj = strtotime($row['created_at']);
        $hari_ini = strtotime('today');
        $kemarin = strtotime('yesterday');
        
        if($waktu_obj >= $hari_ini){
            $waktu = "Hari ini " . date('H:i', $waktu_obj);
        } elseif($waktu_obj >= $kemarin){
            $waktu = "Kemarin " . date('H:i', $waktu_obj);
        } else {
            $waktu = date('d/m/Y H:i', $waktu_obj);
        }
        
        echo "<div class='pesan $class'>";
        echo "  <div class='bubble'>";
        echo "    <strong>$nama_pengirim</strong>";
        echo "    <div>$pesan_text</div>";
        echo "    <small>$waktu</small>";
        echo "  </div>";
        echo "</div>";
    }
}
?>