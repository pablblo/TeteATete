<?php
require 'db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser = $_POST['idUser'];
    $motif = $_POST['motif'];

    // Vérifiez que l'utilisateur existe
    $stmt = $db->prepare("SELECT * FROM User WHERE idUser = :idUser");
    $stmt->execute(['idUser' => $idUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Incrémente le nombre d'avertissements
        $updateStmt = $db->prepare("UPDATE User SET nbAvertissements = nbAvertissements + 1 WHERE idUser = :idUser");
        $updateStmt->execute(['idUser' => $idUser]);

        try {
            $mail = new PHPMailer(true);

            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'teteatete.innowave@gmail.com'; // Remplacez par votre email
            $mail->Password = 'srod bwtb rnhg xmgw'; // Remplacez par votre mot de passe
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Expéditeur et destinataire
            $mail->setFrom('teteatete.innowave@gmail.com', 'Administrateur Tete A Tete');
            $mail->addAddress($user['Mail']);

            // Contenu du mail
            $mail->isHTML(true);
            $mail->Subject = 'Avertissement';
            $mail->Body = "
                Bonjour,<br><br>
                Vous avez un avertissement pour le motif suivant :<br>
                <strong>$motif</strong><br><br>
                Si cela se reproduit, votre compte sera banni.<br><br>
                Cordialement,<br>
                L'administration.
            ";

            // Envoyer le mail
            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Avertissement envoyé avec succès.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Erreur d'envoi : {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    }
}
