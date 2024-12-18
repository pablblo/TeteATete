<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer la requête de recherche
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    header("Location: page_principale.php");
    exit();
}

try {
    // Rechercher les utilisateurs par prénom et nom
    $stmt = $db->prepare("
        SELECT idUser, Prenom, Nom, Photo_de_Profil 
        FROM User 
        WHERE CONCAT(Prenom, ' ', Nom) LIKE ?
    ");
    $stmt->execute(['%' . $query . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors de la recherche : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img-small {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="page_principale.php">
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
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="#">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="FAQ.php">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="page_principale.php">Cours</a>
                </li>
                <!-- Section de recherche -->
                <form class="d-flex ms-3" action="search_profiles.php" method="GET">
                    <input class="form-control me-2" type="search" name="query" placeholder="Rechercher un utilisateur" aria-label="Search" required>
                    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
                </form>
                <!-- Profil utilisateur connecté -->
                
                <li class="nav-item ms-3 d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="profil.php">
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
                    <a class="btn btn-primary" style="background-color: #E2EAF4; color: black;" href="login.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
    <div class="container mt-5">
        <h3 class="mb-4">Résultats pour "<?php echo htmlspecialchars($query); ?>"</h3>
        <div class="row">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card p-3">
                            <div class="d-flex align-items-center">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>" 
                                     class="profile-img-small me-3" 
                                     alt="Photo de profil">
                                <div>
                                    <h5 class="card-title mb-0">
                                        <a href="profil_public.php?id=<?php echo $user['idUser']; ?>">
                                            <?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?>
                                        </a>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
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
</html>
