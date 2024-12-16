<?php
session_start();
require 'config.php'; // Connexion à la base de données

// Inclure la bibliothèque PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Charger PHPMailer via Composer

$message = ""; // Initialisation de la variable pour les messages utilisateur

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Vérifiez si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez entrer une adresse e-mail valide.";
    } else {
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
                $mail->Subject = 'Reinitialisation de votre mot de passe';
                $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token . "'>Réinitialiser le mot de passe</a>";
                $mail->AltBody = "Cliquez sur ce lien pour réinitialiser votre mot de passe : http://localhost/TeteATete/changer_mot_de_passe.php?token=" . $token;

                // Envoi de l'e-mail
                $mail->send();
                $message = "Un email vous a été envoyé pour réinitialiser votre mot de passe.";
            } catch (Exception $e) {
                $message = "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
            }
        } else {
            $message = "Aucun compte trouvé avec cette adresse e-mail.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .message {
            color: green; /* Couleur verte pour succès */
            font-weight: bold;
            margin-bottom: 10px;
        }
        .message.error {
            color: red; /* Couleur rouge pour erreur */
        }
    </style>
</head>
<body>
    <!-- Page principale -->
    <div class="page-container">
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>
        <div class="login-container">
            <div class="form-container">
                <!-- Afficher un message s'il existe -->
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'Erreur') !== false || strpos($message, 'Aucun compte') !== false ? 'error' : ''; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form action="" method="POST">
                    <input type="email" placeholder="Votre e-mail" id="email" name="email" required>
                    <button type="submit">Envoyer le lien de réinitialisation</button>
                    <div class="links">
                        <a href="login.php">Retour à la connexion</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
