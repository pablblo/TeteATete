<?php

require_once __DIR__ . '/../bootstrap.php';

// Initialiser la variable de message d'erreur
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $classe = $_POST['classe'];

    // Validation : Mot de passe
    if (strlen($password) < 8 || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[\W]/', $password)) {
        $error_message = "Le mot de passe doit contenir au moins 8 caractères, un chiffre, et un caractère spécial.";
    }

    // Vérifier si l'utilisateur existe déjà (Email)
    if (empty($error_message)) { // Si aucune erreur précédente
        $stmt = $db->prepare("SELECT * FROM `User` WHERE `Mail` = ?");
        $stmt->execute([$email]);
        $userExists = $stmt->fetch();

        if ($userExists) {
            $error_message = "Cet email est déjà utilisé.";
        }
    }

    // Si aucune erreur, continuer avec le reCAPTCHA
    if (empty($error_message)) {
        // Validation reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $secretKey = '6Lf8HLMqAAAAAMavW7tlUiZ3S8UkoqCwglEZuBnn'; // Votre clé secrète reCAPTCHA
        $remoteIp = $_SERVER['REMOTE_ADDR'];

        // Requête vers l'API reCAPTCHA
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $remoteIp
        ];

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

        // Vérifier si reCAPTCHA est validé
        if (!$resultJson->success) {
            $error_message = "Captcha invalide, veuillez réessayer.";
        } else {
            // Insérer le nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Hashage du mot de passe
            $stmt = $db->prepare("INSERT INTO `User` (Nom, Prenom, Mail, Mot_de_passe, Classe) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hashedPassword, $classe]);

            // Redirection après succès
            header("Location: login.php");
            exit();
        }
    }
}

require __DIR__ . '/../vue/pages/register.php';
