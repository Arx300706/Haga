<?php
/**
 * Fonction pour envoyer des notifications par email
 * À adapter selon votre configuration mail
 */

function sendEmailNotification($admin_email, $subject, $message, $client_data = []) {
    // Configuration email
    $from = "noreply@hagahosting.com";
    $headers = "From: " . $from . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: Haga Hosting System\r\n";
    
    // Créer le corps du message HTML
    $body = "<!DOCTYPE html>
    <html lang=\"fr\">
    <head>
        <meta charset=\"UTF-8\">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
            .email-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .email-body { background: white; padding: 20px; }
            .email-footer { background: #f8fafc; padding: 15px; border-radius: 0 0 8px 8px; font-size: 0.9rem; color: #64748b; border-top: 1px solid #e2e8f0; }
            .info-box { background: #f0f9ff; border-left: 4px solid #0284c7; padding: 15px; margin: 15px 0; }
            .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
            .highlight { background: #ecfdf5; padding: 10px; border-radius: 6px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"email-header\">
                <h1>🌐 Haga Hosting - Notification Système</h1>
            </div>
            <div class=\"email-body\">
                <h2>" . htmlspecialchars($subject) . "</h2>
                <p>" . nl2br($message) . "</p>";
    
    if (!empty($client_data)) {
        $body .= "<div class=\"info-box\">";
        $body .= "<strong>📋 Informations du Client :</strong><br>";
        if (isset($client_data['name'])) $body .= "Nom: " . htmlspecialchars($client_data['name']) . "<br>";
        if (isset($client_data['email'])) $body .= "Email: <a href=\"mailto:" . htmlspecialchars($client_data['email']) . "\">" . htmlspecialchars($client_data['email']) . "</a><br>";
        if (isset($client_data['phone'])) $body .= "Téléphone: " . htmlspecialchars($client_data['phone']) . "<br>";
        if (isset($client_data['company'])) $body .= "Entreprise: " . htmlspecialchars($client_data['company']) . "<br>";
        $body .= "</div>";
    }
    
    $body .= "            <p style=\"margin-top: 20px;\">
                    <a href=\"" . SERVER_BASE_URL . "/web/manage_clients.php\" class=\"btn\">📊 Accéder au tableau de bord</a>
                </p>
            </div>
            <div class=\"email-footer\">
                <p>© " . date('Y') . " Haga Hosting - Tous droits réservés</p>
                <p>Cet email a été généré automatiquement. Veuillez ne pas y répondre.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Essayer d'envoyer l'email
    try {
        if (mail($admin_email, $subject, $body, $headers)) {
            return true;
        }
    } catch (Exception $e) {
        // Enregistrer l'erreur
        error_log("Email notification error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Notifier l'admin qu'un nouveau client s'est inscrit
 */
function notifyNewClientRegistration($client_data) {
    $admin_email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
    $subject = "Nouvelle inscription client - " . $client_data['name'];
    
    $message = "Un nouveau client vient de s'inscrire sur Haga Hosting.\n\n";
    $message .= "Veuillez vérifier les fichiers uploadés et lui assigner un domaine et un servername.\n";
    $message .= "Vous pouvez gérer cela depuis la page de gestion des clients.";
    
    return sendEmailNotification($admin_email, $subject, $message, $client_data);
}

/**
 * Notifier l'admin qu'un client a uploadé un fichier
 */
function notifyFileUpload($client_data, $filename, $file_size) {
    $admin_email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
    $subject = "Nouveau fichier uploadé - " . $client_data['name'];
    
    $message = "Le client " . htmlspecialchars($client_data['name']) . " a uploadé un nouveau fichier.\n\n";
    $message .= "Fichier: " . htmlspecialchars($filename) . "\n";
    $message .= "Taille: " . round($file_size / 1024 / 1024, 2) . " MB\n";
    $message .= "Date: " . date('d/m/Y H:i:s') . "\n\n";
    $message .= "Vérifiez le fichier et attribuez un domaine si nécessaire.";
    
    return sendEmailNotification($admin_email, $subject, $message, $client_data);
}

/**
 * Notifier le client de l'attribution de son domaine
 */
function notifyClientDomainAssignment($client_email, $domain, $servername) {
    $subject = "Votre domaine a été assigné ! 🎉";
    
    $message = "Bienvenue !\n\n";
    $message .= "Votre domaine et servername ont été configurés :\n\n";
    $message .= "Domaine: " . htmlspecialchars($domain) . "\n";
    $message .= "Servername: " . htmlspecialchars($servername) . "\n\n";
    $message .= "Vous pouvez maintenant accéder à votre tableau de bord et commencer à uploader vos projets.\n";
    $message .= "N'hésitez pas à nous contacter si vous avez besoin d'aide.";
    
    $headers = "From: noreply@hagahosting.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return sendEmailNotification($client_email, $subject, $message);
}

?>
