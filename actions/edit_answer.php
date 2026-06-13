<?php
require 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $answer = $_POST['answer'];

    $stmt = $db->prepare("UPDATE Forum SET answer = :answer WHERE id = :id");
    $result = $stmt->execute(['answer' => $answer, 'id' => $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Réponse modifiée avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification de la réponse.']);
    }
}
