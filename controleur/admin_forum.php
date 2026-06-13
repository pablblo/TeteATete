<?php

require_once __DIR__ . '/../bootstrap.php';

requireAdmin();

// Traitement des réponses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'], $_POST['post_id'])) {
    $answer = trim($_POST['answer']);
    $post_id = (int)$_POST['post_id'];
    if (!empty($answer) && $post_id > 0) {
        $stmt = $db->prepare("UPDATE Forum SET answer = ? WHERE id = ?");
        $stmt->execute([$answer, $post_id]);
        header("Location: admin_forum.php"); // Rafraîchir la page
        exit();
    }
}

// Récupération des questions sans réponses
$forum_stmt = $db->query("
    SELECT f.*, u.Prenom, u.Nom 
    FROM Forum f 
    JOIN User u ON f.user_id = u.idUser 
    WHERE f.answer IS NULL
    ORDER BY f.created_at DESC
");
$unanswered_posts = $forum_stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../vue/pages/admin_forum.php';
