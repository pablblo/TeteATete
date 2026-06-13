<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .message {
            color: green; /* Couleur verte pour succès */
            font-weight: bold;
            margin-bottom: 10px;
        }
        .message.error {
            color: red; /* Couleur rouge pour erreur */
        }
    </style>
</head>
<body>
    <!-- Page principale -->
    <div class="page-container">
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>
        <div class="login-container">
            <div class="form-container">
                <!-- Afficher un message s'il existe -->
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'Erreur') !== false || strpos($message, 'Aucun compte') !== false ? 'error' : ''; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form action="" method="POST">
                    <input type="email" placeholder="Votre e-mail" id="email" name="email" required>
                    <button type="submit">Envoyer le lien de réinitialisation</button>
                    <div class="links">
                        <a href="login.php">Retour à la connexion</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="container-fluid" style="height: 125px"></div>
    </div>
</body>
</html>