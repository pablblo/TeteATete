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

// Création de cours si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $courseTitle = $_POST['course_title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $participants = $_POST['participants'];
    $role = $_POST['role']; // "eleve" ou "instructeur"

    // Insérer dans la table Cours
    $stmt = $db->prepare("
        INSERT INTO Cours (Titre, Date, Heure, Taille, Places_restants_Tuteur, Places_restants_Eleve)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($role === 'instructeur') {
        $stmt->execute([$courseTitle, $date, $time, $participants, 1, $participants]);
    } else {
        $stmt->execute([$courseTitle, $date, $time, $participants, $participants, 1]);
    }

    // Récupérer l'ID du cours créé
    $idCours = $db->lastInsertId();

    // Insérer dans la table User_Cours
    $stmt = $db->prepare("
        INSERT INTO User_Cours (Tuteur_ou_Eleve, idUser, idCours)
        VALUES (?, ?, ?)
    ");
    $roleValue = ($role === 'instructeur') ? 1 : 0; // 1 = Tuteur, 0 = Élève
    $stmt->execute([$roleValue, $user_id, $idCours]);

    // Redirection après la création du cours
    header("Location: profil.php?success=course_created");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tête à Tête - Accueil</title>
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
        <li><a href="FAQ.php">FAQ</a></li>
        <li><a href="cgu.php">CGU</a></li>
        <li><a href="mentionslegales.php">Mentions Légales</a></li>
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

<!-- Section de création de cours -->
<section class="create-course-section">
    <h2>Créer un nouveau cours</h2>
    <form method="POST" action="">
        <!-- Date et heure -->
        <div class="date-time">
            <div>
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div>
                <label for="time">Heure</label>
                <input type="time" id="time" name="time" required>
            </div>
        </div>
        
        <!-- Titre du cours -->
        <label for="course_title">Titre du cours</label>
        <input type="text" id="course_title" name="course_title" placeholder="Titre du cours" required>

        <!-- Nombre de participants -->
        <label for="participants">Nombre de participants</label>
        <select id="participants" name="participants" required>
            <option value="">Sélectionnez</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6+">6 ou plus</option>
        </select>

        <!-- Rôle -->
        <label>Rôle</label>
        <div>
            <label class="role-btn">
                <input type="radio" name="role" value="eleve" required> Élève
            </label>
            <label class="role-btn">
                <input type="radio" name="role" value="instructeur" required> Instructeur
            </label>
        </div>

        <!-- Bouton de création -->
        <button type="submit" name="create_course">Créer le cours</button>
    </form>
</section>

</body>
</html>
