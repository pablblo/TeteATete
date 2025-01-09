<?php
require 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idInscription = $_POST['idInscription'];

    if (!empty($idInscription)) {
        $stmt = $db->prepare("DELETE FROM inscription WHERE idInscription = :idInscription");
        $result = $stmt->execute(['idInscription' => $idInscription]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Participant retiré avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du retrait du participant.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de l\'inscription non valide.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
