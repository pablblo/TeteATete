<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté pour la navbar
$current_user_id = $_SESSION['user_id'];
$current_user_stmt = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$current_user_stmt->execute([$current_user_id]);
$current_user = $current_user_stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'ID du profil à afficher est fourni
if (!isset($_GET['id'])) {
    die("Erreur : ID de profil non spécifié");
}

$profile_id = $_GET['id'];

// Récupérer les informations de l'utilisateur dont on veut voir le profil
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$profile_id]);
$profile_user = $query->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$profile_user) {
    die("Erreur : Utilisateur non trouvé");
}

// Récupérer la moyenne des évaluations
$queryMoyenne = $db->prepare("
    SELECT AVG(Note) as moyenne
    FROM Evaluation
    WHERE idUserReceveur = ?
");
$queryMoyenne->execute([$profile_id]);
$moyenne = $queryMoyenne->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0;

// Récupérer uniquement les cours auxquels l'utilisateur est inscrit
$courses_stmt = $db->prepare("
    SELECT c.*, i.role,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
           (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
    FROM Inscription i
    JOIN Cours c ON i.idCours = c.idCours
    WHERE i.idUser = ?
");
$courses_stmt->execute([$profile_id]);
$user_courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        header("Location: profil_public.php?id=" . $profile_id);
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
        header("Location: profil_public.php?id=" . $profile_id);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($profile_user['Prenom'] . ' ' . $profile_user['Nom']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .profile-img-small {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
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
    </style>
</head>
<body>
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
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>"
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

    <!-- Contenu du profil -->
    <div class="container mt-5">
        <div class="row">
            <!-- Informations principales -->
            <div class="col-md-4 text-center">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($profile_user['Photo_de_Profil']); ?>" 
                     class="profile-img mb-3" 
                     alt="Photo de profil">
                <h2><?php echo htmlspecialchars($profile_user['Prenom'] . " " . $profile_user['Nom']); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($profile_user['Mail']); ?></p>
                <div class="mt-3">
                    <h4>Note moyenne</h4>
                    <p class="h3"><?php echo $moyenne > 0 ? number_format($moyenne, 1) . '/5' : 'Pas encore noté'; ?></p>
                    </div>
            </div>

            <!-- Bio et autres informations -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">À propos</h3>
                        <p><?php echo nl2br(htmlspecialchars($profile_user['Bio'])); ?></p>
                    </div>
                </div>

                <!-- Section des cours -->
                <div class="container mt-5">
                    <h3 class="mb-4 text-center">Cours auxquels <?php echo htmlspecialchars($profile_user['Prenom']); ?> est inscrit</h3>
                    <div class="row">
                        <?php if (!empty($user_courses)): ?>
                            <?php foreach ($user_courses as $course): ?>
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

                                            <!-- Boutons d'inscription/désinscription pour l'utilisateur connecté -->
                                            <form method="POST" action="">
                                                <input type="hidden" name="course_id" value="<?php echo $course['idCours']; ?>">
                                                <?php
                                                // Vérifier si l'utilisateur connecté est inscrit à ce cours
                                                $check_stmt = $db->prepare("SELECT * FROM Inscription WHERE idCours = ? AND idUser = ?");
                                                $check_stmt->execute([$course['idCours'], $current_user_id]);
                                                $user_inscription = $check_stmt->fetch();

                                                if ($user_inscription) {
                                                    echo '<p class="text-success">Vous êtes inscrit en tant que ' . htmlspecialchars($user_inscription['role']) . '.</p>';
                                                    echo '<button type="submit" name="unregister_course" class="btn btn-danger me-2 mb-2">Se désinscrire</button>';
                                                } elseif ($course['eleves_inscrits'] >= $course['Taille'] && $course['tuteurs_inscrits'] >= 1) {
                                                    echo '<p class="text-danger">Le cours est complet.</p>';
                                                } else {
                                                    if ($course['tuteurs_inscrits'] == 0) {
                                                        echo '<button type="submit" name="register_course" value="instructeur" class="btn btn-secondary me-2 mb-2" onclick="this.form.role.value=\'instructeur\';">S\'inscrire en tant que tuteur</button>';
                                                    }
                                                    if ($course['eleves_inscrits'] < $course['Taille']) {
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
                        <?php else: ?>
                            <p class="text-center">Cet utilisateur n'est inscrit à aucun cours pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>    
    <br>
    <br>

    <br>
    <br>
    <br>
    <br>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 