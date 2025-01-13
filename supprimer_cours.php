<?php
// Configuration de la base de données
$host = '127.0.0.1';
$dbname = 'bdd_tat';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour supprimer les cours expirés (10 heures après leur début)
    $query = "DELETE FROM cours WHERE TIMESTAMPDIFF(HOUR, CONCAT(`Date`, ' ', `Heure`), NOW()) >= 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    echo "Cours expirés supprimés avec succès.";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
