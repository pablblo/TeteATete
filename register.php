<?php
require 'db_connection.php';

// Récupération des données du formulaire
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hachage du mot de passe pour plus de sécurité
$classe = $_POST['classe'];

// Vérification si l'utilisateur existe déjà
$stmt = $db->prepare("SELECT * FROM `User` WHERE `Mail` = ?");
$stmt->execute([$email]);
$userExists = $stmt->fetch();

if ($userExists) {
    die("Cet utilisateur existe déjà.");
}

// Préparation et exécution de la requête SQL d'insertion
$stmt = $db->prepare("INSERT INTO `User` (Nom, Prenom, Mail, Mot_de_passe, Classe) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nom, $prenom, $email, $password, $classe]);

echo "Inscription réussie !";
header("Location: login.php");
exit();
?>
