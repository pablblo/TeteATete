<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation - <?php echo htmlspecialchars($titreCours); ?></title>
    <link rel="stylesheet" href="style/style_submit_evaluation.css">
</head>
<body>
    <?php include 'vue/partials/navbar.php'; ?>
    <div style="position: absolute; top: 150px; left: 50px;">
        <button onclick="window.location.href='evaluation.php'" class="button-36"> Retour</button>
    </div>
    <div class="form-container">
        <h1>Évaluation - <?php echo htmlspecialchars($titreCours); ?></h1>

        <?php if (empty($usersToEvaluate)): ?>
            <p>Aucun utilisateur à évaluer pour ce cours.</p>
        <?php else: ?>
            <form method="POST">
                <?php foreach ($usersToEvaluate as $user): ?>
                    <div>
                        <h2><?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?></h2>
                        <label for="note_<?php echo $user['idUser']; ?>">Note (1 à 5)</label>
                        <select id="note_<?php echo $user['idUser']; ?>" name="evaluations[<?php echo $user['idUser']; ?>][note]" required>
                            <option value="">Choisissez une note</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>

                        <label for="commentaire_<?php echo $user['idUser']; ?>">Commentaire</label>
                        <textarea id="commentaire_<?php echo $user['idUser']; ?>" name="evaluations[<?php echo $user['idUser']; ?>][commentaire]" rows="4" required></textarea>

                        <input type="hidden" name="evaluations[<?php echo $user['idUser']; ?>][idUserReceveur]" value="<?php echo $user['idUser']; ?>">
                    </div>
                <?php endforeach; ?>

                <button class="button-36" type="submit">Envoyer les évaluations</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
