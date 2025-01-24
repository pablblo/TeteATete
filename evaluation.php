<?php
require 'db_connection.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la connexion si non connecté
    exit();
}

$idUser = $_SESSION['user_id']; // ID de l'utilisateur connecté

try {
    // Récupérer les cours terminés éligibles pour une évaluation
    $query = "
        SELECT c.idCours, c.Titre, c.Date, c.Heure
        FROM inscription i
        INNER JOIN cours c ON i.idCours = c.idCours
        WHERE i.idUser = :idUser
          AND TIMESTAMP(c.Date, c.Heure) <= NOW() - INTERVAL 5 HOUR
          AND c.idCours NOT IN (
              SELECT idCours
              FROM evaluation
              WHERE idUserAuteur = :idUser
          )
    ";
    $stmt = $db->prepare($query);
    $stmt->execute(['idUser' => $idUser]);

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des cours : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation des cours</title>
    <link rel="stylesheet" href="style/style_evaluation.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>Évaluez vos cours terminés</h1>
    <div class="course-container">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course">
                    <h2><?php echo htmlspecialchars($course['Titre']); ?></h2>
                    <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?></p>
                    <p><strong>Heure :</strong> <?php echo htmlspecialchars($course['Heure']); ?></p>
                    <button onclick="window.location.href='submit_evaluation.php?idCours=<?php echo $course['idCours']; ?>'">
                        Évaluer ce cours
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun cours éligible à une évaluation pour le moment.</p>
        <?php endif; ?>
    </div>

<footer class="bg-light text-center py-3 mt-5">
    <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
    <a href="CGU.php" class="text-decoration-none mx-3 text-dark">
        Conditions générales d'utilisation
    </a>
    |
    <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
        Mentions légales
    </a>
</footer>
</body>
</html>
