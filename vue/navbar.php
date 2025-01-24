<?php
// Inclusion de la connexion à la base de données
require 'db_connection.php';

// Vérification de connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    generateUrlFromFilename("Location: login.php");
    exit();
}

// Récupération des informations utilisateur connecté

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$statement = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$statement->execute([$user_id]);
$user = $statement->fetch(PDO::FETCH_ASSOC);
?>

<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    </style>

<!-- Navbar -->
<link rel="icon" href="images/logo.png">
<div class="container-fluid" style="height: 125px"></div>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php?cible=generique&function=page_principale">
            <img src="images/logo.png" alt="TAT Logo" style="height: 100px; width: 100px;" class="me-2">
            <span style="font-size: 20px; font-weight: bold; color: #0061A0;"></span>
        </a>
        <!-- Toggler for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Navbar Links -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"> <a class="nav-link text-dark fw-semibold" href="index.php?cible=generique&function=contact">Contact</a> </li>
                <li class="nav-item"> <a class="nav-link text-dark fw-semibold" href="index.php?cible=generique&function=FAQ">FAQ</a> </li>
                <li class="nav-item"> <a class="nav-link text-dark fw-semibold" href="index.php?cible=generique&function=page_principale">Cours</a> </li>
                <li class="nav-item"> <a class="nav-link text-dark fw-semibold" href="index.php?cible=generique&function=chat">Chat</a> </li>
                <li class="nav-item"> <a class="nav-link text-dark fw-semibold" href="index.php?cible=generique&function=evaluation">Avis</a> </li>




                <!-- Section de recherche -->
                <li class="nav-item d-flex align-items-center ms-3">
                    <form class="d-flex" action="vue/search_profiles.php" method="GET" style="margin-bottom: 0;">
                        <input class="form-control me-2" type="search" name="query" placeholder="Rechercher un utilisateur" aria-label="Search" required>
                        <button class="btn btn-outline-primary" type="submit">Rechercher</button>
                    </form>
                </li>

                <!-- Profil utilisateur connecté -->
                <li class="nav-item ms-3 d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="index.php?cible=generique&function=profil">
                        <?php 
                        if (!empty($user['Photo_de_Profil'])) {
                        // Utiliser la photo de profil si elle existe
                            $image_src = 'data:image/jpeg;base64,' . base64_encode($user['Photo_de_Profil']);
                        } else {
                        // Image par défaut si la photo n'existe pas
                            $image_src = 'images/default_profile.png'; // Chemin vers votre image par défaut
                        }
                        ?>
                        <img src="<?php echo $image_src; ?>"
                             alt="Profil"
                             class="rounded-circle"
                             style="object-fit: cover; height: 40px; width: 40px; border: 2px solid #ddd;">
                    </a>
                </li>

                <li class="nav-item ms-3">
                    <a class="btn btn-outline-primary"  href="index.php?cible=utilisateurs&function=login">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>