<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration de la base de données
$host = '127.0.0.1'; // Remplacez par votre adresse hôte si nécessaire
$dbname = 'bdd_tat'; // Nom de votre base de données
$username = 'root'; // Nom d'utilisateur de la BDD
$password = ''; // Mot de passe de la BDD

// Configurer l'en-tête pour retourner du JSON
header('Content-Type: application/json');

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si idCours est présent dans la requête GET
    if (!isset($_GET['idCours'])) {
        echo json_encode(['error' => 'idCours est manquant.']);
        exit;
    }

    $idCours = (int) $_GET['idCours']; // Récupérer et sécuriser l'entrée

    // Requête SQL pour récupérer les messages du cours
    $sql = "SELECT 
                m.idMessage,
                m.message,
                m.timestamp,
                u.Nom,
                u.Prenom,
                u.Photo_de_Profil,
                i.role
            FROM 
                message m
            INNER JOIN 
                user u ON m.idUser = u.idUser
            INNER JOIN 
                inscription i ON m.idCours = i.idCours AND m.idUser = i.idUser
            WHERE 
                m.idCours = :idCours
            ORDER BY 
                m.timestamp ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idCours', $idCours, PDO::PARAM_INT);
    $stmt->execute();

    // Récupérer les résultats
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir le BLOB (Photo_de_Profil) en Base64 pour l'affichage dans le frontend
    foreach ($messages as &$message) {
        if (!empty($message['Photo_de_Profil'])) {
            $message['Photo_de_Profil'] = base64_encode($message['Photo_de_Profil']);
        }
    }

    // Retourner les données en JSON
    echo json_encode($messages);

} catch (PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
