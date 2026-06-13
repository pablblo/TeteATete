<?php
require 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idCours = $_POST['idCours'];
    $Titre = $_POST['Titre'];
    $Date = $_POST['Date'];
    $Heure = $_POST['Heure'];

    if (!empty($idCours) && !empty($Titre) && !empty($Date) && !empty($Heure)) {
        $stmt = $db->prepare("UPDATE Cours SET Titre = :Titre, Date = :Date, Heure = :Heure WHERE idCours = :idCours");
        $result = $stmt->execute([
            'Titre' => $Titre,
            'Date' => $Date,
            'Heure' => $Heure,
            'idCours' => $idCours
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cours mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du cours.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
