<?php

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

if (!isset($_GET['idCours'])) {
    echo json_encode(['error' => 'idCours est manquant.']);
    exit;
}

$idCours = (int) $_GET['idCours'];

try {
    $sql = "SELECT 
                m.idMessage,
                m.message,
                m.timestamp,
                u.Nom,
                u.Prenom,
                u.Photo_de_Profil,
                i.role
            FROM 
                message m
            INNER JOIN 
                User u ON m.idUser = u.idUser
            INNER JOIN 
                inscription i ON m.idCours = i.idCours AND m.idUser = i.idUser
            WHERE 
                m.idCours = :idCours
            ORDER BY 
                m.timestamp ASC";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':idCours', $idCours, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$message) {
        if (!empty($message['Photo_de_Profil'])) {
            $message['Photo_de_Profil'] = base64_encode($message['Photo_de_Profil']);
        }
    }

    echo json_encode($messages);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
