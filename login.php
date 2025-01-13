<?php
require 'db_connection.php'; // Connexion à la base de données


// Vérifier s'il y a déjà un message d'erreur
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Effacez le message après l'avoir affiché
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si le reCAPTCHA a été rempli
    if (empty($_POST['g-recaptcha-response'])) {
        $_SESSION['error_message'] = "Veuillez valider le reCAPTCHA.";
        header("Location: login.php");
        exit();
    }

    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = '6Lc9KrMqAAAAAJpdJP2G8GWD0MDD87W0SXaFV5GV'; // Votre clé secrète
    $remoteIp = $_SERVER['REMOTE_ADDR'];

    // Requête vers l'API reCAPTCHA de Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $remoteIp
    ];

    // Faire une requête POST pour valider le reCAPTCHA
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result);

    // Vérifier la validité du reCAPTCHA
    if (!$resultJson->success) {
        $_SESSION['error_message'] = "Échec de la validation du reCAPTCHA. Veuillez réessayer.";
        header("Location: login.php");
        exit();
    }

    // Le reCAPTCHA est validé, continuer avec le traitement
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Adresse e-mail invalide.");
    }

    // Recherche l'utilisateur dans la base de données
    $stmt = $db->prepare("SELECT * FROM User WHERE Mail = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Mot_de_passe'])) { // Vérification du mot de passe
        // Connexion réussie, régénération de l'ID de session
        session_regenerate_id(true);

        // Stocke les infos utilisateur dans la session
        $_SESSION['user_id'] = $user['idUser'];
        $_SESSION['username'] = $user['Nom'];
        $_SESSION['Admin'] = $user['Admin']; // Stocker le rôle Admin

        // Redirection conditionnelle selon le rôle
        if ($user['Admin'] == 1) { // Si administrateur
            header("Location: admin.php");
        } else { // Si utilisateur standard
            header("Location: page_principale.php");
        }
        exit();
    } else {
        $_SESSION['error_message'] = "Identifiants incorrects. Veuillez réessayer."; // Message d'erreur
        header("Location: login.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tête à Tête - Connexion</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .error-message {
            color: red; /* Couleur rouge pour le message d'erreur */
            font-weight: bold; /* Mettre en gras le texte */
            margin-bottom: 10px; /* Espace sous le message d'erreur */
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>

        <div class="login-container">
            <div class="form-container">
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="email" placeholder="Mail" id="email" name="email" required>
                    <input type="password" placeholder="Mot de passe" id="password" name="password" required>
                    <br>
                    <br>
                    
                    <div class="g-recaptcha" data-sitekey="6Lc9KrMqAAAAAPSGlsM294Va-fL6FUhavCjtPpPC"></div>
                    <br>

                    <button type="submit">Se Connecter</button>
                    <div class="links">
                        <a href="reset_password.php">Mot de passe oublié</a>
                        <p></p>
                        <a href="register.php">Inscription</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="container-fluid" style="height: 125px"></div>
    </div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</body>
</html>
