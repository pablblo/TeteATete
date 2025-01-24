<?php
// Activer les erreurs pour débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connection.php';

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $idUser = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Vérifier si les données POST sont présentes
        if (!empty($_POST['idCours']) && !empty($_POST['message'])) {
            $idCours = (int) $_POST['idCours'];
            $message = trim($_POST['message']);

            // Valider si idCours existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM cours WHERE idCours = :idCours");
            $stmt->bindParam(':idCours', $idCours, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() == 0) {
                die("Erreur : idCours invalide.");
            }

            // Insérer le message dans la base de données
            $stmt = $db->prepare("INSERT INTO message (idCours, idUser, message, timestamp) VALUES (:idCours, :idUser, :message, NOW())");
            $stmt->bindParam(':idCours', $idCours, PDO::PARAM_INT);
            $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->execute();

            // Rediriger vers la page des messages après l'insertion
            generateUrlFromFilename("Location: http://localhost/TeteATete/messages0.php?idCours=$idCours");
            exit();
        } else {
            die("Paramètres manquants.");
        }
    }
} catch (PDOException $e) {
    die("Erreur lors de l'insertion du message : " . $e->getMessage());
}
