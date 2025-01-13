<?php

include 'modele/requetes.utilisateurs.php';

$function = getRequestParameter('function', 'loginPage');

switch ($function) {
    case 'login':
        $vue = "login";
        $refreshLocation = "index.php?cible=utilisateurs&function=login";
        
        // Vérifier s'il y a déjà un message d'erreur
        $message = getMessage();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $form = isset($_POST['form']) ? $_POST['form'] : '';

            switch ($form) {
                case 'login':
                    // Vérifier le reCAPTCHA
                    if (empty($_POST['g-recaptcha-response'])) {
                        redirectWithMessage("Erreur : Veuillez valider le reCAPTCHA.", $refreshLocation);
                    }

                    // Valider le reCAPTCHA
                    $recaptchaResponse = $_POST['g-recaptcha-response'];
                    if (!validerRecaptcha($recaptchaResponse, '6Lc9KrMqAAAAAJpdJP2G8GWD0MDD87W0SXaFV5GV')) {
                        redirectWithMessage("Erreur : Échec de la validation du reCAPTCHA. Veuillez réessayer.", $refreshLocation);
                    }

                    $email = clean_input($_POST['login-email']);
                    $password = clean_input($_POST['login-password']);
                
                    // Validation de l'email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        redirectWithMessage("Erreur : Adresse e-mail invalide.", $refreshLocation);
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
                        redirectWithMessage("Erreur : Identifiants incorrects. Veuillez réessayer.", $refreshLocation);
                    }
                    break;
                
                case 'mdpo':
                    $email = clean_input($_POST['mdpo-email']);

                    // Validation de l'email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        redirectWithMessage("Erreur : Adresse e-mail invalide.", $refreshLocation);
                    }

                    // Vérifiez si l'email existe dans la base de données
                    $user = rechercheParMail($db, $email);

                    if ($user) {
                        $_SESSION['message'] = mdpResetEmail($db, $email);
                    } else {
                        $_SESSION['message'] = "Erreur : Aucun compte trouvé avec cette adresse e-mail.";
                    }
                    redirectWithMessage($_SESSION['message'], $refreshLocation);
                    break;

                case 'register':
                    // Vérifier le reCAPTCHA
                    if (empty($_POST['g-recaptcha-response'])) {
                        redirectWithMessage("Erreur : Veuillez valider le reCAPTCHA.", $refreshLocation);
                    }

                    // Valider le reCAPTCHA
                    $recaptchaResponse = $_POST['g-recaptcha-response'];
                    if (!validerRecaptcha($recaptchaResponse, '6Lf8HLMqAAAAAMavW7tlUiZ3S8UkoqCwglEZuBnn')) {
                        redirectWithMessage("Erreur : Échec de la validation du reCAPTCHA. Veuillez réessayer.", $refreshLocation);
                    }

                    $nom = clean_input($_POST['register-nom']);
                    $prenom = clean_input($_POST['register-prenom']);
                    $email = clean_input($_POST['register-email']);
                    $password = clean_input($_POST['register-password']);
                    $classe = clean_input($_POST['register-classe']);

                    // Validation : Mot de passe
                    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
                        redirectWithMessage("Erreur : Le mot de passe doit contenir au moins 8 caractères, un chiffre, et un caractère spécial.", $refreshLocation);
                    }

                    // Recherche l'utilisateur dans la base de données
                    $user = rechercheParMail($db, $email);
                    if (!empty($user)) { // Explicitly check if a user exists
                        redirectWithMessage("Erreur : Cet email est déjà utilisé.", $refreshLocation);
                    }
                    
                    // Insertion utilisateur
                    $password = password_hash($password, PASSWORD_BCRYPT);
                    $utilisateur = [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'mail' => $email,
                        'mot_de_passe' => $password,
                        'classe' => $classe
                    ];
                    if (ajouteUtilisateur($db, $utilisateur)){
                        $_SESSION['message'] = "Inscription a réussi.";
                    } else {
                        $_SESSION['message'] = "Erreur : Inscription a échoué.";
                    }
                    redirectWithMessage($_SESSION['message'], $refreshLocation);
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
