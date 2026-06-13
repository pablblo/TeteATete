<?php

require_once __DIR__ . '/../bootstrap.php';

// Inclusion du fichier de connexion à la base de données
// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

require __DIR__ . '/../vue/pages/profil.php';
