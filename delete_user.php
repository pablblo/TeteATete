<?php
require 'db_connection.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser = $_POST['idUser']; // Récupérer l'ID de l'utilisateur à supprimer

    // Vérifiez que l'ID est valide
    if (!empty($idUser)) {
        $stmt = $db->prepare("DELETE FROM User WHERE idUser = :idUser");
        $result = $stmt->execute(['idUser' => $idUser]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur non valide.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
