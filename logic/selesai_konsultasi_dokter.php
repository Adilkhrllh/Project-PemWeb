<?php
session_start();
include("../config/koneksi.php");

if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'dokter'){
    header("Location: ../login.php?status=login_dulu");
    exit;
}

$id_konsultasi = 0;
if(isset($_GET['id'])){
    $id_konsultasi = intval($_GET['id']);
} elseif(isset($_POST['id_konsultasi'])){
    $id_konsultasi = intval($_POST['id_konsultasi']);
}

if($id_konsultasi == 0){
    if(isset($_POST['id_konsultasi'])){
        echo json_encode(['status' => 'error', 'message' => 'ID konsultasi tidak ditemukan']);
        exit;
    }
    header("Location: ../dokter/konsultasi_pending.php?status=error");
    exit;
}

$id_user = $_SESSION['id_user'];

$query_dokter = mysqli_query($koneksi, "SELECT id_dokter FROM dokter WHERE id_user = $id_user");
if(!$query_dokter || mysqli_num_rows($query_dokter) == 0){
    if(isset($_POST['id_konsultasi'])){
        echo json_encode(['status' => 'error', 'message' => 'Data dokter tidak ditemukan']);
        exit;
    }
    header("Location: ../dokter/konsultasi_pending.php?status=error");
    exit;
}

$data_dokter = mysqli_fetch_assoc($query_dokter);
$id_dokter = $data_dokter['id_dokter'];
$cek = mysqli_query($koneksi, "SELECT * FROM konsultasi 
                                WHERE id_konsultasi = $id_konsultasi 
                                AND id_dokter = $id_dokter");

if(!$cek || mysqli_num_rows($cek) == 0){
    if(isset($_POST['id_konsultasi'])){
        echo json_encode(['status' => 'error', 'message' => 'Konsultasi tidak ditemukan atau bukan tanggung jawab Anda']);
        exit;
    }
    header("Location: ../dokter/konsultasi_pending.php?status=error");
    exit;
}

$query = "UPDATE konsultasi 
          SET status_konsul = 'selesai' 
          WHERE id_konsultasi = $id_konsultasi 
          AND id_dokter = $id_dokter";

if(mysqli_query($koneksi, $query)){
    if(isset($_POST['id_konsultasi'])){
        echo json_encode(['status' => 'success', 'message' => 'Konsultasi berhasil ditandai sebagai selesai']);
        exit;
    }
    header("Location: ../dokter/konsultasi_pending.php?status=success");
    exit;
} else {
    if(isset($_POST['id_konsultasi'])){
        echo json_encode(['status' => 'error', 'message' => 'Gagal menandai konsultasi selesai: ' . mysqli_error($koneksi)]);
        exit;
    }
    header("Location: ../dokter/konsultasi_pending.php?status=error");
    exit;
}
?>