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

// Récupérer les informations de l'utilisateur
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Création de cours si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $courseTitle = htmlspecialchars($_POST['course_title']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $participants = $_POST['participants'];
    $role = $_POST['role'];

    if ($participants === '6+') {
        $participants = 6; // Limite maximale ou ajustez selon vos besoins
    }

    try {
        // Insertion dans la table Cours
        $stmt = $db->prepare("
            INSERT INTO Cours (Titre, Date, Heure, Taille, Places_restants_Tuteur, Places_restants_Eleve)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($role === 'instructeur') {
            $stmt->execute([$courseTitle, $date, $time, $participants, 0, $participants]);
        } else {
            $stmt->execute([$courseTitle, $date, $time, $participants, 1, $participants - 1]);
        }

        // Récupérer l'ID du cours créé
        $idCours = $db->lastInsertId();

        // Inscrire le créateur dans le cours
        $stmt = $db->prepare("
            INSERT INTO User_Cours (Tuteur_ou_Eleve, idUser, idCours)
            VALUES (?, ?, ?)
        ");
        $roleValue = ($role === 'instructeur') ? 1 : 0; // 1 = Tuteur, 0 = Élève
        $stmt->execute([$roleValue, $user_id, $idCours]);

        // Redirection après la création
        header("Location: page_principale.php?success=course_created");
        exit();
    } catch (PDOException $e) {
        header("Location: page_principale.php?error=database_error");
        exit();
    }
}

// Inscription à un cours si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_course'])) {
    $idCours = $_POST['id_cours'];
    $role = $_POST['role']; // "eleve" ou "instructeur"

    try {
        // Vérifier les places disponibles
        $stmt = $db->prepare("SELECT * FROM Cours WHERE idCours = ?");
        $stmt->execute([$idCours]);
        $cours = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cours) {
            header("Location: profil.php?error=course_not_found");
            exit();
        }

        if ($role === 'instructeur' && $cours['Places_restants_Tuteur'] > 0) {
            // Inscription comme instructeur
            $stmt = $db->prepare("
                INSERT INTO User_Cours (Tuteur_ou_Eleve, idUser, idCours)
                VALUES (1, ?, ?)
            ");
            $stmt->execute([$user_id, $idCours]);

            // Mise à jour des places restantes
            $stmt = $db->prepare("
                UPDATE Cours SET Places_restants_Tuteur = Places_restants_Tuteur - 1
                WHERE idCours = ?
            ");
            $stmt->execute([$idCours]);
        } elseif ($role === 'eleve' && $cours['Places_restants_Eleve'] > 0) {
            // Inscription comme élève
            $stmt = $db->prepare("
                INSERT INTO User_Cours (Tuteur_ou_Eleve, idUser, idCours)
                VALUES (0, ?, ?)
            ");
            $stmt->execute([$user_id, $idCours]);

            // Mise à jour des places restantes
            $stmt = $db->prepare("
                UPDATE Cours SET Places_restants_Eleve = Places_restants_Eleve - 1
                WHERE idCours = ?
            ");
            $stmt->execute([$idCours]);
        } else {
            // Pas de places disponibles
            header("Location: profil.php?error=no_places_available");
            exit();
        }

        // Redirection après inscription
        header("Location: profil.php?success=joined_course");
        exit();
    } catch (PDOException $e) {
        header("Location: profil.php?error=database_error");
        exit();
    }
}



// Récupérer tous les cours pour affichage
$courses = $db->query("SELECT * FROM Cours ORDER BY Date, Heure")->fetchAll(PDO::FETCH_ASSOC);
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
        <li><a href="page_principale.php">Cours</a></li>
        <li><a href="profil.php" class="user-profile">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>"
                 style="object-fit: cover; height: 50px; width: 50px !important; border: 1px solid #ddd; border-radius: 50%;"
                 alt="Photo de profil">
        </a></li>
        <li><a href="login.html">Déconnexion</a></li>
    </ul>
</nav>

<br>
<br>

<!-- Bouton pour ouvrir la section de création de cours -->
<button 
    id="create-course-btn" 
    class="btn-create-course"
    style="
        background-color: #0061A0;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 18px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: block;
        margin: 0 auto;
        cursor: pointer;
        text-align: center;
        transition: background-color 0.3s ease;"
    onmouseover="this.style.backgroundColor='#004f80';"
    onmouseout="this.style.backgroundColor='#0061A0';">
    Créer un cours
</button>

<!-- Section de création de cours masquée par défaut -->
<section id="create-course-section" class="create-course-section" style="display: none;">
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

<section class="create-course-section">
    <h2>Liste des cours disponibles</h2>
</section>

<section class="create-course-section">

    <div class="courses-container">
        <?php foreach ($courses as $course): ?>
        <div class="course-card">
            <div class="course-header">
                <p class="course-date"><?php echo date('d M, Y', strtotime($course['Date'])); ?></p>
                <p class="course-time"><?php echo date('H:i', strtotime($course['Heure'])); ?></p>
            </div>
            <h3 class="course-title"><?php echo htmlspecialchars($course['Titre']); ?></h3>

            <!-- Étudiants inscrits -->
            <div class="course-students">
                <p><strong>Étudiant(s) inscrit :</strong></p>
                <div class="student-icons">
                    <!-- Afficher les avatars -->
                    <img class="small-avatar" src="images/default-user.png" alt="Étudiant 1">
                    <img class="small-avatar" src="images/default-user.png" alt="Étudiant 2">
                    <span>+<?php echo max(0, $course['Taille'] - $course['Places_restants_Eleve']); ?></span>
                </div>
                <p><?php echo $course['Places_restants_Eleve']; ?> place(s) restante(s) sur <?php echo $course['Taille']; ?></p>
            </div>

            <!-- Bouton pour s'inscrire en tant qu'élève -->
            <form method="POST" action="">
                <input type="hidden" name="id_cours" value="<?php echo $course['idCours']; ?>">
                <input type="hidden" name="role" value="eleve">
                <button type="submit" name="join_course" class="btn-student">S'inscrire en tant qu'élève</button>
            </form>

            <!-- Instructeur inscrit -->
            <div class="course-instructor">
                <p><strong>Instructeur inscrit :</strong></p>
                <div class="instructor-icons">
                    <!-- Afficher l'avatar de l'instructeur -->
                    <img class="small-avatar" src="images/default-user.png" alt="Instructeur">
                </div>
                <p><?php echo $course['Places_restants_Tuteur'] == 0 ? "0 place restante" : "1 place restante"; ?></p>
            </div>

            <!-- Bouton pour s'inscrire en tant qu'instructeur -->
            <form method="POST" action="">
                <input type="hidden" name="id_cours" value="<?php echo $course['idCours']; ?>">
                <input type="hidden" name="role" value="instructeur">
                <button type="submit" name="join_course" class="btn-instructor">S'inscrire en tant que tuteur</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const createCourseBtn = document.getElementById("create-course-btn");
        const createCourseSection = document.getElementById("create-course-section");

        // Ajouter un événement au clic sur le bouton
        createCourseBtn.addEventListener("click", () => {
            // Basculer la visibilité de la section
            if (createCourseSection.style.display === "none" || createCourseSection.style.display === "") {
                createCourseSection.style.display = "block";
                createCourseBtn.textContent = "Fermer la création de cours"; // Changer le texte du bouton
            } else {
                createCourseSection.style.display = "none";
                createCourseBtn.textContent = "Créer un cours"; // Revenir au texte initial
            }
        });
    });
</script>

</body>
</html>
