<?php
if(!isset($_SESSION['username'])) {
    header("Location: login.php?status=login_dulu");
    exit;
}