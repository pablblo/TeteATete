<?php
// Inclusion des fichiers nécessaires
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'db_connection.php';
include 'navbar.php';



// Vérification de connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupération des informations utilisateur connecté
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 30px;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #0061A0;
            font-weight: bold;
        }
        .profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .profile-container {
            display: flex;
            align-items: center;
        }
        .navbar .nav-link {
            transition: color 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #004f80 !important;
        }

        .navbar .btn-outline-primary {
            transition: all 0.3s ease;
        }

        .navbar .btn-outline-primary:hover {
            background-color: #0061A0;
            color: white;
        }
        .text-danger {
            font-size: 16px;
            font-weight: bold;
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Contactez-nous</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="CGU.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
    </footer>


    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>