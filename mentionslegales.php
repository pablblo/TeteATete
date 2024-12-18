<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Inclusion de la barre de navigation
include 'navbar.php';

// Démarrer la session pour l'utilisateur


// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de login si l'utilisateur n'est pas connecté
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Principale</title>
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
        #create-course-section {
            display: none; /* Masqué par défaut */
        }
        .navbar .nav-link {
            transition: color 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #004f80 !important; /* Couleur au survol */
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
        .profile-container {
            background-color: white;
            text-align: center; /* Centre le texte dans le conteneur */
            border: 1px solid #0061A0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
        }

        .profile-container h1 {
            color: #0061A0;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        
        }
        
        iframe {
            border: none;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: #0061A0;
            color: white;
            padding: 10px 0;
            text-align: center;
            margin-top: 30px;
        }

        footer a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        footer a:hover {
            text-decoration: underline;
        }


    </style>
</head>
<body>
    
<!-- Section iframe pour afficher le PDF -->
<div class="profile-container">
    <iframe src="documents/MLTAT.pdf" width="100%" height="600px"></iframe>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
