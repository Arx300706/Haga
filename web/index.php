<?php
require_once 'config.php';
force_login(); // Vérifie que l'admin est bien connecté avec "hagasite"

// --- FONCTION : LIRE LES DOMAINES ACTIFS DANS BIND9 ---
function getDomainZones() {
    $zones = [];
    $namedConfLocal = '/etc/bind/named.conf.local';
    if (!file_exists($namedConfLocal)) return $zones;
    $content = file_get_contents($namedConfLocal);
    if (preg_match_all('/zone\s+"([^"]+)"/', $content, $matches)) {
        $zones = $matches[1];
    }
    return $zones;
}

// --- FONCTION : LIRE LES DEMANDES CLIENTS EN ATTENTE ---
function getPendingRequests() {
    $file = 'pending_requests.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true);
}

$active_domains = getDomainZones();
$pending_requests = getPendingRequests();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Haga Hosting - Admin Panel</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Ajout de styles pour différencier les sections */
        .section-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .badge-pending { background: #f59e0b; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
        .btn-approve { background: #10b981; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .logout-btn { background: #ef4444; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; float: right; }
    </style>
</head>
<body style="background: #f1f5f9; font-family: sans-serif; padding: 40px;">

    <a href="logout.php" class="logout-btn">Déconnexion</a>
    <h1>Tableau de Bord Administrateur</h1>
    <p>Bienvenue, vous êtes connecté en tant qu'expert Haga Hosting.</p>

    <!-- Navigation rapide -->
    <div class="section-box" style="background: #e3f2fd; border-left: 4px solid #2563eb;">
        <strong>Navigation rapide :</strong>
        <a href="create_project.php" style="margin: 0 10px; color: #2563eb; text-decoration: none; font-weight: bold;">➕ Créer un projet</a> | 
        <a href="manage_clients.php" style="margin: 0 10px; color: #2563eb; text-decoration: none; font-weight: bold;">👥 Gérer les clients</a> | 
        <a href="index.php" style="margin: 0 10px; color: #2563eb; text-decoration: none; font-weight: bold;">🏠 Tableau de bord</a>
    </div>

    <div class="section-box">
        <h2 style="color: #f59e0b;">📩 Nouvelles demandes reçues (Clients)</h2>
        <table width="100%" border="0" cellpadding="10" style="border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th align="left">Client</th>
                    <th align="left">Domaine souhaité</th>
                    <th align="left">Email</th>
                    <th align="left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_requests)): ?>
                    <tr><td colspan="4" align="center">Aucune nouvelle demande pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending_requests as $req): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td><strong><?= htmlspecialchars($req['name']) ?></strong></td>
                        <td><code><?= htmlspecialchars($req['domain']) ?></code></td>
                        <td><?= htmlspecialchars($req['email']) ?></td>
                        <td>
                            <form action="create_domain.php" method="POST">
                                <input type="hidden" name="domain" value="<?= htmlspecialchars($req['domain']) ?>">
                                <input type="hidden" name="site_code" value="<?= htmlspecialchars($req['code']) ?>">
                                <input type="hidden" name="client_email" value="<?= htmlspecialchars($req['email']) ?>">
                                <button type="submit" class="btn-approve">Approuver & Créer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section-box">
        <h2 style="color: #2563eb;">🌐 Domaines déjà configurés (Actifs)</h2>
        <table width="100%" border="0" cellpadding="10" style="border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th align="left">Nom du domaine</th>
                    <th align="left">Statut</th>
                    <th align="left">Lien</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_domains as $domain): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td><strong><?= htmlspecialchars($domain) ?></strong></td>
                    <td><span style="color: #10b981;">● Actif</span></td>
                    <td><a href="http://<?= htmlspecialchars($domain) ?>" target="_blank">Visiter le site</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center;">
        <a href="create_domain.php" class="btn-approve" style="background: #2563eb; text-decoration: none; padding: 15px 30px;">+ Ajouter un domaine manuellement</a>
    </div>

</body>
</html>