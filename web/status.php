<?php
/**
 * Page de statut système - Vérifie que tout fonctionne correctement
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut Système - Haga Hosting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .status-item {
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .status-item.ok {
            background: #ecfdf5;
            color: #059669;
            border-left: 4px solid #10b981;
        }
        
        .status-item.warning {
            background: #fef3c7;
            color: #d97706;
            border-left: 4px solid #f59e0b;
        }
        
        .status-item.error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }
        
        .content {
            background: white;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h2 {
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .check-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .check-item.pass {
            background: #ecfdf5;
            color: #059669;
        }
        
        .check-item.fail {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .footer {
            background: white;
            padding: 2rem;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            color: #64748b;
        }
        
        .nav-links {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Statut du Système Haga Hosting</h1>
            <p>Vérification de la configuration et des services</p>
            
            <div class="status-grid">
                <?php
                $checks = [];
                
                // Vérification PHP
                $checks[] = [
                    'name' => 'PHP Version',
                    'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'ok' : 'warning',
                    'value' => PHP_VERSION
                ];
                
                // Vérification fichiers
                $checks[] = [
                    'name' => 'donnee.txt',
                    'status' => file_exists('donnee.txt') ? 'ok' : 'error',
                    'value' => file_exists('donnee.txt') ? '✓ Existe' : '✗ Manquant'
                ];
                
                $checks[] = [
                    'name' => 'client_uploads/',
                    'status' => is_dir('client_uploads') ? 'ok' : 'error',
                    'value' => is_dir('client_uploads') ? '✓ Dossier créé' : '✗ Manquant'
                ];
                
                // Vérification extensions PHP
                $checks[] = [
                    'name' => 'Ext. Sessions',
                    'status' => extension_loaded('session') ? 'ok' : 'error',
                    'value' => extension_loaded('session') ? '✓ Chargée' : '✗ Absente'
                ];
                
                $checks[] = [
                    'name' => 'Ext. JSON',
                    'status' => extension_loaded('json') ? 'ok' : 'error',
                    'value' => extension_loaded('json') ? '✓ Chargée' : '✗ Absente'
                ];
                
                $checks[] = [
                    'name' => 'Ext. Mail',
                    'status' => extension_loaded('mail') ? 'ok' : 'warning',
                    'value' => extension_loaded('mail') ? '✓ Disponible' : '⚠ Non chargée'
                ];
                
                foreach ($checks as $check) {
                    echo '<div class="status-item ' . $check['status'] . '">';
                    echo '<strong>' . $check['name'] . '</strong><br>';
                    echo $check['value'];
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="content">
            <!-- Fichiers -->
            <div class="section">
                <h2>📁 Vérification des Fichiers</h2>
                <?php
                $files = [
                    'config.php' => 'Configuration système',
                    'register.php' => 'Inscription clients',
                    'client_index.php' => 'Connexion clients',
                    'client_dashboard.php' => 'Dashboard client',
                    'login.php' => 'Connexion admin',
                    'index.php' => 'Dashboard admin',
                    'create_project.php' => 'Création projets',
                    'manage_clients.php' => 'Gestion clients',
                    'email_notification.php' => 'Notifications email',
                    'logout.php' => 'Déconnexion',
                ];
                
                foreach ($files as $file => $description) {
                    $exists = file_exists($file);
                    $class = $exists ? 'pass' : 'fail';
                    $status = $exists ? '✓' : '✗';
                    echo '<div class="check-item ' . $class . '">';
                    echo '<span>' . $status . '</span>';
                    echo '<span><strong>' . $file . '</strong> - ' . $description . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- Dossiers -->
            <div class="section">
                <h2>📂 Vérification des Dossiers</h2>
                <?php
                $dirs = [
                    'client_uploads' => 'Uploads clients',
                    'css' => 'Styles CSS',
                ];
                
                foreach ($dirs as $dir => $description) {
                    $exists = is_dir($dir);
                    $writable = $exists && is_writable($dir);
                    $class = $writable ? 'pass' : 'fail';
                    $status = $writable ? '✓' : ($exists ? '⚠' : '✗');
                    echo '<div class="check-item ' . $class . '">';
                    echo '<span>' . $status . '</span>';
                    echo '<span><strong>' . $dir . '</strong> - ' . $description;
                    if ($exists && !$writable) echo ' (non accessible en écriture)';
                    echo '</span>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- Configuration -->
            <div class="section">
                <h2>⚙️ Configuration</h2>
                <?php
                require_once 'config.php';
                echo '<div class="check-item pass">';
                echo '<span>✓</span>';
                echo '<span><strong>SERVER_BASE_URL</strong>: ' . SERVER_BASE_URL . '</span>';
                echo '</div>';
                
                echo '<div class="check-item pass">';
                echo '<span>✓</span>';
                echo '<span><strong>SERVER_IP</strong>: ' . SERVER_IP . '</span>';
                echo '</div>';
                
                echo '<div class="check-item pass">';
                echo '<span>✓</span>';
                echo '<span><strong>ADMIN_USER</strong>: ' . ADMIN_USER . '</span>';
                echo '</div>';
                ?>
            </div>
            
            <!-- Données Clients -->
            <div class="section">
                <h2>👥 Clients Enregistrés</h2>
                <?php
                if (file_exists('donnee.txt')) {
                    $lines = file('donnee.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $count = count($lines);
                    
                    echo '<div class="check-item pass">';
                    echo '<span>✓</span>';
                    echo '<span><strong>' . $count . ' client(s)</strong> enregistré(s)</span>';
                    echo '</div>';
                    
                    if ($count > 0) {
                        echo '<p style="margin-top: 1rem; font-size: 0.9rem; color: #64748b;">Exemple de premier client:</p>';
                        $data = explode('|', $lines[0]);
                        if (count($data) >= 3) {
                            echo '<div style="background: #f8fafc; padding: 1rem; border-radius: 6px; font-family: monospace; font-size: 0.85rem; overflow-x: auto;">';
                            echo 'Nom: ' . htmlspecialchars($data[0]) . '<br>';
                            echo 'Email: ' . htmlspecialchars($data[1]) . '<br>';
                            if (count($data) > 5) echo 'Domaine: ' . (trim($data[6]) ?: '(vide)') . '<br>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="check-item fail">';
                    echo '<span>✗</span>';
                    echo '<span>Fichier <strong>donnee.txt</strong> non trouvé</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="footer">
            <div class="nav-links">
                <a href="register.php">📝 Inscription Client</a>
                <a href="client_index.php">🔐 Connexion Client</a>
                <a href="login.php">👨‍💼 Connexion Admin</a>
                <a href="index.php">🏠 Dashboard Admin</a>
            </div>
            <p style="margin-top: 2rem;">
                © 2026 Haga Hosting - Système mis en place le 21 janvier 2026
            </p>
        </div>
    </div>
</body>
</html>
