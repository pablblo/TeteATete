<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation des cours</title>
    <link rel="stylesheet" href="style/style_evaluation.css">
</head>
<body>
    <?php include 'vue/partials/navbar.php'; ?>

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

<footer class="bg-light text-center py-3 mt-5 fixed-bottom">
    <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="cgu.php" class="text-decoration-none mx-3 text-dark">
        Conditions générales d'utilisation
    </a>
    |
    <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
        Mentions légales
    </a>
</footer>
</body>
</html>
