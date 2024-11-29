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
    <link rel="stylesheet" href="styleFAQ.css">
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
        <li><a href="contact.php">Contact</a></li>
        <li><a href="#">FAQ</a></li>
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

    <!-- Section Qui sommes-nous -->
    <section class="about-us">
        <div class="about-text">
            <h1>Qui sommes-nous ?</h1>
            <h2>InnoWave</h2>
            <p>
                Cette plateforme a pour objectif de faciliter l'accès à des cours en ligne et des services de tutorat. Elle permettra aux étudiants de suivre des sessions de formation supplémentaires grâce à la mise en relation d’un tuteur, de recevoir de l'aide via des tuteurs et d'organiser des séances de tutorat de manière flexible.
                L'application s'inscrit dans une démarche de soutien pédagogique pour améliorer la performance académique des étudiants tout en favorisant l'entraide.
            </p>
        </div>
        <div class="about-image">
            <img src="images/APPlogo.png" style="height: 250px; width: 250px;"  alt="Logo InnoWave">
        </div>
    </section>

    <!-- Section FAQ -->
    <section class="faq">
        <h2>FAQ</h2>

        <div class="faq-item">
            <h3>Politique d'Annulation</h3>
            <h4>Que se passe-t-il si je dois annuler une séance de tutorat ?</h4>
            <p>
                Si vous devez annuler une séance, vous pouvez le faire directement via votre espace personnel. Nous vous demandons d’annuler au moins 24 heures à l'avance pour éviter tout frais d'annulation. Si l’annulation est faite en dehors de ce délai, des frais peuvent être appliqués. Veuillez consulter notre politique d'annulation pour plus de détails.
            </p>
        </div>

        <div class="faq-item">
            <h3>Assistance Technique</h3>
            <h4>Qui puis-je contacter si je rencontre un problème technique sur la plateforme ?</h4>
            <p>
                Si vous rencontrez un problème technique sur la plateforme, vous pouvez contacter le support technique via l'e-mail : support.client@gmail.com ou le formulaire de contact mis à disposition en haut de la page. Vous pouvez aussi consulter la section FAQ pour résoudre les problèmes courants.
            </p>
        </div>
        <div class="faq-item">
            <h3>Modification Profile</h3>
            <h4>Puis-je changer mes informations personnelles ?</h4>
            <p>
                Oui, vous pouvez modifier vos informations personnelles en vous rendant dans la section "Profil" après vous être connecté. Cliquez sur "Modifier" pour mettre à jour vos données.

            </p>
        </div>
        <div class="faq-item">
            <h3>Réservation</h3>
            <h4>Comment réserver un cours ?</h4>
            <p>
                Après vous être connecté, allez dans la section "Cours", sélectionnez celui qui vous intéresse, et cliquez sur "Réserver". Assurez-vous qu'il reste des places disponibles pour les tuteurs ou élèves.

            </p>
        </div>
        <div class="faq-item">
            <h3>Evaluation</h3>
            <h4>Comment évaluer un tuteur ou un élève ?</h4>
            <p>
                Une fois le cours terminé, vous pouvez évaluer le tuteur ou l'élève en accédant à la page du cours et en laissant une note et un commentaire.

            </p>
        </div>
    </section>

</div>

</body>
</html>
