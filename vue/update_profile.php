<?php
require 'db_connection.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    generateUrlFromFilename("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Traiter les données soumises
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];

    // Gérer l'upload de l'image de profil
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK) {
        $photo_de_profil = file_get_contents($_FILES['photo_de_profil']['tmp_name']);
        $query = $db->prepare("UPDATE User SET Nom = ?, Prenom = ?, Mail = ?, Bio = ?, Photo_de_Profil = ? WHERE idUser = ?");
        $query->execute([$nom, $prenom, $email, $bio, $photo_de_profil, $user_id]);
    } else {
        $query = $db->prepare("UPDATE User SET Nom = ?, Prenom = ?, Mail = ?, Bio = ? WHERE idUser = ?");
        $query->execute([$nom, $prenom, $email, $bio, $user_id]);
    }

    // Rediriger vers le profil mis à jour
    generateUrlFromFilename("Location: profile.php");
    exit();
}
?>