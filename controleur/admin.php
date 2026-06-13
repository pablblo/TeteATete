<?php

require_once __DIR__ . '/../bootstrap.php';

// Connexion à la base de données


// Vérification de connexion administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['Admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Requête pour récupérer les cours avec les inscriptions
$query = "
    SELECT 
        c.idCours,
        c.Titre,
        c.Date,
        c.Heure,
        COUNT(CASE WHEN i.role = 'eleve' THEN 1 END) AS nbEleves,
        COUNT(CASE WHEN i.role = 'instructeur' THEN 1 END) AS nbInstructeurs
    FROM 
        Cours c
    LEFT JOIN 
        inscription i ON c.idCours = i.idCours
    GROUP BY 
        c.idCours
";
$courses = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);


$courseParticipants = [];
foreach ($courses as $course) {
    $stmt = $db->prepare("
        SELECT i.idInscription, u.Nom, u.Prenom, u.Mail, i.role 
        FROM inscription i
        JOIN User u ON i.idUser = u.idUser
        WHERE i.idCours = :idCours
    ");
    $stmt->execute(['idCours' => $course['idCours']]);
    $courseParticipants[$course['idCours']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des utilisateurs
$users = $db->query("SELECT * FROM User")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des questions du forum
$questions = $db->query("SELECT * FROM Forum")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../vue/pages/admin.php';
