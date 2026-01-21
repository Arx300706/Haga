<?php
require_once 'config.php';
require_once 'MailManager.php';
require_once 'mail_config.php';

force_login(); // Vérifie que l'admin est connecté

$message = '';
$error = '';
$data_file = 'donnee.txt';

// Lire les clients
$clients = [];
if (file_exists($data_file)) {
    $lines = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (!empty($line)) {
            $data = explode('|', $line);
            if (count($data) >= 8) {
                $clients[] = [
                    'name' => trim($data[0]),
                    'email' => trim($data[1]),
                    'phone' => trim($data[2]),
                    'company' => trim($data[3]),
                    'password' => trim($data[4]),
                    'created' => trim($data[5]),
                    'domain' => trim($data[6]),
                    'servername' => trim($data[7])
                ];
            }
        }
    }
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    $client_email = htmlspecialchars($_POST['client_email']);
    $domain = htmlspecialchars(strtolower($_POST['domain']));
    $servername = htmlspecialchars(strtolower($_POST['servername']));
    $client_name = '';
    
    if (empty($domain) || empty($servername)) {
        $error = "Le domaine et le servername sont obligatoires.";
    } else {
        // Reconstruire le fichier avec la mise à jour
        $updated_lines = [];
        foreach ($lines as $line) {
            if (!empty($line)) {
                $data = explode('|', $line);
                if (isset($data[1]) && trim($data[1]) === $client_email) {
                    $client_name = trim($data[0]);
                    $data[6] = $domain;
                    $data[7] = $servername;
                    $updated_lines[] = implode('|', $data);
                } else {
                    $updated_lines[] = $line;
                }
            }
        }
        
        if (file_put_contents($data_file, implode(PHP_EOL, $updated_lines) . PHP_EOL)) {
            $message = "✓ Client mis à jour avec succès !";
            
            // Envoyer un email au client pour notifier l'attribution du domaine
            try {
                $mailManager = new MailManager();
                $mailManager->sendDomainAssignmentEmail($client_name, $client_email, $domain, $servername);
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi de l'email: " . $e->getMessage());
            }
            
            // Relire les clients
            $lines = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $clients = [];
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $data = explode('|', $line);
                    if (count($data) >= 8) {
                        $clients[] = [
                            'name' => trim($data[0]),
                            'email' => trim($data[1]),
                            'phone' => trim($data[2]),
                            'company' => trim($data[3]),
                            'password' => trim($data[4]),
                            'created' => trim($data[5]),
                            'domain' => trim($data[6]),
                            'servername' => trim($data[7])
                        ];
                    }
                }
            }
        } else {
            $error = "Erreur lors de la mise à jour du client.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients - Haga Hosting</title>
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
        
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 6px;
            transition: background 0.3s;
        }
        
        .header a:hover {
            background: rgba(255,255,255,0.3);
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
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
        
        .clients-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .clients-table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .clients-table th, .clients-table td {
            padding: 1rem;
            text-align: left;
            color: #475569;
        }
        
        .clients-table tr:hover {
            background: #f8fafc;
        }
        
        .btn-edit {
            background: #2563eb;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-edit:hover {
            background: #1d4ed8;
        }
        
        .status-active {
            background: #ecfdf5;
            color: #059669;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #d97706;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
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
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
        
        .empty-state {
            text-align: center;
            color: #94a3b8;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Gestion des Clients</h1>
        <div>
            <a href="index.php">← Admin Panel</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2>📋 Liste des Clients</h2>
            
            <?php if (count($clients) > 0): ?>
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Entreprise</th>
                            <th>Domaine</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['name']) ?></td>
                                <td><?= htmlspecialchars($client['email']) ?></td>
                                <td><?= htmlspecialchars($client['company']) ?></td>
                                <td><?= htmlspecialchars($client['domain'] ?: '—') ?></td>
                                <td>
                                    <?php if ($client['domain']): ?>
                                        <span class="status-active">✓ Assigné</span>
                                    <?php else: ?>
                                        <span class="status-pending">⏳ En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn-edit" onclick="openModal('<?= htmlspecialchars($client['email']) ?>', '<?= htmlspecialchars($client['domain']) ?>', '<?= htmlspecialchars($client['servername']) ?>')">Éditer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>👤 Aucun client enregistré pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de modification -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2>✏️ Modifier le Client</h2>
            
            <form method="POST" action="manage_clients.php">
                <input type="hidden" name="update_client" value="1">
                <input type="hidden" id="client_email" name="client_email">
                
                <div class="form-group">
                    <label>Email (lecture seule)</label>
                    <input type="email" id="display_email" disabled>
                </div>
                
                <div class="form-group">
                    <label for="domain">Domaine *</label>
                    <input type="text" id="domain" name="domain" placeholder="example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="servername">Servername *</label>
                    <input type="text" id="servername" name="servername" placeholder="ns1.example.com" required>
                </div>
                
                <button type="submit" class="btn">✓ Mettre à jour</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(email, domain, servername) {
            document.getElementById('client_email').value = email;
            document.getElementById('display_email').value = email;
            document.getElementById('domain').value = domain;
            document.getElementById('servername').value = servername;
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        // Fermer le modal si clic en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
