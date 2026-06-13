<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser = $_POST['idUser'];
    $newStatus = $_POST['newStatus']; // 1 pour admin, 0 pour utilisateur standard

    $stmt = $db->prepare("UPDATE User SET Admin = :newStatus WHERE idUser = :idUser");
    if ($stmt->execute(['newStatus' => $newStatus, 'idUser' => $idUser])) {
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut.']);
    }
}
?>
