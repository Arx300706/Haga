<?php
require_once 'config.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Haga Hosting</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #1e293b; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #64748b; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .btn { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 1rem; }
        .error { color: #ef4444; font-size: 0.85rem; text-align: center; margin-bottom: 1rem; }
        .client-box { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; text-align: center; }
        .btn-client { background: #10b981; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Haga Hosting</h2>
        <?php if($error): ?> <div class="error"><?= $error ?></div> <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Utilisateur</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Connexion Admin</button>
        </form>

        <div class="client-box">
            <p style="font-size: 0.9rem; color: #64748b;">Pas encore de compte ?</p>
            <a href="client_index.php" class="btn btn-client" style="color:white; padding: 10px; border-radius: 6px; margin-top:5px;">Créer un nouveau compte client</a>
        </div>
    </div>
</body>
</html>