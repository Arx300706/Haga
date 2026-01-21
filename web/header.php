<?php
require_once 'config.php';

// Fonction simple pour savoir quelle page est active
function isActive($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return ($current === $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haga Hosting</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<aside>
    <div class="brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary-color)"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>
        <span>Haga Hosting</span>
    </div>
    <nav>
        <a href="index.php" class="<?= isActive('index.php') ?>">Tableau de bord</a>
        <a href="create_domain.php" class="<?= isActive('create_domain.php') ?>">Créer un domaine</a>
        <a href="dns_status.php" class="<?= isActive('dns_status.php') ?>">Supervision DNS</a>
    </nav>
    <div class="server-status">
        <p><span class="status-dot"></span> Système : En ligne</p>
    </div>
</aside>

<main>
    <header>
        <h1><?= isset($pageTitle) ? $pageTitle : 'Haga Hosting' ?></h1>
        <div style="font-size:0.9rem; color:var(--text-muted);">
            <?= date("d/m/Y H:i") ?>
        </div>
    </header>