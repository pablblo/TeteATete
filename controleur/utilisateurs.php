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

                        // Redirection après connexion réussie
                        header("Location: page_principale.php");
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