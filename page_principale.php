<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Démarrer la session pour l'utilisateur
session_start();

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

// Mise à jour des informations si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $photo = null;

    // Si une nouvelle photo de profil est téléchargée
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Mettre à jour les informations dans la base de données
    $update_query = $db->prepare("
        UPDATE User
        SET Nom = ?, Prenom = ?, Mail = ?, Bio = ?, Photo_de_Profil = IFNULL(?, Photo_de_Profil)
        WHERE idUser = ?
    ");
    $update_query->execute([$nom, $prenom, $email, $bio, $photo, $user_id]);

    // Recharger la page pour voir les nouvelles informations
    header("Location: profil.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tête à Tête - Accueil</title>
    <link rel="stylesheet" href="style/styleprofil.css">
</head>
<body>
    
<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <a href="page_principale.php">
        <img src="images/logo.png" style="height: 100px; width: 100px;" alt="TAT Logo">
        </a>
    </div>

    <ul class="nav-links">
        <li><a href="#">Contact</a></li>
        <li><a href="FAQ.php">FAQ</a></li>
        <li><a href="#" class="post-btn">Poster</a></li>
        <li><a href="profil.php" class="user-profile"> <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>" style="object-fit: cover; height: 50px; width: 50px !important;border: 1px solid #ddd; border-radius: 50%;" alt="Photo de profil"></a></li>

        <li><a href="login.html">Déconnexion</a></li>
    </ul>
</nav>

    <!-- Section de recherche et filtres -->
    <section class="search-section">
        <input type="text" placeholder="Recherche" class="search-bar">
        <button class="filter-btn">Filtre</button>
    </section>

    <!-- Bouton pour poster une annonce -->
    <div class="post-announcement">
        <button class="post-announcement-btn">Poster une annonce <span>✏️</span></button>
    </div>
</body>
</html>
