<?php

require_once __DIR__ . '/../bootstrap.php';

// Inclusion de la connexion à la base de données
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
    // Exécuter la requête SQL pour récupérer les cours
    $stmt = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'eleve') AS eleves_inscrits,
               (SELECT COUNT(*) FROM Inscription WHERE idCours = c.idCours AND role = 'instructeur') AS tuteurs_inscrits
        FROM Cours c
        WHERE TIMESTAMP(c.Date, c.Heure) > NOW() - INTERVAL 5 HOUR
    ");

    // Vérifiez si la requête a été correctement exécutée
    if (!$stmt) {
        throw new Exception("Échec de l'exécution de la requête SQL.");
    }

    // Récupérer les résultats
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si aucun cours trouvé, initialisez un tableau vide
    if (!$courses) {
        $courses = [];
    }

    // Récupération des inscriptions pour chaque cours
    $inscriptions = [];
    foreach ($courses as $course) {
        $inscription_stmt = $db->prepare("
            SELECT DISTINCT u.Photo_de_Profil
            FROM Inscription i
            JOIN User u ON i.idUser = u.idUser
            WHERE i.idCours = ?
        ");
        $inscription_stmt->execute([$course['idCours']]);
        $inscriptions[$course['idCours']] = $inscription_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Gérer les erreurs PDO
    die("Erreur PDO : " . $e->getMessage());
} catch (Exception $e) {
    // Gérer les erreurs générales
    die("Erreur : " . $e->getMessage());
}

// Gestion des paramètres de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$participants_filter = isset($_GET['participants']) ? $_GET['participants'] : '';

// Construction dynamique de la clause WHERE
// Ajout de la condition temporelle à toutes les requêtes
$where_clauses[] = "TIMESTAMP(c.Date, c.Heure) > NOW() - INTERVAL 5 HOUR";
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

require __DIR__ . '/../vue/pages/page_principale.php';
