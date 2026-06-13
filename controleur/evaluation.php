<?php

require_once __DIR__ . '/../bootstrap.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la connexion si non connecté
    exit();
}

$idUser = $_SESSION['user_id']; // ID de l'utilisateur connecté

try {
    // Récupérer les cours terminés éligibles pour une évaluation
    $query = "
        SELECT c.idCours, c.Titre, c.Date, c.Heure
        FROM inscription i
        INNER JOIN cours c ON i.idCours = c.idCours
        WHERE i.idUser = :idUser
          AND TIMESTAMP(c.Date, c.Heure) <= NOW() - INTERVAL 5 HOUR
          AND c.idCours NOT IN (
              SELECT idCours
              FROM evaluation
              WHERE idUserAuteur = :idUser
          )
    ";
    $stmt = $db->prepare($query);
    $stmt->execute(['idUser' => $idUser]);

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des cours : " . $e->getMessage());
}

require __DIR__ . '/../vue/pages/evaluation.php';
