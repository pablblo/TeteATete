<?php

require_once __DIR__ . '/../bootstrap.php';

// Inclusion du fichier de connexion à la base de données
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer la requête de recherche
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    header("Location: page_principale.php");
    exit();
}

try {
    // Rechercher les utilisateurs par prénom et nom
    $stmt = $db->prepare("
        SELECT idUser, Prenom, Nom, Photo_de_Profil 
        FROM User 
        WHERE CONCAT(Prenom, ' ', Nom) LIKE ?
    ");
    $stmt->execute(['%' . $query . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors de la recherche : " . $e->getMessage());
}

require __DIR__ . '/../vue/pages/search_profiles.php';
