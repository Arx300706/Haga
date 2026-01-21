<?php
session_start();
session_destroy();

// Rediriger vers admin ou client selon le contexte
if (isset($_GET['type']) && $_GET['type'] === 'client') {
    header('Location: client_index.php');
} else {
    header('Location: login.php');
}
exit();
?>