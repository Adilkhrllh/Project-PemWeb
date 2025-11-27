<!-- AUTH.PHP -->
<?php
include("../config/koneksi.php");
session_start();
// LOGIN
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username' OR email='$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 0) {
        header("Location: ../login.php?status=gagal_login");
        exit;
    }

    $data = mysqli_fetch_assoc($result);

    if ($password !== $data['password']) {
        header("Location: ../login.php?status=gagal_login");
        exit;
    }

    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];

    $_SESSION['user'] = [
        'id_user' => $data['id_user'],
        'nama' => isset($data['nama']) ? $data['nama'] : $data['username'],
        'email' => $data['email'],
        'role' => $data['role']
    ];

    if ($data['role'] === "admin") {
        header("Location: ../admin/dashboard.php?status=login_admin");
    } elseif ($data['role'] === "dokter") {
        header("Location: ../dokter/dashboard.php?status=login_dokter");
    } else {
        header("Location: ../pasien/dashboard.php?status=login_user");
    }
    exit;
}

// REGISTER
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    if ($password !== $confirm) {
        header("Location: ../login.php?status=password_tidak_sama&register=1");
        exit;
    }

    $cek_username = "SELECT * FROM users WHERE username='$username'";
    $res_username = mysqli_query($koneksi, $cek_username);

    if (mysqli_num_rows($res_username) > 0) {
        header("Location: ../login.php?status=username_terdaftar&register=1");
        exit;
    }

    $cek_email = "SELECT * FROM users WHERE email='$email'";
    $res_email = mysqli_query($koneksi, $cek_email);

    if (mysqli_num_rows($res_email) > 0) {
        header("Location: ../login.php?status=email_terdaftar&register=1");
        exit;
    }

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