<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

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
?>





<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style_Faq.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Section Qui sommes-nous -->
    <section class="about-us">
        <div class="about-text">
            <h1>Qui sommes-nous ?</h1>
            <h2>InnoWave</h2>
            <p>
                InnoWave facilite l'accès aux cours en ligne et aux services de tutorat, aidant les étudiants à améliorer leurs performances académiques grâce à l'entraide pédagogique.
            </p>
        </div>
        <img src="images/APPlogo.png" alt="Logo InnoWave">
    </section>

    <!-- Section FAQ -->
    <section class="faq">
        <div class="container"> <!-- Ajout de la classe container -->
            <h2>FAQ</h2>
            <div class="faq-item">
                <h3>Politique d'annulation</h3>
                <h4>Que se passe-t-il si je dois annuler une séance ?</h4>
                <p>Vous pouvez annuler une séance au moins 24 heures à l'avance via votre espace personnel.</p>
            </div>
            <div class="faq-item">
                <h3>Assistance technique</h3>
                <h4>Qui contacter en cas de problème technique ?</h4>
                <p>Contactez le support technique à teteatete.innowave@gmail.com.</p>
            </div>
            <div class="faq-item">
                <h3>Modification du profil</h3>
                <h4>Comment changer mes informations personnelles ?</h4>
                <p>Accédez à la section "Profil", cliquez sur "Modifier" pour mettre à jour vos informations.</p>
            </div>
            <div class="faq-item">
                <h3>Réservation</h3>
                <h4>Comment réserver un cours ?</h4>
                <p>Allez dans la section "Cours", sélectionnez un cours et cliquez sur "Réserver".</p>
            </div>
            <div class="faq-item">
                <h3>Évaluation</h3>
                <h4>Comment évaluer un tuteur ou un élève ?</h4>
                <p>Après la séance, accédez à la page du cours pour laisser une évaluation.</p>
            </div>
        </div>
    </section>
    <section id="forum" class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Forum de discussion</h2>

            <!-- Formulaire pour poser une question -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="question" class="form-label">Posez votre question :</label>
                            <textarea id="question" name="question" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>

            <!-- Liste des questions et réponses -->
            
    </section>
    <div class="forum-questions">
        <?php if (!empty($forum_posts)): ?>
            <?php foreach ($forum_posts as $post): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            Question de <?php echo htmlspecialchars($post['Prenom'] . ' ' . $post['Nom']); ?> 
                            <span class="text-muted" style="font-size: 0.8rem;">(<?php echo $post['created_at']; ?>)</span>
                        </h5>
                        <p class="card-text">
                            <?php echo strlen($post['question']) > 150 ? substr(htmlspecialchars($post['question']), 0, 150) . '...' : nl2br(htmlspecialchars($post['question'])); ?>
                        </p>

                        <!-- Affichage de la réponse -->
                        <?php if (!empty($post['answer'])): ?>
                            <div class="mt-3 p-3 bg-light border rounded">
                                <strong>Réponse de l'administrateur :</strong>
                                <p><?php echo nl2br(htmlspecialchars($post['answer'])); ?></p>
                            </div>
                        <?php elseif ($isAdmin): ?>
                            <!-- Formulaire de réponse pour les administrateurs -->
                            <form method="POST" action="" class="mt-3">
                                <input type="hidden" name="question_id" value="<?php echo $post['id']; ?>">
                                <div class="mb-3">
                                    <textarea name="answer" class="form-control" rows="3" placeholder="Répondre à cette question..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Envoyer la réponse</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">En attente de réponse...</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">Aucune question pour le moment. Posez-en une ci-dessus !</p>
        <?php endif; ?>
    </div>


    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5 fixed-bottom">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="CGU.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
