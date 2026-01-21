<?php
require_once 'config.php';
force_login(); // Vérifie que l'admin est bien connecté avec "hagasite"

$message = '';
$error = '';

// Fonction pour copier un template récursivement
function copyTemplate($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copyTemplate($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Fonction pour créer une zone DNS via le serveur Java
function createDnsZone($domain, $projectName) {
    $server_base = SERVER_BASE_URL;
    $server_ip = SERVER_IP;
    
    // Construire l'URL pour le serveur Java
    $url = $server_base . "/createZone?domain=" . urlencode($domain) . "&zone=" . urlencode($projectName) . "&ip=" . urlencode($server_ip);
    
    try {
        $response = @file_get_contents($url);
        return $response !== false;
    } catch (Exception $e) {
        return false;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = strtolower(preg_replace('/[^a-z0-9\-]/', '', $_POST['project_name']));
    $domain = strtolower(trim($_POST['domain']));
    $projectType = $_POST['project_type'];
    $template = $_POST['template'];
    $description = htmlspecialchars($_POST['description']);
    $owner = htmlspecialchars($_POST['owner']);
    $active = isset($_POST['active']) ? true : false;

    // Validations
    if (empty($projectName)) {
        $error = "Erreur : Nom du projet invalide.";
    } elseif (!filter_var("http://$domain", FILTER_VALIDATE_URL)) {
        $error = "Erreur : Domaine invalide.";
    } else {
        // Créer les dossiers nécessaires
        $base_path = "/var/www/html/projects";
        @mkdir($base_path, 0755, true);
        
        $projectPath = "$base_path/$projectName";
        if (is_dir($projectPath)) {
            $error = "Erreur : Le projet '$projectName' existe déjà.";
        } else {
            // Créer le dossier du projet
            if (!mkdir($projectPath, 0755, true)) {
                $error = "Erreur : Impossible de créer le dossier du projet.";
            } else {
                // Copier le template si sélectionné
                $templatePath = "/var/www/html/templates/$template/";
                if (is_dir($templatePath)) {
                    copyTemplate($templatePath, $projectPath);
                } else {
                    // Créer un index de base selon le type
                    if ($projectType === 'html') {
                        $html = "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>$projectName</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        p { color: #666; line-height: 1.6; }
    </style>
</head>
<body>
    <div class=\"container\">
        <h1>Bienvenue sur $projectName</h1>
        <p>$description</p>
        <p><em>Projet créé le " . date('d/m/Y H:i:s') . "</em></p>
    </div>
</body>
</html>";
                        file_put_contents("$projectPath/index.html", $html);
                    } elseif ($projectType === 'php') {
                        $php = "<?php
echo '<div style=\"font-family: Arial; max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);\">';
echo '<h1>Bienvenue sur " . htmlspecialchars($projectName) . "</h1>';
echo '<p>" . htmlspecialchars($description) . "</p>';
echo '<p><em>Projet créé le " . date('d/m/Y H:i:s') . "</em></p>';
echo '</div>';
?>";
                        file_put_contents("$projectPath/index.php", $php);
                    }
                }

                // Créer un fichier de métadonnées
                $metadata = [
                    'name' => $projectName,
                    'domain' => $domain,
                    'type' => $projectType,
                    'template' => $template,
                    'description' => $description,
                    'owner' => $owner,
                    'active' => $active,
                    'created' => date('Y-m-d H:i:s')
                ];
                file_put_contents("$projectPath/project.json", json_encode($metadata, JSON_PRETTY_PRINT));

                // Si actif, créer une zone DNS
                if ($active) {
                    $dns_success = createDnsZone($domain, $projectName);
                    if (!$dns_success) {
                        $error = "⚠ Zone DNS non créée. Vérifiez la connexion avec le serveur Java.";
                    }
                }

                // Ensemble - succès
                if (!$error) {
                    $message = "✓ Projet '$projectName' créé avec succès !<br>Accès : <a href=\"http://$projectName.$domain\" target=\"_blank\">http://$projectName.$domain</a>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Projet Web - Haga Hosting</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
        }
        
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 600;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-row .form-group {
            margin-bottom: 0;
        }
        
        .btn {
            padding: 0.875rem 1.5rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #1d4ed8;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .message.success {
            background: #ecfdf5;
            color: #059669;
            border-left: 4px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .message a {
            color: inherit;
            font-weight: bold;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌐 Haga Hosting - Créer un Projet</h1>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>

    <div class="container">
        <div class="section">
            <h2>📝 Créer un Nouveau Projet Web</h2>
            
            <?php if ($message): ?>
                <div class="message success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="create_project.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="project_name">Nom du projet *</label>
                        <input type="text" id="project_name" name="project_name" placeholder="ex: monsite" pattern="[a-z0-9\-]+" required>
                        <small style="color: #94a3b8;">Lettres minuscules, chiffres et tirets uniquement</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="domain">Domaine principal *</label>
                        <input type="text" id="domain" name="domain" placeholder="example.com" value="example.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="project_type">Type de projet *</label>
                        <select id="project_type" name="project_type" required>
                            <option value="">Sélectionner un type</option>
                            <option value="html">Site HTML</option>
                            <option value="php">Projet PHP</option>
                            <option value="empty">Dossier vide</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="template">Template</label>
                        <select id="template" name="template">
                            <option value="basic">Basic</option>
                            <option value="php">PHP minimal</option>
                            <option value="construction">En construction</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Décrivez votre projet..."></textarea>
                </div>

                <div class="form-group">
                    <label for="owner">Client / Responsable *</label>
                    <input type="text" id="owner" name="owner" placeholder="Nom du client" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="active" name="active" checked>
                        <label for="active" style="margin-bottom: 0;">Activer le projet immédiatement</label>
                    </div>
                </div>

                <button type="submit" class="btn">✓ Créer le projet</button>
            </form>
        </div>
    </div>
</body>
</html>