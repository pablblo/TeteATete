<?php
session_start();
require 'config.php'; // Connexion à la base de données

// Inclure la bibliothèque PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Si vous utilisez Composer, chargez PHPMailer

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

        // Préparer l'e-mail avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Paramètres du serveur
            $mail->isSMTP();  // Utilisation du serveur SMTP
            $mail->Host = 'smtp.gmail.com';  // Serveur SMTP de Gmail
            $mail->SMTPAuth = true; 
            $mail->Username = 'teteatete.innowave@gmail.com';  // Votre adresse Gmail
            $mail->Password = 'srod bwtb rnhg xmgw';  // Mot de passe ou mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Chiffrement TLS
            $mail->Port = 587;  // Port SMTP pour TLS

            // Expéditeur et destinataire
            $mail->setFrom('teteatete.innowave@gmail.com', 'Tête à Tête');  // L'expéditeur
            $mail->addAddress($email);  // Le destinataire (utilisateur qui demande la réinitialisation)

            // Contenu de l'e-mail
            $mail->isHTML(true);  // Format HTML
            $mail->Subject = 'Reinitialisation de votre mot de passe';
            $mail->Body    = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token . "'>Réinitialiser le mot de passe</a>";
            $mail->AltBody = 'Cliquez sur ce lien pour réinitialiser votre mot de passe : http://localhost/TeteATete/changer_mot_de_passe.php?token=' . $token;

            // Envoi de l'e-mail
            $mail->send();
            echo "Un e-mail a été envoyé pour réinitialiser votre mot de passe.";
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
        }
    } else {
        echo "Aucun compte trouvé avec cette adresse e-mail.";
    }
}
?>
