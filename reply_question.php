<?php
// Inclusion de la connexion à la base de données
require 'db_connection.php';
session_start();

// Vérification de connexion administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Répondre à une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idQuestion'], $_POST['reply'])) {
    $question_id = $_POST['idQuestion'];
    $reply = trim($_POST['reply']);

    if (empty($reply)) {
        die("La réponse ne peut pas être vide.");
    }

    try {
        // Ajouter la réponse à la question
        $stmt = $db->prepare("UPDATE Forum SET Reponse = ? WHERE idQuestion = ?");
        $stmt->execute([$reply, $question_id]);

        // Redirection après réponse
        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        die("Erreur lors de la réponse à la question : " . $e->getMessage());
    }
} else {
    header("Location: admin.php");
    exit();
}
?>
