<?php

require_once __DIR__ . '/../bootstrap.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

requireAuth();

$user_id = $_SESSION['user_id'];
try {
    $user_stmt = $db->prepare("SELECT * FROM User WHERE idUser = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Valider les données
    if (empty($nom) || empty($prenom) || empty($email) || empty($message)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Préparer l'envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'teteatete.innowave@gmail.com';
            $mail->Password   = 'srod bwtb rnhg xmgw'; // Remplacez par votre mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Expéditeur et destinataire
            $mail->setFrom($email, "$nom $prenom");
            $mail->addAddress('teteatete.innowave@gmail.com', 'Tête à Tête');
            $mail->addReplyTo($email, "$nom $prenom");

            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = 'Nouveau message de contact';
            $mail->Body    = "
                <html>
                <body>
                    <h2>Nouveau message de contact</h2>
                    <p><strong>Nom :</strong> {$nom}</p>
                    <p><strong>Prénom :</strong> {$prenom}</p>
                    <p><strong>Email :</strong> {$email}</p>
                    <p><strong>Message :</strong></p>
                    <p>{$message}</p>
                </body>
                </html>
            ";

            // Envoyer l'email
            $mail->send();
            $success = "Votre message a été envoyé avec succès.";

        } catch (Exception $e) {
            $error = "Une erreur est survenue lors de l'envoi de votre message. Erreur : {$mail->ErrorInfo}";
        }
    }
}

require __DIR__ . '/../vue/pages/contact.php';
