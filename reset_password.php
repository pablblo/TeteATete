<?php
session_start();
require 'config.php'; // Connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Vérifiez si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM User WHERE Mail = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Génération d'un token de réinitialisation
        $token = bin2hex(random_bytes(50));
        $stmt = $pdo->prepare("UPDATE User SET reset_token = :token WHERE Mail = :email");
        $stmt->execute(['token' => $token, 'email' => $email]);

        // Préparer l'e-mail
        $to = $email;
        $subject = 'Réinitialisation du mot de passe';
        $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : ";
        $message .= "http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token; // Remplacez par votre domaine
        $headers = 'From: noreply@votre-domaine.com' . "\r\n" .
                   'Reply-To: noreply@votre-domaine.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        // Envoyer l'e-mail
        if (mail($to, $subject, $message, $headers)) {
            echo "Un e-mail a été envoyé pour réinitialiser votre mot de passe.";
        } else {
            echo "Échec de l'envoi de l'e-mail.";
        }
    } else {
        echo "Aucun compte trouvé avec cette adresse e-mail.";
    }
}
?>
