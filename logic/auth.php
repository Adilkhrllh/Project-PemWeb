<?php
include("../config/koneksi.php");
session_start();
// LOGIN
if (isset($_POST['login'])) {
    $username = $_POST['username']; // bisa username atau email
    $password = $_POST['password'];

    // Query dengan username ATAU email
    $query = "SELECT * FROM users WHERE username='$username' OR email='$username'";
    $result = mysqli_query($koneksi, $query);

    // Jika tidak ditemukan
    if (mysqli_num_rows($result) === 0) {
        header("Location: ../login.php?status=gagal_login");
        exit;
    }

    $data = mysqli_fetch_assoc($result);

    // Cek password plaintext
    if ($password !== $data['password']) {
        header("Location: ../login.php?status=gagal_login");
        exit;
    }

    // Login berhasil
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];

    header("Location: ../dashboard/index.php?status=login_berhasil");
    exit;}

// REGISTER
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    // 1. Cek password sama atau tidak
    if ($password !== $confirm) {
        header("Location: ../login.php?status=password_tidak_sama&register=1");
        exit;
    }

    // 2. Cek apakah username sudah ada
    $cek_username = "SELECT * FROM users WHERE username='$username'";
    $res_username = mysqli_query($koneksi, $cek_username);

    if (mysqli_num_rows($res_username) > 0) {
        header("Location: ../login.php?status=username_terdaftar&register=1");
        exit;
    }

    // 3. Cek apakah email sudah ada
    $cek_email = "SELECT * FROM users WHERE email='$email'";
    $res_email = mysqli_query($koneksi, $cek_email);

    if (mysqli_num_rows($res_email) > 0) {
        header("Location: ../login.php?status=email_terdaftar&register=1");
        exit;
    }

    // 3. Masukkan ke database
    // **NOTE**: untuk keamanan lebih baik gunakan password_hash
    // $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password, email)
              VALUES ('$username', '$password', '$email')";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        header("Location: ../login.php?status=berhasil_mendaftar");
    } else {
        header("Location: ../login.php?status=gagal_mendaftar&register=1");
    }
    exit;
} ?>