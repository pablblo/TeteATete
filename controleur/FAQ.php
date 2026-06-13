<?php

require_once __DIR__ . '/../bootstrap.php';

// Inclusion du fichier de connexion à la base de données
// Démarrer la session pour l'utilisateur


// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est un administrateur
$isAdmin = $user['Admin'] == 1;

// Gestion des réponses par les administrateurs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'], $_POST['question_id']) && $isAdmin) {
    $answer = trim($_POST['answer']);
    $question_id = (int)$_POST['question_id'];

    if (!empty($answer)) {
        $stmt = $db->prepare("UPDATE Forum SET answer = ? WHERE id = ?");
        $stmt->execute([$answer, $question_id]);
        header("Location: " . $_SERVER['PHP_SELF']); // Rafraîchir la page
        exit();
    }
}

// Gestion des questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = trim($_POST['question']);
    if (!empty($question)) {
        $stmt = $db->prepare("INSERT INTO Forum (user_id, question) VALUES (?, ?)");
        $stmt->execute([$user_id, $question]);
        header("Location: " . $_SERVER['PHP_SELF']); // Rafraîchir la page
        exit();
    }
}

// Récupération des questions et réponses
$forum_stmt = $db->query("
    SELECT f.*, u.Prenom, u.Nom 
    FROM Forum f 
    JOIN User u ON f.user_id = u.idUser 
    ORDER BY f.created_at DESC
");
$forum_posts = $forum_stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../vue/pages/FAQ.php';
