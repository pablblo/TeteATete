<?php

require_once __DIR__ . '/../bootstrap.php';

// Connexion à la base de données


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

        if ($token = apiLogin($email, $password)) {
            $_SESSION['api_token'] = $token;
        }

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

require __DIR__ . '/../vue/pages/login.php';
