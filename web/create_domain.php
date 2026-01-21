<?php
require_once 'config.php';
force_login();

// --- LOGIQUE DE CRÉATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    
    // 1. Nettoyage et préparation
    $domain_raw = trim(strtolower($_POST['domain']));
    $domain_escaped = escapeshellarg($domain_raw);
    
    // On récupère le code source (si client) ou on met un code par défaut (si manuel)
    $site_code = !empty($_POST['site_code']) ? $_POST['site_code'] : "<h1>Bienvenue sur $domain_raw</h1><p>Site cree par l'administrateur.</p>";
    $client_email = !empty($_POST['client_email']) ? $_POST['client_email'] : "admin@haga.local";

    // 2. Exécution du script Bash (DNS + Dossiers)
    $command = "sudo /usr/local/bin/add_dns_zone.sh $domain_escaped 2>&1";
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        // 3. Injection du code HTML dans le dossier du client
        $user_index = "/var/www/$domain_raw/index.html";
        $escaped_code = escapeshellarg($site_code);
        
        // On écrit le fichier index.html avec les droits root (via sudo tee)
        shell_exec("echo $escaped_code | sudo tee $user_index > /dev/null");
        
        // 4. Nettoyage de la liste d'attente (si le fichier existe)
        if (file_exists('pending_requests.json')) {
            $requests = json_decode(file_get_contents('pending_requests.json'), true);
            $new_requests = array_filter($requests, function($req) use ($domain_raw) {
                return $req['domain'] !== $domain_raw;
            });
            file_put_contents('pending_requests.json', json_encode(array_values($new_requests)));
        }

        // Redirection vers l'index avec succès
        header("Location: index.php?success=1&new_site=$domain_raw");
        exit();
    } else {
        $error_msg = implode("<br>", $output);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Haga Hosting - Création de Domaine</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; padding: 40px; color: #1e293b; }
        .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { color: #2563eb; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .btn { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%; font-size: 1rem; }
        .btn:hover { background: #1d4ed8; }
        .error { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Ajouter un nouveau domaine</h2>

    <?php if (isset($error_msg)): ?>
        <div class="error">
            <strong>Erreur :</strong><br> <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nom de domaine</label>
            <input type="text" name="domain" placeholder="exemple.mg" required 
                   value="<?= isset($_GET['domain']) ? htmlspecialchars($_GET['domain']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Code Source (Optionnel)</label>
            <textarea name="site_code" rows="5" placeholder="Laissez vide pour une page par défaut"></textarea>
        </div>

        <input type="hidden" name="client_email" value="admin@haga.local">
        
        <button type="submit" class="btn">Lancer la création</button>
    </form>

    <a href="index.php" class="back-link">← Retour au tableau de bord</a>
</div>

</body>
</html>