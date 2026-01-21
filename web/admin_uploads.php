<?php
session_start();
require_once 'config.php';
require_once 'MailManager.php';
require_once 'mail_config.php';

force_login(); // Vérifie que l'admin est connecté

$data_file = 'donnee.txt';
$upload_base_dir = 'client_uploads/';

// Récupérer tous les clients et leurs uploads
$clients_uploads = [];
if (file_exists($data_file)) {
    $lines = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (!empty($line)) {
            $data = explode('|', $line);
            if (count($data) >= 8) {
                $client_email = trim($data[1]);
                $email_hash = md5($client_email);
                $client_upload_dir = $upload_base_dir . $email_hash . '/';
                
                $files = [];
                if (is_dir($client_upload_dir)) {
                    $dir_contents = array_diff(scandir($client_upload_dir), ['.', '..']);
                    foreach ($dir_contents as $file) {
                        if (is_file($client_upload_dir . $file)) {
                            $files[] = [
                                'name' => $file,
                                'path' => $client_upload_dir . $file,
                                'size' => filesize($client_upload_dir . $file),
                                'date' => date('d/m/Y H:i', filemtime($client_upload_dir . $file))
                            ];
                        }
                    }
                }
                
                $clients_uploads[] = [
                    'name' => trim($data[0]),
                    'email' => $client_email,
                    'phone' => trim($data[2] ?? ''),
                    'company' => trim($data[3] ?? ''),
                    'files' => $files,
                    'file_count' => count($files)
                ];
            }
        }
    }
}

// Traitement de l'envoi d'email à un client
$email_sent = false;
$email_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $client_email = htmlspecialchars($_POST['client_email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    if (!empty($client_email) && !empty($subject) && !empty($message)) {
        try {
            $mailManager = new MailManager();
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('MAIL_USERNAME') ?: 'your-email@gmail.com';
            $mail->Password = getenv('MAIL_PASSWORD') ?: 'your-app-password';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom(getenv('MAIL_FROM') ?: 'noreply@hagahosting.com', 'Admin Haga Hosting');
            $mail->addAddress($client_email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($message);
            
            if ($mail->send()) {
                $email_sent = true;
                $_POST = [];
            } else {
                $email_error = "Erreur lors de l'envoi du mail";
            }
        } catch (Exception $e) {
            $email_error = "Erreur: " . $e->getMessage();
            error_log("Email error: " . $e->getMessage());
        }
    } else {
        $email_error = "Tous les champs sont obligatoires";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Uploads & Clients - Haga Hosting</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #64748b;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #0f172a;
            border-bottom-color: #3b82f6;
        }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .client-card {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .client-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .file-list {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .file-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            align-items: center;
        }
        
        .file-item:last-child { border-bottom: none; }
        
        .email-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-family: inherit;
        }
        
        textarea { resize: vertical; min-height: 150px; }
        
        button {
            background: #0f172a;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        button:hover { background: #1e293b; }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #dbeafe;
            color: #0c4a6e;
            border-radius: 3px;
            font-size: 0.875rem;
        }
        
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .footer-link {
            margin-top: 2rem;
            text-align: center;
        }
        
        .footer-link a {
            color: #3b82f6;
            text-decoration: none;
        }
        
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📊 Admin - Gestion des Uploads & Clients</h1>
            <p>Voir les fichiers uploadés et communiquer avec les clients</p>
        </div>
    </div>
    
    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('uploads')">📁 Uploads des Clients</button>
            <button class="tab-btn" onclick="switchTab('email')">✉️ Envoyer un Email</button>
        </div>
        
        <!-- TAB: UPLOADS -->
        <div id="uploads" class="tab-content active">
            <?php if (count($clients_uploads) === 0): ?>
                <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center;">
                    <p>📭 Aucun fichier uploadé pour l'instant</p>
                </div>
            <?php else: ?>
                <?php foreach ($clients_uploads as $client): ?>
                    <div class="client-card">
                        <div class="client-header">
                            <div>
                                <h2><?php echo htmlspecialchars($client['name']); ?></h2>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
                            </div>
                            <div>
                                <p><strong>Entreprise:</strong> <?php echo htmlspecialchars($client['company']); ?></p>
                                <p><span class="badge"><?php echo $client['file_count']; ?> fichier(s)</span></p>
                            </div>
                        </div>
                        
                        <?php if ($client['file_count'] > 0): ?>
                            <div class="file-list">
                                <div style="font-weight: 600; margin-bottom: 0.75rem;">📄 Fichiers uploadés:</div>
                                <?php foreach ($client['files'] as $file): ?>
                                    <div class="file-item">
                                        <div><?php echo htmlspecialchars($file['name']); ?></div>
                                        <div><?php echo number_format($file['size'] / 1024 / 1024, 2); ?> MB</div>
                                        <div><?php echo $file['date']; ?></div>
                                        <div>
                                            <a href="<?php echo htmlspecialchars($file['path']); ?>" download style="color: #3b82f6; text-decoration: none;">⬇️ Télécharger</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #999; margin-top: 1rem;">❌ Aucun fichier uploadé</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- TAB: EMAIL -->
        <div id="email" class="tab-content">
            <?php if ($email_sent): ?>
                <div class="success-msg">✅ Email envoyé avec succès!</div>
            <?php endif; ?>
            
            <?php if (!empty($email_error)): ?>
                <div class="error-msg">❌ <?php echo $email_error; ?></div>
            <?php endif; ?>
            
            <div class="email-form">
                <h2>📧 Envoyer un Email au Client</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="client_email">Sélectionner un client:</label>
                        <select name="client_email" id="client_email" required>
                            <option value="">-- Choisir un client --</option>
                            <?php foreach ($clients_uploads as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['email']); ?>">
                                    <?php echo htmlspecialchars($client['name']); ?> (<?php echo htmlspecialchars($client['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Sujet:</label>
                        <input type="text" name="subject" id="subject" required placeholder="Objet du message">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea name="message" id="message" required placeholder="Votre message..."></textarea>
                    </div>
                    
                    <button type="submit" name="send_email">Envoyer l'Email</button>
                </form>
            </div>
        </div>
        
        <div class="footer-link">
            <a href="index.php">← Retour à l'admin</a>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Masquer tous les tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Afficher le tab sélectionné
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
