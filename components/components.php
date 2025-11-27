<!-- COMPONENTS.PHP -->
<?php
function head($title)
{ ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
<?php } ?>

<?php
function listAlert($status)
{
  switch ($status) {
    case 'berhasil_mendaftar': ?>
      <div class="alert alert-success" role="alert">
        Success : Berhasil mendaftarkan akun 
      </div>
    <?php break;
    case 'gagal_mendaftar': ?>
      <div class="alert alert-danger" role="alert">
        Error : Gagal mendafatarkan akun
      </div>
    <?php break;
    case 'gagal_login': ?>
      <div class="alert alert-danger" role="alert">
        Error : Gagal login
      </div>
    <?php break;
    case 'password_tidak_sama': ?>
      <div class="alert alert-danger" role="alert">
        Error : Password tidak sama
      </div>
    <?php break;
    case 'login_dulu': ?>
      <div class="alert alert-danger" role="alert">
        Error : Login terlebih dahulu
      </div>
    <?php break;    
    case 'berhasil_logout': ?>
      <div class="alert alert-success" role="alert">
        Success : Berhasil Logout
      </div>
    <?php break;
    case 'email_terdaftar': ?>
      <div class="alert alert-danger" role="alert">
        Error : Email telah terdaftar
      </div>
    <?php break;
    case 'username_terdaftar': ?>
      <div class="alert alert-danger" role="alert">
        Error : Username telah terdaftar
      </div>
    <?php break;
  }
}
?>

<?php
function navbar()
{ ?>
  <nav class="navbar bg-dark border-bottom">
    <div class="container d-flex justify-content-beetween">
      <a class="navbar-brand fw-bold fs-3 text-white" href="{kode kamu}">
        To Do List Saya
      </a>
      <a href="../logic/logout.php"><button class="btn btn-danger">Logout</button></a>
    </div>
  </nav>
<?php } ?>

<?php