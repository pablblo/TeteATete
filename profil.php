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

// Récupérer la moyenne des évaluations d'un utilisateur (tuteur ou élève)
$queryMoyenne = $db->prepare("
    SELECT AVG(Note) as moyenne
    FROM Evaluation
    WHERE idUserReceveur = ?
");
$queryMoyenne->execute([$user_id]);
$moyenne = $queryMoyenne->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0;

// Afficher les étoiles (5 étoiles grises par défaut, jaunes selon la moyenne)
$etoilesGrises = 5 - round($moyenne);  // Calculer combien d'étoiles grises afficher
$etoilesJaunes = round($moyenne);  // Étoiles jaunes selon la moyenne
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link rel="stylesheet" href="styleprofil.css">
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
        <li><a href="FAQ.PHP">FAQ</a></li>
        <li><a href="cgu.html">CGU</a></li>
        <li><a href="#" class="post-btn">Poster</a></li>
        <li><a href="profil.php" class="user-profile">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>"
            style="object-fit: cover; height: 50px; width: 50px !important;border: 1px solid #ddd; border-radius: 50%;"
            alt="Photo de profil"></a>
        </li>
        <li><a href="login.html">Déconnexion</a></li>
    </ul>
</nav>

<div class="profile-container">
    <h1><?php echo htmlspecialchars($user['Prenom']) . " " . htmlspecialchars($user['Nom']); ?></h1><br>


    <div class="profile-details">
        <!-- Photo de profil -->
        <div class="profile-photo">
            <?php if ($user['Photo_de_Profil']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>"
                style="object-fit: cover; height: 100px; width: 100px !important;border: 1px solid #ddd; border-radius: 50%;"
                alt="Photo de profil">
            <?php else: ?>
                <img src="default-profile.png" alt="Photo par défaut">
            <?php endif; ?>
            <br><br>
        </div>

        <!-- Affichage des étoiles -->
        <div class="rating">
            <?php
            for ($i = 0; $i < $etoilesJaunes; $i++) {
                echo "<img src='images/etoile_pleine.png' alt='Étoile jaune'>";
            }
            for ($i = 0; $i < $etoilesGrises; $i++) {
                echo "<img src='images/etoile_vide.png' alt='Étoile grise'>";
            }
            ?>
            <br><br>
        </div>

        <!-- Informations de l'utilisateur -->
        <div class="profile-info">
            <p><?php echo htmlspecialchars($user['Bio']); ?></p><br>
            <p><?php echo htmlspecialchars($user['Mail']); ?></p><br>

            <!-- Bouton pour afficher le formulaire -->
            <button id="edit-profile-btn">Modifier le profil</button><br>

<div class="form-container" id="edit-form">
    <form action="profil.php" method="POST" enctype="multipart/form-data">
        <label for="nom">Nom :</label>
        <input type="text" name="nom" value="<?php echo htmlspecialchars($user['Nom']); ?>" required>


                <label for="prenom">Prénom :</label>
                <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required>

                <label for="email">Email :</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['Mail']); ?>" required><br><br>

                <label for="bio">Bio :</label>
                <textarea name="bio" required><?php echo htmlspecialchars($user['Bio']); ?></textarea><br><br>

                <label for="photo">Photo de profil :</label>
                <input type="file" name="photo"><br><br>

                <button type="submit">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Récupérer le bouton et le formulaire
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const editForm = document.getElementById('edit-form');

    // Ajouter un événement de clic au bouton
    editProfileBtn.addEventListener('click', function() {
        // Basculer l'affichage du formulaire (afficher/masquer)
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
        }
    });
</script>

</body>
</html>
