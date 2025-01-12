<?php

include 'modele/requetes.utilisateurs.php';

$function = getRequestParameter('function', 'loginPage');

switch ($function) {
    case 'login':
        $vue = "login";
        
        // Vérifier s'il y a déjà un message d'erreur
        $message = getMessage();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $form = isset($_POST['form']) ? $_POST['form'] : '';

            switch ($form) {
                case 'login':
                    // Vérifier le reCAPTCHA
                    if (empty($_POST['g-recaptcha-response'])) {
                        $_SESSION['message'] = "Veuillez valider le reCAPTCHA.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    // Valider le reCAPTCHA
                    $recaptchaResponse = $_POST['g-recaptcha-response'];
                    if (!validerRecaptcha($recaptchaResponse, '6Lc9KrMqAAAAAJpdJP2G8GWD0MDD87W0SXaFV5GV')) {
                        $_SESSION['message'] = "Échec de la validation du reCAPTCHA. Veuillez réessayer.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    $email = trim($_POST['login-email']);
                    $password = trim($_POST['login-password']);
                
                    // Validation de l'email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION['message'] = "Adresse e-mail invalide.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }
                    
                    // Recherche l'utilisateur dans la base de données
                    $user = rechercheParMail($db, $email);
                
                    if ($user && password_verify($password, $user['Mot_de_passe'])) { // Vérification du mot de passe
                        // Connexion réussie, régénération de l'ID de session
                        session_regenerate_id(true);
                        
                        // Stocke les infos utilisateur dans la session
                        $_SESSION['user_id'] = $user['idUser'];
                        $_SESSION['username'] = $user['Nom'];
                        $_SESSION['Admin'] = $user['Admin'];

                        // Redirection conditionnelle selon le rôle
                        if ($user['Admin'] == 1) {
                            header("Location: admin.php");
                        } else {
                            header("Location: page_principale.php");
                        }
                    } else {
                        $_SESSION['message'] = "Identifiants incorrects. Veuillez réessayer."; // Message d'erreur
                        header("Location: index.php?cible=utilisateurs&function=login"); // Redirection vers le formulaire de connexion
                    }
                    exit();
                    break;
                
                case 'mdpo':
                    $email = trim($_POST['mdpo-email']);

                    // Validation de l'email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION['message'] = "Adresse e-mail invalide.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    // Vérifiez si l'email existe dans la base de données
                    $user = rechercheParMail($db, $email);

                    if ($user) {
                        $_SESSION['message'] = mdpResetEmail($db, $email);
                    } else {
                        $_SESSION['message'] = "Aucun compte trouvé avec cette adresse e-mail.";
                    }
                    header("Location: index.php?cible=utilisateurs&function=login");
                    exit();
                    break;

                case 'register':
                    // Vérifier le reCAPTCHA
                    if (empty($_POST['g-recaptcha-response'])) {
                        $_SESSION['message'] = "Veuillez valider le reCAPTCHA.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    // Valider le reCAPTCHA
                    $recaptchaResponse = $_POST['g-recaptcha-response'];
                    if (!validerRecaptcha($recaptchaResponse, '6Lf8HLMqAAAAAMavW7tlUiZ3S8UkoqCwglEZuBnn')) {
                        $_SESSION['message'] = "Échec de la validation du reCAPTCHA. Veuillez réessayer.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    $nom = trim($_POST['register-nom']);
                    $prenom = trim($_POST['register-prenom']);
                    $email = trim($_POST['register-email']);
                    $password = trim($_POST['register-password']);
                    $classe = trim($_POST['register-classe']);

                    // Validation : Mot de passe
                    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
                        $_SESSION['message'] = "Le mot de passe doit contenir au moins 8 caractères, un chiffre, et un caractère spécial.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }

                    // Recherche l'utilisateur dans la base de données
                    $user = rechercheParMail($db, $email);
                    if (!empty($user)) { // Explicitly check if a user exists
                        $_SESSION['message'] = "Cet email est déjà utilisé.";
                        header("Location: index.php?cible=utilisateurs&function=login");
                        exit();
                    }
                    
                    //Insertion utilisateur
                    $password = password_hash($password, PASSWORD_BCRYPT);
                    $utilisateur = [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'mail' => $email,
                        'mot_de_passe' => $password,
                        'classe' => $classe
                    ];
                    if (ajouteUtilisateur($db, $utilisateur)){
                        $_SESSION['message'] = "Inscription a reussi.";
                    } else {
                        $_SESSION['message'] = "Inscription a echoue.";
                    }
                    header("Location: index.php?cible=utilisateurs&function=login");
                    exit();
                    break;
            }
        }

        break;
    
    default:
        $vue = "erreur404";
        $message = "Erreur 404 : la page recherchée n'existe pas.";
}

include 'vue/header.php';
include 'vue/' . $vue . '.php';
include 'vue/footer.php';

?>