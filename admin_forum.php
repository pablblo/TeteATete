<?php
// Vérifiez si l'utilisateur est un administrateur (remplacez cette logique par votre propre système de rôle)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Questions sans réponses</h2>
        <?php if (!empty($unanswered_posts)): ?>
            <?php foreach ($unanswered_posts as $post): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            Question de <?php echo htmlspecialchars($post['Prenom'] . ' ' . $post['Nom']); ?> 
                            <span class="text-muted" style="font-size: 0.9rem;">(<?php echo $post['created_at']; ?>)</span>
                        </h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($post['question'])); ?></p>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <textarea name="answer" class="form-control" rows="3" placeholder="Répondez ici..." required></textarea>
                            </div>
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn btn-success">Répondre</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Toutes les questions ont été répondues !</p>
        <?php endif; ?>
    </div>
</body>
</html>
