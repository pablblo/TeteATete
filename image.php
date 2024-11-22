<?php
require 'db_connection.php'; // Connexion à la base de données

$userId = $_GET['idUser']; // L'ID de l'utilisateur, par exemple, passé dans l'URL

$stmt = $pdo->prepare("SELECT Photo_de_Profil FROM User WHERE idUser = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['Photo_de_Profil']) {
    header('Content-Type: image/jpeg'); // Change en fonction du type d'image (ex: image/png pour PNG)
    echo $user['Photo_de_Profil'];
} else {
    // Redirige ou affiche une image par défaut si l'utilisateur n'a pas de photo
    header('Content-Type: image/png');
    readfile('default-avatar.png');
}
?>
