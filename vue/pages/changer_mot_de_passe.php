<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Changer le mot de passe</title>
</head>
<body>
    <div class="page-container">
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>
        <div class="form-container">
            <h2>Changer le mot de passe</h2>

            <!-- Afficher le message si présent -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'succès') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de changement de mot de passe -->
            <form action="changer_mot_de_passe.php" method="POST">
                <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <button type="submit">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</body>
</html>