<?php

require_once __DIR__ . '/../bootstrap.php';

// Connexion à la base de données

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la connexion si non connecté
    exit();
}

$idUser = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Récupérer le paramètre idCours
if (!isset($_GET['idCours'])) {
    die("Aucun cours sélectionné.");
}

$idCours = (int) $_GET['idCours'];

try {
    // Récupérer le rôle de l'utilisateur et le titre du cours
    $query = "
        SELECT i.role, c.Titre
        FROM inscription i
        INNER JOIN cours c ON i.idCours = c.idCours
        WHERE i.idUser = :idUser AND c.idCours = :idCours
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'idUser' => $idUser,
        'idCours' => $idCours
    ]);

    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        die("Vous n'êtes pas inscrit à ce cours ou ce cours n'existe pas.");
    }

    $role = $course['role']; // Rôle de l'utilisateur (eleve ou instructeur)
    $titreCours = $course['Titre'];

    // Initialisation des utilisateurs à évaluer
    $usersToEvaluate = [];

    if ($role === 'instructeur') {
        // Si l'utilisateur est instructeur, récupérer les élèves inscrits au cours
        $queryEleves = "
            SELECT u.idUser, u.Nom, u.Prenom
            FROM inscription i
            INNER JOIN User u ON i.idUser = u.idUser
            WHERE i.idCours = :idCours AND i.role = 'eleve'
        ";
        $stmtEleves = $db->prepare($queryEleves);
        $stmtEleves->execute(['idCours' => $idCours]);
        $usersToEvaluate = $stmtEleves->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($role === 'eleve') {
        // Si l'utilisateur est élève, récupérer le tuteur du cours
        $queryTuteur = "
            SELECT u.idUser, u.Nom, u.Prenom
            FROM inscription i
            INNER JOIN User u ON i.idUser = u.idUser
            WHERE i.idCours = :idCours AND i.role = 'instructeur'
        ";
        $stmtTuteur = $db->prepare($queryTuteur);
        $stmtTuteur->execute(['idCours' => $idCours]);
        $usersToEvaluate = $stmtTuteur->fetchAll(PDO::FETCH_ASSOC);
    }

    // Débogage : Vérifiez si des utilisateurs sont récupérés
    if (empty($usersToEvaluate)) {
        echo "<pre>";
        echo "Aucun utilisateur trouvé à évaluer pour le cours : " . htmlspecialchars($titreCours) . "\n";
        echo "Rôle : " . htmlspecialchars($role);
        echo "</pre>";
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Boucle sur chaque évaluation envoyée
        foreach ($_POST['evaluations'] as $evaluation) {
            $idUserReceveur = (int) $evaluation['idUserReceveur']; // Récupérer l'ID de l'utilisateur évalué
            $note = (int) $evaluation['note']; // Récupérer la note
            $commentaire = trim($evaluation['commentaire']); // Récupérer le commentaire

            // Validation des données
            if ($note < 1 || $note > 5) {
                throw new Exception("La note doit être comprise entre 1 et 5.");
            }
            if (empty($commentaire)) {
                throw new Exception("Le commentaire ne peut pas être vide.");
            }

            // Insertion dans la base de données
            $insertQuery = "
                INSERT INTO evaluation (Tuteur_ou_Eleve, Note, Commentaire, idUserAuteur, idUserReceveur, idCours)
                VALUES (:tuteur_ou_eleve, :note, :commentaire, :idUserAuteur, :idUserReceveur, :idCours)
            ";
            $stmt = $db->prepare($insertQuery);
            $stmt->execute([
                'tuteur_ou_eleve' => ($role === 'instructeur' ? 0 : 1), // 0 pour élève évalué, 1 pour tuteur évalué
                'note' => $note,
                'commentaire' => $commentaire,
                'idUserAuteur' => $idUser, // L'utilisateur qui effectue l'évaluation
                'idUserReceveur' => $idUserReceveur,
                'idCours' => $idCours
            ]);
        }

        // Redirection après succès
        header("Location: evaluation.php");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

require __DIR__ . '/../vue/pages/submit_evaluation.php';
