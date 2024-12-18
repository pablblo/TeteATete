<?php
// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);
?>

<!-- Navbar -->
  
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
                    <a class="nav-link text-dark fw-semibold" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="FAQ.php">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="page_principale.php">Cours</a>
                </li>

                <!-- Section de recherche -->
                <li class="nav-item d-flex align-items-center ms-3">
                    <form class="d-flex" action="search_profiles.php" method="GET" style="margin-bottom: 0;">
                        <input class="form-control me-2" type="search" name="query" placeholder="Rechercher un utilisateur" aria-label="Search" required>
                        <button class="btn btn-outline-primary" type="submit">Rechercher</button>
                    </form>
                </li>

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