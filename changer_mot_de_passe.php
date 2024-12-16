<?php
session_start();
require 'config.php'; // Connexion à la base de données

$message = ""; // Variable pour stocker le message d'état

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT); // Hachage du nouveau mot de passe

    // Vérifiez si le token existe
    $stmt = $pdo->prepare("SELECT * FROM User WHERE reset_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mettre à jour le mot de passe et réinitialiser le token
        $stmt = $pdo->prepare("UPDATE User SET Mot_de_passe = :new_password, reset_token = NULL WHERE reset_token = :token");
        $stmt->execute(['new_password' => $new_password, 'token' => $token]);
        $message = "Votre mot de passe a été réinitialisé avec succès. <a href='login.html'>Retour à la connexion</a>";
    } else {
        $message = "Token invalide. Veuillez réessayer ou demander un nouveau lien de réinitialisation.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
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
