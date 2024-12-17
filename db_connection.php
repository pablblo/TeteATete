<?php
// Détails de connexion à la base de données
$host = 'localhost'; // Adresse du serveur (localhost si c'est en local)
$dbname = 'bdd_tat'; // Nom de ta base de données
$username = 'root';  // Nom d'utilisateur MySQL
$password = '';      // Mot de passe MySQL

try {
    // Connexion à la base de données avec PDO (PHP Data Objects)
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Paramétrage pour afficher les erreurs en cas de problème
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, afficher un message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>