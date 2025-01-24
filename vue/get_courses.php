<?php
// Inclusion de la connexion à la base de données
require 'db_connection.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    generateUrlFromFilename("Location: login.php");
    exit();
}

// Récupération de l'idUser depuis la session
$idUser = $_SESSION['user_id'];

try {
    // Requête SQL pour récupérer les cours auxquels l'utilisateur est inscrit
    $sql = "
    SELECT 
        c.idCours,
        c.Titre,
        COUNT(i.idUser) AS participants, -- Compte le nombre de participants inscrits
        CASE 
            WHEN i.role = 'instructeur' THEN 'Tuteur'
            ELSE 'Élève'
        END AS role
    FROM 
        cours c
    LEFT JOIN 
        inscription i ON c.idCours = i.idCours -- Jointure pour compter les participants
    WHERE 
        i.idUser = :idUser
    GROUP BY 
        c.idCours, i.role
";

    $stmt = $db->prepare($sql); // $db est la connexion incluse via db_connection.php
    $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
    $stmt->execute();

    // Récupération des résultats
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retour des données en JSON
    echo json_encode($courses);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
