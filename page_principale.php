<?php
// Inclusion de la connexion à la base de données
require 'db_connection.php';

// Vérification de connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupération des informations utilisateur connecté
$user_id = $_SESSION['user_id'];
try {
    $user_stmt = $db->prepare("SELECT * FROM User WHERE idUser = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
// Gestion de la création de cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $title = $_POST['course_title'];
    $participants = $_POST['participants'];

    try {
        // Préparer et exécuter l'insertion
        $insert_stmt = $db->prepare("INSERT INTO Cours (Titre, Date, Heure, Taille, Places_restants_Eleve, Places_restants_Tuteur)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->execute([
            $title,                          // Titre du cours
            $date,                           // Date
            $time,                           // Heure
            $participants,                   // Nombre de participants
            $participants,                   // Places restantes pour élèves
            1                                // Places restantes pour le tuteur
        ]);

        // Redirection pour recharger la liste des cours
        header("Location: page_principale.php");
        exit();
    } catch (Exception $e) {
        die("Erreur lors de la création du cours : " . $e->getMessage());
    }
}

// Gestion de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_course'])) {
    $course_id = $_POST['course_id'];
    $role = $_POST['role'];

    try {
        // Vérifier les places disponibles
        $check_places_stmt = $db->prepare("SELECT Places_restants_Eleve, Places_restants_Tuteur FROM Cours WHERE idCours = ?");
        $check_places_stmt->execute([$course_id]);
        $course = $check_places_stmt->fetch(PDO::FETCH_ASSOC);

        if ($role === 'eleve' && $course['Places_restants_Eleve'] <= 0) {
            throw new Exception("Aucune place disponible pour les élèves.");
        }
        if ($role === 'instructeur' && $course['Places_restants_Tuteur'] <= 0) {
            throw new Exception("Aucune place disponible pour les tuteurs.");
        }

        // Vérifier si l'utilisateur est déjà inscrit
        $check_stmt = $db->prepare("SELECT * FROM Inscription WHERE idCours = ? AND idUser = ?");
        $check_stmt->execute([$course_id, $user_id]);
        $existing = $check_stmt->fetch();

        if ($existing) {
            throw new Exception("Vous êtes déjà inscrit à ce cours.");
        }

        // Inscrire l'utilisateur
        $insert_stmt = $db->prepare("INSERT INTO Inscription (idCours, idUser, role) VALUES (?, ?, ?)");
        $insert_stmt->execute([$course_id, $user_id, $role]);

        // Mettre à jour les places restantes
        if ($role === 'eleve') {
            $update_places_stmt = $db->prepare("UPDATE Cours SET Places_restants_Eleve = Places_restants_Eleve - 1 WHERE idCours = ?");
        } else {
            $update_places_stmt = $db->prepare("UPDATE Cours SET Places_restants_Tuteur = Places_restants_Tuteur - 1 WHERE idCours = ?");
        }
        $update_places_stmt->execute([$course_id]);

        // Rediriger pour recharger la page
        header("Location: page_principale.php");
        exit();
    } catch (Exception $e) {
        die("Erreur lors de l'inscription : " . $e->getMessage());
    }
}


// Gestion de la désinscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unregister_course'])) {
    $course_id = $_POST['course_id'];

    try {
        // Vérifier le rôle de l'utilisateur dans l'inscription
        $check_role_stmt = $db->prepare("SELECT role FROM Inscription WHERE idCours = ? AND idUser = ?");
        $check_role_stmt->execute([$course_id, $user_id]);
        $user_inscription = $check_role_stmt->fetch(PDO::FETCH_ASSOC);

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

        // Rediriger pour recharger la page
        header("Location: page_principale.php");
        exit();
    } catch (Exception $e) {
        die("Erreur lors de la désinscription : " . $e->getMessage());
    }
}


// Récupération des cours
try {
    $stmt = $db->query("SELECT c.*, 
                               (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
                               (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
                        FROM Cours c");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des inscriptions pour chaque cours
    $inscriptions = [];
    foreach ($courses as $course) {
        $inscription_stmt = $db->prepare("SELECT DISTINCT u.Photo_de_Profil
                                          FROM Inscription i
                                          JOIN User u ON i.idUser = u.idUser
                                          WHERE i.idCours = ?");
        $inscription_stmt->execute([$course['idCours']]);
        $inscriptions[$course['idCours']] = $inscription_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

// Gestion des paramètres de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$participants_filter = isset($_GET['participants']) ? $_GET['participants'] : '';

// Construction dynamique de la clause WHERE
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(Titre LIKE ?)";
    $params[] = '%' . $search . '%';
}

if (!empty($date_filter)) {
    $where_clauses[] = "(Date = ?)";
    $params[] = $date_filter;
}

if (!empty($participants_filter)) {
    if ($participants_filter == '1-5') {
        $where_clauses[] = "(Taille BETWEEN 1 AND 5)";
    } elseif ($participants_filter == '6-10') {
        $where_clauses[] = "(Taille BETWEEN 6 AND 10)";
    } elseif ($participants_filter == '11+') {
        $where_clauses[] = "(Taille >= 11)";
    }
}

// Combinaison des conditions pour la requête SQL
$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Requête SQL avec les filtres appliqués
$stmt = $db->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
    FROM Cours c
    $where_sql
");
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Principale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 30px;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #0061A0;
            font-weight: bold;
        }
        .profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .profile-container {
            display: flex;
            align-items: center;
        }
        #create-course-section {
            display: none; /* Masqué par défaut */
        }
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

        .text-danger {
            font-size: 16px;
            font-weight: bold;
            color: red;
            text-align: center;
        
}


    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Bouton pour ouvrir la section de création de cours -->
    <div class="container my-4">
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
        
    </div>

    <!-- Section de création de cours -->
    <section id="create-course-section" class="create-course-section container">
        <h2>Créer un nouveau cours</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="course_title" class="form-label">Titre du cours</label>
                <input type="text" id="course_title" name="course_title" class="form-control" placeholder="Titre du cours" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Heure</label>
                <input type="time" id="time" name="time" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="participants" class="form-label">Nombre de participants</label>
                <input type="number" id="participants" name="participants" class="form-control" required>
            </div>
            <button type="submit" name="create_course" class="btn btn-primary">Créer le cours</button>
            <br>
            <br>
        </form>
        <br>
    </section>

    <div class="container mb-4">
    <form method="GET" action="page_principale.php" class="row g-3">
        <!-- Barre de recherche -->
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un cours par mot-clé ou titre" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </div>

        <!-- Filtre par date -->
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
        </div>

        <!-- Filtre par nombre de participants -->
        <div class="col-md-3">
            <select name="participants" class="form-select">
                <option value="">Nombre de participants</option>
                <option value="1-5" <?php echo (isset($_GET['participants']) && $_GET['participants'] == '1-5') ? 'selected' : ''; ?>>1-5</option>
                <option value="6-10" <?php echo (isset($_GET['participants']) && $_GET['participants'] == '6-10') ? 'selected' : ''; ?>>6-10</option>
                <option value="11+" <?php echo (isset($_GET['participants']) && $_GET['participants'] == '11+') ? 'selected' : ''; ?>>11 ou plus</option>
            </select>
        </div>

        <!-- Bouton de recherche -->
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-secondary">Rechercher</button>
            <a href="page_principale.php" class="btn btn-primary">Réinitialiser</a>

        </div>
    </form>
</div>


<div class="container">
    <h2 class="text-center mb-4">Liste des cours disponibles</h2>
    <div class="row">
        <?php foreach ($courses as $course): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Titre et informations de base -->
                        <h5 class="card-title"><?php echo htmlspecialchars($course['Titre']); ?></h5>
                        <p><strong>Date :</strong>  <?php echo htmlspecialchars($course['Date']); ?> à <?php echo htmlspecialchars($course['Heure']); ?></p>

                        <!-- Section des élèves inscrits -->
                        <?php
                        // Récupérer le nombre d'élèves inscrits
                        $eleves_count_stmt = $db->prepare("SELECT COUNT(*) as eleves_inscrits FROM Inscription WHERE idCours = ? AND role = 'eleve'");
                        $eleves_count_stmt->execute([$course['idCours']]);
                        $eleves_inscrits = $eleves_count_stmt->fetch(PDO::FETCH_ASSOC)['eleves_inscrits'];
                        ?>
                        <p><strong>Élèves inscrits : </strong><?php echo htmlspecialchars($eleves_inscrits); ?> / <?php echo htmlspecialchars($course['Taille']); ?></p>
                        <div class="profile-container mb-3">
                            <?php
                            // Afficher les photos des élèves inscrits avec lien cliquable
                            $eleve_stmt = $db->prepare("
                                SELECT u.idUser, u.Photo_de_Profil 
                                FROM Inscription i
                                JOIN User u ON i.idUser = u.idUser
                                WHERE i.idCours = ? AND i.role = 'eleve'
                            ");
                            $eleve_stmt->execute([$course['idCours']]);
                            $eleves = $eleve_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($eleves as $eleve): ?>
                                <a href="profil_public.php?id=<?php echo $eleve['idUser']; ?>" title="Voir le profil">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($eleve['Photo_de_Profil']); ?>" 
                                         class="profile-img" 
                                         alt="Profil Élève">
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Section des tuteurs inscrits -->
                        <?php
                        // Récupérer le nombre de tuteurs inscrits
                        $tuteurs_count_stmt = $db->prepare("SELECT COUNT(*) as tuteurs_inscrits FROM Inscription WHERE idCours = ? AND role = 'instructeur'");
                        $tuteurs_count_stmt->execute([$course['idCours']]);
                        $tuteurs_inscrits = $tuteurs_count_stmt->fetch(PDO::FETCH_ASSOC)['tuteurs_inscrits'];
                        ?>
                        <p><strong>Tuteurs inscrits :</strong> <?php echo htmlspecialchars($tuteurs_inscrits); ?> / 1</p>
                        <div class="profile-container mb-3">
                            <?php
                            // Afficher la photo des tuteurs inscrits avec lien cliquable
                            $tuteur_stmt = $db->prepare("
                                SELECT u.idUser, u.Photo_de_Profil 
                                FROM Inscription i
                                JOIN User u ON i.idUser = u.idUser
                                WHERE i.idCours = ? AND i.role = 'instructeur'
                            ");
                            $tuteur_stmt->execute([$course['idCours']]);
                            $tuteurs = $tuteur_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tuteurs as $tuteur): ?>
                                <a href="profil_public.php?id=<?php echo $tuteur['idUser']; ?>" title="Voir le profil">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                         class="profile-img" 
                                         alt="Profil Tuteur">
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Boutons d'inscription -->
                        <form method="POST" action="">
                            <input type="hidden" name="course_id" value="<?php echo $course['idCours']; ?>">

                            <?php
                            // Vérification de l'inscription de l'utilisateur
                            $check_stmt = $db->prepare("SELECT * FROM Inscription WHERE idCours = ? AND idUser = ?");
                            $check_stmt->execute([$course['idCours'], $user_id]);
                            $user_inscription = $check_stmt->fetch();

                            // Logique pour afficher les messages ou les boutons
                            if ($user_inscription) {
                                // Si l'utilisateur est inscrit, afficher son rôle et le bouton "Se désinscrire"
                                echo '<p class="text-success">Vous êtes inscrit en tant que ' . htmlspecialchars($user_inscription['role']) . '.</p>';
                                echo '<button type="submit" name="unregister_course" class="btn btn-danger me-2 mb-2">Se désinscrire</button>';
                            } elseif ($eleves_inscrits >= $course['Taille'] && $tuteurs_inscrits == 1) {
                                // Si le cours est complet (places élèves pleines ET tuteur inscrit)
                                echo '<p class="text-danger">Le cours est complet, recréez-en un !</p>';
                            } else {
                                // Afficher les options d'inscription
                                if ($tuteurs_inscrits == 0) {
                                    echo '<button type="submit" name="register_course" value="instructeur" class="btn btn-secondary me-2 mb-2" onclick="this.form.role.value=\'instructeur\';">S\'inscrire en tant que tuteur</button>';
                                }
                                if ($eleves_inscrits < $course['Taille']) {
                                    echo '<button type="submit" name="register_course" value="eleve" class="btn btn-primary me-2 mb-2" onclick="this.form.role.value=\'eleve\';">S\'inscrire en tant qu\'élève</button>';
                                }
                                echo '<input type="hidden" name="role" value="">';
                            }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <!-- Footer -->
   

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JavaScript -->
    <script>
        const createCourseBtn = document.getElementById('create-course-btn');
        const createCourseSection = document.getElementById('create-course-section');

        createCourseBtn.addEventListener('click', () => {
            createCourseSection.style.display = createCourseSection.style.display === 'none' ? 'block' : 'none';
        });
    </script>
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