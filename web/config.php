<?php
session_start();

define('SERVER_BASE_URL', 'http://127.0.0.1:8080');
define('SERVER_IP', '192.168.88.163');

// Identifiants Admin
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'hagasite');

// Fonction pour protéger les pages admin
function force_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}
?>