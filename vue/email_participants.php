<?php
require 'vendor/autoload.php'; // Charge PHPMailer via Composer
require 'db_connection.php'; // Connexion à la base de données

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function envoyerEmailParticipants($idCours, $titreCours) {
    global $db;

    // Récupérer les participants du cours
    $stmt = $db->prepare("
        SELECT u.Mail, u.Nom, u.Prenom, c.Titre 
        FROM inscription i
        INNER JOIN user u ON i.idUser = u.idUser
        INNER JOIN cours c ON i.idCours = c.idCours
        WHERE i.idCours = :idCours
    ");
    $stmt->execute(['idCours' => $idCours]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configuration de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Paramètres SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'votre_email@gmail.com'; // Votre email SMTP
        $mail->Password = 'votre_mot_de_passe'; // Votre mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Paramètres généraux de l'expéditeur
        $mail->setFrom('no-reply@teteatete.com', 'Tête à Tête');

        // Parcourir chaque participant et envoyer un email
        foreach ($participants as $participant) {
            $mail->addAddress($participant['Mail'], $participant['Nom'] . ' ' . $participant['Prenom']);

            // Corps du message
            $message = "
                Bonjour {$participant['Prenom']} {$participant['Nom']},

                Le cours « $titreCours » auquel vous avez participé est maintenant terminé.

                Nous vous invitons à évaluer le tuteur en donnant une note sur 5 et un commentaire :
                [Cliquez ici pour évaluer le tuteur](http://localhost/TeteATete/index.php?cible=generique&function=evaluation&idCours=$idCours).

                Merci pour votre participation !

                Cordialement,
                L'équipe Tête à Tête
            ";

            // Configuration de l'email
            $mail->isHTML(true);
            $mail->Subject = "Évaluation du tuteur - $titreCours";
            $mail->Body = nl2br($message);

            // Envoi de l'email
            $mail->send();
            $mail->clearAddresses(); // Nettoyer les adresses pour le prochain email
        }

    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
    }
}
?>
