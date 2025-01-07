<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Récupérer la moyenne des évaluations d'un utilisateur
$queryMoyenne = $db->prepare("
    SELECT AVG(Note) as moyenne
    FROM Evaluation
    WHERE idUserReceveur = ?
");
$queryMoyenne->execute([$user_id]);
$moyenne = $queryMoyenne->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0;

// Récupérer les cours auxquels l'utilisateur est inscrit
$courses_stmt = $db->prepare("
    SELECT c.*, i.role,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
    FROM Inscription i
    JOIN Cours c ON i.idCours = c.idCours
    WHERE i.idUser = ?
");
$courses_stmt->execute([$user_id]);
$user_courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Gestion de la mise à jour du profil
    if ($action === 'update_profile') {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        $photo_de_profil = null;

        // Si une nouvelle photo de profil est téléchargée
        if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK){
            $photo_de_profil = file_get_contents($_FILES['photo_de_profil']['tmp_name']);
        }

        try {
            if ($photo_de_profil) {
                // Mettre à jour les informations avec la photo de profil
                $update_query = $db->prepare("
                    UPDATE User
                    SET Nom = ?, Prenom = ?, Mail = ?, Bio = ?, Photo_de_Profil = ?
                    WHERE idUser = ?
                ");
                $update_query->execute([$nom, $prenom, $email, $bio, $photo_de_profil, $user_id]);
            } else {
                // Mettre à jour les informations sans la photo de profil
                $update_query = $db->prepare("
                    UPDATE User
                    SET Nom = ?, Prenom = ?, Mail = ?, Bio = ?
                    WHERE idUser = ?
                ");
                $update_query->execute([$nom, $prenom, $email, $bio, $user_id]);
            }

            // Recharger la page pour voir les nouvelles informations
            header("Location: profil.php");
            exit();
        } catch (Exception $e) {
            $error_message = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
            echo $e;
        }
    }

    // Gestion de la désinscription
    if ($action === 'unregister_course') {
        $course_id = $_POST['course_id'];

        try {
            // Vérifier si l'utilisateur est inscrit
            $check_inscription_stmt = $db->prepare("SELECT role FROM Inscription WHERE idCours = ? AND idUser = ?");
            $check_inscription_stmt->execute([$course_id, $user_id]);
            $user_inscription = $check_inscription_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user_inscription) {
                throw new Exception("Vous n'êtes pas inscrit à ce cours.");
            }

            // Supprimer l'inscription
            $delete_stmt = $db->prepare("DELETE FROM Inscription WHERE idCours = ? AND idUser = ?");
            $delete_stmt->execute([$course_id, $user_id]);

            // Mettre à jour les places restantes
            if ($user_inscription['role'] === 'eleve') {
                $update_places_stmt = $db->prepare("UPDATE Cours SET Places_restants_Eleve = Places_restants_Eleve + 1 WHERE idCours = ?");
            } else {
                $update_places_stmt = $db->prepare("UPDATE Cours SET Places_restants_Tuteur = Places_restants_Tuteur + 1 WHERE idCours = ?");
            }
            $update_places_stmt->execute([$course_id]);

            // Rediriger pour recharger la page et mettre à jour la liste des cours
            header("Location: profil.php");
            exit();
        } catch (Exception $e) {
            $error_message = "Erreur lors de la désinscription : " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .rating img {
            width: 30px;
            height: 30px;
        }
        .form-container {
            display: none;
            margin-top: 20px;
        }
        .profile-container {
            margin-top: 30px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #0061A0;
            font-weight: bold;
        }
        .profile-img-small {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 5px;
    }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Profil -->
    <div class="container text-center mt-5">
        <h1><?php echo htmlspecialchars($user['Prenom']) . " " . htmlspecialchars($user['Nom']); ?></h1>
        <?php 
        if (!empty($user['Photo_de_Profil'])) {
            // Utiliser la photo de profil si elle existe
            $image_src = 'data:image/jpeg;base64,' . base64_encode($user['Photo_de_Profil']);
        } else {
        // Image par défaut si la photo n'existe pas
            $image_src = 'images/default_profile.png'; // Chemin vers votre image par défaut
        }
        ?>
        <img src="<?php echo $image_src; ?>" class="profile-img" alt="Photo de profil">
        <p><?php echo htmlspecialchars($user['Bio'] ?? ''); ?></p>
        <p><strong>Email :</strong> <?php echo htmlspecialchars($user['Mail']); ?></p>

        <button id="edit-profile-btn" class="btn btn-primary">Modifier le profil</button>
        <div id="edit-form" class="form-container">
            <form action="profil.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['Nom']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email :</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['Mail']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio :</label>
                    <textarea name="bio" class="form-control" required><?php echo htmlspecialchars($user['Bio'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="photo_de_profil" class="form-label">Photo de profil :</label>
                    <input type="file" name="photo_de_profil" class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Mettre à jour</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('edit-profile-btn').addEventListener('click', () => {
            const form = document.getElementById('edit-form');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        });
    </script>

<div class="container mt-5">
    <h3 class="mb-4 text-center">Cours auxquels vous êtes inscrit</h3>
    <div class="row">
        <?php if (!empty($user_courses)): ?>
            <?php foreach ($user_courses as $course): ?>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <!-- Titre et informations de base -->
                            <h5 class="card-title"><?php echo htmlspecialchars($course['Titre']); ?></h5>
                            <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?> à <?php echo htmlspecialchars($course['Heure']); ?></p>

                            <!-- Section des élèves inscrits -->
                            <p><strong>Élèves inscrits :</strong> <?php echo htmlspecialchars($course['eleves_inscrits']); ?> / <?php echo htmlspecialchars($course['Taille']); ?></p>
                            <div class="profile-container mb-3">
                                <?php
                                // Modifier la requête pour récupérer aussi l'ID de l'utilisateur
                                $eleve_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                            FROM Inscription i
                                                            JOIN User u ON i.idUser = u.idUser
                                                            WHERE i.idCours = ? AND i.role = 'eleve'");
                                $eleve_stmt->execute([$course['idCours']]);
                                $eleves = $eleve_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($eleves as $eleve): ?>
                                    <a href="profil_public.php?id=<?php echo $eleve['idUser']; ?>">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($eleve['Photo_de_Profil']); ?>" 
                                             class="profile-img-small" 
                                             alt="Profil Élève"
                                             title="Voir le profil">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <!-- Section des tuteurs inscrits -->
                            <p><strong>Tuteurs inscrits :</strong> <?php echo htmlspecialchars($course['tuteurs_inscrits']); ?> / 1</p>
                            <div class="profile-container mb-3">
                                <?php
                                // Modifier la requête pour récupérer aussi l'ID de l'utilisateur
                                $tuteur_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                             FROM Inscription i
                                                             JOIN User u ON i.idUser = u.idUser
                                                             WHERE i.idCours = ? AND i.role = 'instructeur'");
                                $tuteur_stmt->execute([$course['idCours']]);
                                $tuteurs = $tuteur_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tuteurs as $tuteur): ?>
                                    <a href="profil_public.php?id=<?php echo $tuteur['idUser']; ?>">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                             class="profile-img-small" 
                                             alt="Profil Tuteur"
                                             title="Voir le profil">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <!-- Boutons d'inscription -->
                            <form action="profil.php" method="POST">
                                <input type="hidden" name="action" value="unregister_course">
                            <input type="hidden" name="course_id" value="<?php echo $course['idCours']; ?>">
                            <button type="submit" class="btn btn-danger">Se désinscrire</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Vous n'êtes inscrit à aucun cours pour le moment.</p>
        <?php endif; ?>
    </div>
</div>



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
</body>
</html>