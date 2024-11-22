<?php
// Informations de connexion
$host = 'localhost';
$db   = 'BDD_TAT'; // Nom de ta base de données
$user = 'root'; // Utilisateur MySQL par défaut sous XAMPP
$pass = ''; // Mot de passe MySQL par défaut est vide sous XAMPP

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
