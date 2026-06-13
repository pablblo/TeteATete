<?php
require 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $stmt = $db->prepare("DELETE FROM Forum WHERE id = :id");
    $result = $stmt->execute(['id' => $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Question supprimée avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de la question.']);
    }
}
