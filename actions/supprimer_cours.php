<?php

require_once __DIR__ . '/../bootstrap.php';

try {
    $query = "DELETE FROM Cours WHERE TIMESTAMPDIFF(HOUR, CONCAT(`Date`, ' ', `Heure`), NOW()) >= 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    echo 'Cours expirés supprimés avec succès.';
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
