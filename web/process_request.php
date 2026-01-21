<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request = [
        'id' => uniqid(),
        'name' => $_POST['client_name'],
        'email' => $_POST['client_email'],
        'domain' => $_POST['requested_domain'],
        'code' => $_POST['site_code'],
        'date' => date('Y-m-d H:i:s')
    ];

    $file = 'pending_requests.json';
    $current_data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $current_data[] = $request;
    
    file_put_contents($file, json_encode($current_data));

    echo "Merci ! Votre demande a été envoyée. L'administrateur vous contactera par email.";
    echo "<br><a href='client_index.php'>Retour</a>";
}
?>