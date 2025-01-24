<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    generateUrlFromFilename("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT Nom, Prenom, Mail, Bio, Photo_de_Profil, Classe FROM User WHERE idUser = ?");
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
    WHERE i.idUser = ? AND TIMESTAMP(c.Date, c.Heure) > NOW() - INTERVAL 5 HOUR
");
$courses_stmt->execute([$user_id]);
$user_courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

$old_courses_stmt = $db->prepare("
    SELECT c.*, i.role,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
    FROM Inscription i
    JOIN Cours c ON i.idCours = c.idCours
    WHERE i.idUser = ? AND TIMESTAMP(c.Date, c.Heure) <= NOW() - INTERVAL 5 HOUR
");
$old_courses_stmt->execute([$user_id]);
$old_courses = $old_courses_stmt->fetchAll(PDO::FETCH_ASSOC);



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_profile') {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        $photo_de_profil = null;

        // Si une photo est téléchargée
        if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK) {
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

            header('Content-Type: application/json');

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'reset_photo') {
        try {
            // Réinitialiser la photo de profil en la mettant à NULL
            $reset_query = $db->prepare("UPDATE User SET Photo_de_Profil = NULL WHERE idUser = ?");
            $reset_query->execute([$user_id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
        exit();
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
    <link rel="stylesheet" href="style/style_profil.css">
</head>
<body>
    <?php include 'vue/navbar.php'; ?>
    <!-- Profil -->
    <div class="container text-center mt-5">
        <h1><?php echo htmlspecialchars($user['Prenom']) . " " . htmlspecialchars($user['Nom']); ?></h1>
        <br>
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
        <br>
        <br>

        <p><?php echo htmlspecialchars($user['Bio'] ?? ''); ?></p>
        <p><strong>Email :</strong> <?php echo htmlspecialchars($user['Mail']); ?></p>
        <p><strong>Classe :</strong> <?php echo htmlspecialchars($user['Classe'] ?? 'Non spécifiée'); ?></p>


        <button id="edit-profile-btn" class="btn btn-primary" onclick="openEditProfileModal()">Modifier le profil</button>
        
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
                                    <a href="index.php?cible=generique&function=profil_public&id=<?php echo $eleve['idUser']; ?>">
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
                                    <a href="index.php?cible=generique&function=profil_public&id=<?php echo $tuteur['idUser']; ?>">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                             class="profile-img-small" 
                                             alt="Profil Tuteur"
                                             title="Voir le profil">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <!-- Boutons d'inscription -->
                            <form action="" method="POST">
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
<h3 class="mb-4 text-center">Anciens Cours</h3>
<div class="row">
    <?php if (!empty($old_courses)): ?>
        <?php foreach ($old_courses as $course): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Titre et informations de base -->
                        <h5 class="card-title"><?php echo htmlspecialchars($course['Titre']); ?></h5>
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?> à <?php echo htmlspecialchars($course['Heure']); ?></p>
                        <p><strong>Rôle :</strong> <?php echo $course['role'] === 'instructeur' ? 'Tuteur' : 'Élève'; ?></p>

                        <!-- Section des élèves inscrits -->
                        <p><strong>Élèves inscrits :</strong> <?php echo htmlspecialchars($course['eleves_inscrits']); ?> / <?php echo htmlspecialchars($course['Taille']); ?></p>
                        <div class="profile-container mb-3">
                            <?php
                            $eleve_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                        FROM Inscription i
                                                        JOIN User u ON i.idUser = u.idUser
                                                        WHERE i.idCours = ? AND i.role = 'eleve'");
                            $eleve_stmt->execute([$course['idCours']]);
                            $eleves = $eleve_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($eleves as $eleve): ?>
                                <a href="index.php?cible=generique&function=profil_public&id=<?php echo $eleve['idUser']; ?>">
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
                            $tuteur_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                         FROM Inscription i
                                                         JOIN User u ON i.idUser = u.idUser
                                                         WHERE i.idCours = ? AND i.role = 'instructeur'");
                            $tuteur_stmt->execute([$course['idCours']]);
                            $tuteurs = $tuteur_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tuteurs as $tuteur): ?>
                                <a href="index.php?cible=generique&function=profil_public&id=<?php echo $tuteur['idUser']; ?>">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                         class="profile-img-small" 
                                         alt="Profil Tuteur"
                                         title="Voir le profil">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">Aucun ancien cours trouvé.</p>
    <?php endif; ?>
</div>

<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Modifier le profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label for="edit-nom" class="form-label">Nom :</label>
                        <input type="text" id="edit-nom" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-prenom" class="form-label">Prénom :</label>
                        <input type="text" id="edit-prenom" name="prenom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email :</label>
                        <input type="email" id="edit-email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-bio" class="form-label">Bio :</label>
                        <textarea id="edit-bio" name="bio" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-photo" class="form-label">Photo de profil :</label>
                        <input type="file" id="edit-photo" name="photo_de_profil" class="form-control">
                        <!-- Bouton de réinitialisation de la photo de profil -->
                        <button type="button" id="reset-photo-btn" class="btn btn-secondary mt-2">Réinitialiser la photo de profil</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="submitEditProfile()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Ouvre la modale de modification du profil avec les données actuelles
    function openEditProfileModal() {
        document.getElementById('edit-nom').value = "<?php echo htmlspecialchars($user['Nom']); ?>";
        document.getElementById('edit-prenom').value = "<?php echo htmlspecialchars($user['Prenom']); ?>";
        document.getElementById('edit-email').value = "<?php echo htmlspecialchars($user['Mail']); ?>";
        document.getElementById('edit-bio').value = "<?php echo htmlspecialchars($user['Bio'] ?? ''); ?>";
        const editProfileModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        editProfileModal.show();
    }

    // Soumet le formulaire de modification via AJAX
    function submitEditProfile() {
        const formData = new FormData(document.getElementById('editProfileForm'));

        fetch('profil.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profil mis à jour avec succès !');
                location.reload();
            } else {
                alert(data.message || 'Une erreur est survenue.');
            }
        })
        .catch(error => console.error('Erreur :', error));
    }

    // Fonction pour réinitialiser la photo de profil
    document.getElementById('reset-photo-btn').addEventListener('click', () => {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser votre photo de profil ?')) {
            fetch('profil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'reset_photo'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Photo de profil réinitialisée avec succès !');
                    location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue.');
                }
            })
            .catch(error => console.error('Erreur :', error));
        }
    });
</script>


<div style="height: 56px;"></div>
    <footer class="bg-light text-center py-3 mt-5 fixed-bottom">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="index.php?cible=generique&function=CGU" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="index.php?cible=generique&function=mentionslegales" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
    </footer>
</body>
</html>