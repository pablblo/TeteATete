<?php
use PHPMailer\PHPMailer\PHPMailer;// Inclure la bibliothèque PHPMailer
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Charger PHPMailer via Composer

function getRequestParameter($key, $default = "")
{
    return isset($_GET[$key]) && !empty($_GET[$key]) ? $_GET[$key] : $default;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

function recupereTousFichiers(): array {
    $directory = __DIR__.'/../';
    $fichiers = scandir($directory);
    $fichiers = array_diff($fichiers, ['.', '..']);
    $fichiers = array_filter($fichiers, function ($file) {
        return (pathinfo($file, PATHINFO_EXTENSION) === 'php' || pathinfo($file, PATHINFO_EXTENSION) === 'html');
    });
    return array_values($fichiers);
}

function mdpResetEmail($db, $email) {
    // Génération d'un token de réinitialisation
    $token = bin2hex(random_bytes(50));
    
    // Préparer et exécuter la mise à jour du token dans la base de données
    $statement = $db->prepare("UPDATE User SET reset_token = :token WHERE Mail = :email");
    $statement->execute(['token' => $token, 'email' => $email]);

    // Préparer l'e-mail avec PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Paramètres du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'teteatete.innowave@gmail.com';
        $mail->Password = 'srod bwtb rnhg xmgw'; // Utiliser un mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('teteatete.innowave@gmail.com', 'TeteATete');
        $mail->addAddress($email);

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de votre mot de passe';
        $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token . "'>Réinitialiser le mot de passe</a>";
        $mail->AltBody = "Cliquez sur ce lien pour réinitialiser votre mot de passe : http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token;

        // Envoi de l'e-mail
        $mail->send();
        return "Un email vous a été envoyé pour réinitialiser votre mot de passe.";
    } catch (Exception $e) {
        return "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }
}
?>