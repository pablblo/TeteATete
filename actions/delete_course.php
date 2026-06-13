<?php
require 'db_connection.php'; // Connexion à la base de données

header('Content-Type: application/json'); // Réponse JSON

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idCours = $_POST['idCours']; // Récupérer l'ID du cours

    // Vérifiez que l'ID est valide
    if (!empty($idCours)) {
        $stmt = $db->prepare("DELETE FROM Cours WHERE idCours = :idCours");
        $result = $stmt->execute(['idCours' => $idCours]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cours supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du cours.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID du cours non valide.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
