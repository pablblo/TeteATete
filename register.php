<?php
// Connexion à la base de données
$host = 'localhost';
$db = 'BDD_TAT'; // Assurez-vous que ce nom correspond exactement au nom de la base de données
$user = 'root'; // Utilisateur par défaut dans XAMPP
$pass = ''; // Par défaut dans XAMPP, le mot de passe est vide
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données du formulaire
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hachage du mot de passe pour plus de sécurité
$classe = $_POST['classe'];

// Vérification si l'utilisateur existe déjà
$stmt = $pdo->prepare("SELECT * FROM `User` WHERE `Mail` = ?");
$stmt->execute([$email]);
$userExists = $stmt->fetch();

if ($userExists) {
    die("Cet utilisateur existe déjà.");
}

// Préparation et exécution de la requête SQL d'insertion
$stmt = $pdo->prepare("INSERT INTO `User` (Nom, Prenom, Mail, Mot_de_passe, Classe) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nom, $prenom, $email, $password, $classe]);

echo "Inscription réussie !";
header("Location: login.html");
exit();

?>