<?php

require_once __DIR__ . '/../bootstrap.php';

// Connexion à la base de données

$message = ""; // Variable pour stocker le message d'état

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT); // Hachage du nouveau mot de passe

    // Vérifiez si le token existe
    $stmt = $db->prepare("SELECT * FROM User WHERE reset_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mettre à jour le mot de passe et réinitialiser le token
        $stmt = $db->prepare("UPDATE User SET Mot_de_passe = :new_password, reset_token = NULL WHERE reset_token = :token");
        $stmt->execute(['new_password' => $new_password, 'token' => $token]);
        $message = "Votre mot de passe a été réinitialisé avec succès. <a href='login.php'>Retour à la connexion</a>";
    } else {
        $message = "Token invalide. Veuillez réessayer ou demander un nouveau lien de réinitialisation.";
    }
}

require __DIR__ . '/../vue/pages/changer_mot_de_passe.php';
