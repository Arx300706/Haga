<?php
session_start();
$error = "";
$data_file = 'donnee.txt';

// --- LOGIQUE DE CONNEXION ---
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    if (file_exists($data_file)) {
        $users = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($users as $line) {
            if (!empty($line)) {
                $data = explode('|', $line);
                
                // Format: Nom|Email|Téléphone|Entreprise|MotDePasse|DateCreation|Domaine|Servername
                if (count($data) >= 5) {
                    $stored_email = trim($data[1]);
                    $stored_pass = trim($data[4]);

                    if ($stored_email === $email && $stored_pass === $pass) {
                        $_SESSION['client_user'] = trim($data[0]);
                        $_SESSION['client_email'] = $stored_email;
                        $_SESSION['client_phone'] = trim($data[2] ?? '');
                        $_SESSION['client_company'] = trim($data[3] ?? '');
                        header('Location: client_dashboard.php');
                        exit();
                    }
                }
            }
        }
    }
    $error = "Email ou mot de passe incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Client - Haga Hosting</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            width: 100%;
            max-width: 1000px;
        }
        
        .login-card, .info-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .login-card h2, .info-card h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
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
        
        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #dc2626;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }
        
        .register-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .info-card h3 {
            color: #2563eb;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        
        .info-card h3:first-of-type {
            margin-top: 0;
        }
        
        .info-card p, .info-card li {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }
        
        .info-card ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .admin-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .admin-link a {
            color: #64748b;
            text-decoration: none;
        }
        
        .admin-link a:hover {
            color: #1e293b;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-card, .info-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Formulaire de connexion -->
        <div class="login-card">
            <h2>🔐 Connexion Client</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="client_index.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn">Se connecter</button>
            </form>

            <div class="register-link">
                Pas encore de compte ? <a href="register.php">S'inscrire ici</a>
            </div>
            
            <div class="admin-link">
                <a href="login.php">→ Accès administrateur</a>
            </div>
        </div>

        <!-- Infos -->
        <div class="info-card">
            <h2>ℹ️ Bienvenue chez Haga Hosting</h2>
            
            <h3>Pour les clients :</h3>
            <ul>
                <li>Uploadez vos fichiers et projets</li>
                <li>Recevez un domaine personnalisé</li>
                <li>Gestion facile de vos données</li>
                <li>Support technique réactif</li>
            </ul>
            
            <h3>Services inclus :</h3>
            <ul>
                <li>✓ Hébergement web sécurisé</li>
                <li>✓ Serveur DNS performant</li>
                <li>✓ Gestion de domaine</li>
                <li>✓ Certificats SSL/HTTPS</li>
                <li>✓ Sauvegarde automatique</li>
            </ul>
            
            <h3>Besoin d'aide ?</h3>
            <p>Contactez notre équipe support :</p>
            <p><strong>Email :</strong> admin@example.com</p>
            <p><strong>Tél :</strong> +33 1 XX XX XX XX</p>
        </div>
    </div>
</body>
</html>