<?php
session_start();
$error = "";
$success = "";
$data_file = 'donnee.txt';

require_once 'MailManager.php';
require_once 'mail_config.php';

if (isset($_POST['register'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $company = htmlspecialchars(trim($_POST['company']));
    $pass = htmlspecialchars(trim($_POST['password']));
    $pass_confirm = htmlspecialchars(trim($_POST['password_confirm']));

    if (empty($name) || empty($email) || empty($pass) || empty($phone) || empty($company)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($pass !== $pass_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } else {
        $exists = false;
        if (file_exists($data_file)) {
            $users = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($users as $line) {
                if (!empty($line)) {
                    $data = explode('|', $line);
                    if (isset($data[1]) && trim($data[1]) === $email) {
                        $exists = true;
                        break;
                    }
                }
            }
        }

        if ($exists) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $creation_date = date('Y-m-d H:i:s');
            $line = $name . "|" . $email . "|" . $phone . "|" . $company . "|" . $pass . "|" . $creation_date . "|" . "|" . PHP_EOL;
            
            if (file_put_contents($data_file, $line, FILE_APPEND | LOCK_EX)) {
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                $_POST = [];
                
                try {
                    $mailManager = new MailManager();
                    $mailManager->sendRegistrationEmail($name, $email, $company);
                    $mailManager->sendAdminNotification($name, $email, $phone, $company);
                } catch (Exception $e) {
                    error_log("Erreur lors de l'envoi des emails: " . $e->getMessage());
                }
            } else {
                $error = "Erreur lors de la création du compte.";
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
    <title>Inscription Client - Haga Hosting</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .register-container {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #0f172a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #1e293b;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: #666;
        }
        .login-link a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>📝 Inscription Client</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nom complet:</label>
                <input type="text" name="name" required value="<?php echo $_POST['name'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Téléphone:</label>
                <input type="tel" name="phone" required value="<?php echo $_POST['phone'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Entreprise:</label>
                <input type="text" name="company" required value="<?php echo $_POST['company'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Mot de passe:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe:</label>
                <input type="password" name="password_confirm" required>
            </div>
            <button type="submit" name="register">S'inscrire</button>
        </form>
        
        <div class="login-link">
            Déjà inscrit? <a href="client_index.php">Se connecter</a>
        </div>
    </div>
</body>
</html>
