<?php
session_start();
require_once 'config.php';
require_once 'MailManager.php';
require_once 'mail_config.php';

// Vérifier que le client est connecté
if (!isset($_SESSION['client_email'])) {
    header('Location: client_index.php');
    exit();
}

$client_name = $_SESSION['client_user'] ?? 'Client';
$client_email = $_SESSION['client_email'];
$upload_dir = 'client_uploads/' . md5($client_email) . '/';

// Créer le dossier client s'il n'existe pas
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Récupérer les infos du client
$data_file = 'donnee.txt';
$client_domain = '';
$client_servername = '';

if (file_exists($data_file)) {
    $users = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $line) {
        if (!empty($line)) {
            $data = explode('|', $line);
            if (isset($data[1]) && trim($data[1]) === $client_email) {
                $client_domain = trim($data[6] ?? '');
                $client_servername = trim($data[7] ?? '');
                break;
            }
        }
    }
}

// Lister les fichiers uploadés
$client_files = array_diff(scandir($upload_dir), ['.', '..']);
$projects = [];
foreach ($client_files as $file) {
    if (is_file($upload_dir . $file)) {
        $projects[] = [
            'name' => $file,
            'size' => filesize($upload_dir . $file),
            'date' => date('d/m/Y H:i', filemtime($upload_dir . $file))
        ];
    }
}

// Traitement de l'upload
$upload_message = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['project_file'])) {
    $file = $_FILES['project_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_error = "Erreur lors de l'upload du fichier.";
    } else {
        $filename = basename($file['name']);
        // Valider l'extension
        $allowed_extensions = ['zip', 'tar', 'gz', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $upload_error = "Extension de fichier non autorisée. Utilisez: " . implode(', ', $allowed_extensions);
        } elseif ($file['size'] > 100 * 1024 * 1024) { // 100 MB max
            $upload_error = "Le fichier est trop volumineux (max 100 MB).";
        } else {
            $target_file = $upload_dir . time() . '_' . $filename;
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $upload_message = "Fichier uploadé avec succès ! L'administrateur sera notifié.";
                
                // Envoyer les emails
                try {
                    $mailManager = new MailManager();
                    
                    // Email au client
                    $mailManager->sendUploadNotification($client_name, $client_email, $filename);
                    
                    // Email à l'admin
                    $mailManager->sendAdminNotification($client_name, $client_email, '', 'Nouveau fichier: ' . $filename);
                } catch (Exception $e) {
                    error_log("Erreur lors de l'envoi des emails: " . $e->getMessage());
                }
                
                // Rediriger pour rafraîchir la page
                header('Location: client_dashboard.php');
                exit();
            } else {
                $upload_error = "Erreur lors de l'enregistrement du fichier.";
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
    <title>Tableau de Bord Client - Haga Hosting</title>
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
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .header .user-info {
            text-align: right;
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
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section h2 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .info-box.warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        
        .info-box p {
            color: #1e293b;
            margin: 0.5rem 0;
        }
        
        .info-box strong {
            color: #059669;
        }
        
        .info-box.warning strong {
            color: #d97706;
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            color: #475569;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .file-input-label:hover {
            background: #1d4ed8;
        }
        
        .file-name {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #1d4ed8;
        }
        
        .files-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .files-table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .files-table th, .files-table td {
            padding: 1rem;
            text-align: left;
            color: #475569;
        }
        
        .files-table tr:hover {
            background: #f8fafc;
        }
        
        .files-table tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .download-btn {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }
        
        .download-btn:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            color: #94a3b8;
            padding: 2rem;
        }
        
        .message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>🌐 Tableau de Bord Client</h1>
        <div class="user-info">
            <p><?= htmlspecialchars($client_name) ?></p>
            <p style="font-size: 0.9rem; color: #cbd5e1;"><?= htmlspecialchars($client_email) ?></p>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <!-- Infos client -->
        <div class="section">
            <h2>📋 Vos Informations</h2>
            
            <?php if ($client_domain): ?>
                <div class="info-box">
                    <p><strong>Domaine :</strong> <?= htmlspecialchars($client_domain) ?></p>
                    <p><strong>Servername :</strong> <?= htmlspecialchars($client_servername) ?></p>
                </div>
            <?php else: ?>
                <div class="info-box warning">
                    <p><strong>⏳ En attente</strong></p>
                    <p>Votre domaine et servername seront assignés par l'administrateur après examen de vos fichiers.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upload de fichiers -->
        <div class="section">
            <h2>📤 Upload de Fichiers/Projets</h2>
            
            <?php if ($upload_message): ?>
                <div class="message success"><?= $upload_message ?></div>
            <?php endif; ?>
            
            <?php if ($upload_error): ?>
                <div class="message error"><?= $upload_error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label>Sélectionner un fichier à uploader</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="project_file" name="project_file" required onchange="updateFileName(this)">
                        <label for="project_file" class="file-input-label">Choisir un fichier</label>
                    </div>
                    <div class="file-name" id="file-name-display">Aucun fichier sélectionné</div>
                </div>
                
                <p style="color: #64748b; font-size: 0.9rem;">
                    ✓ Formats acceptés: ZIP, TAR, GZ, TXT, PDF, DOC, DOCX, XLS, XLSX<br>
                    ✓ Taille maximale: 100 MB
                </p>
                
                <button type="submit" class="btn">Uploader le fichier</button>
            </form>
        </div>

        <!-- Fichiers uploadés -->
        <div class="section">
            <h2>📁 Vos Fichiers</h2>
            
            <?php if (count($projects) > 0): ?>
                <table class="files-table">
                    <thead>
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Taille</th>
                            <th>Date d'upload</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['name']) ?></td>
                                <td><?= round($project['size'] / 1024, 2) ?> KB</td>
                                <td><?= $project['date'] ?></td>
                                <td>
                                    <a href="<?= $upload_dir . htmlspecialchars($project['name']) ?>" class="download-btn" download>📥 Télécharger</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>📭 Vous n'avez pas encore uploadé de fichiers.</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Utilisez le formulaire ci-dessus pour uploader vos projets.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Support -->
        <div class="section">
            <h2>📧 Support</h2>
            <p>Pour toute question ou assistance, contactez l'administrateur :</p>
            <p><strong>Email :</strong> <a href="mailto:admin@example.com" style="color: #2563eb;">admin@example.com</a></p>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name || 'Aucun fichier sélectionné';
            document.getElementById('file-name-display').textContent = fileName;
        }
    </script>
</body>
</html>
