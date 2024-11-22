<?php
session_start();
require 'config.php'; // Connexion à la base de données

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
        echo "Votre mot de passe a été réinitialisé avec succès.";
    } else {
        echo "Token invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
</head>
<body>
    <form action="" method="POST">
        <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
        <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
        <button type="submit">Changer le mot de passe</button>
    </form>
</body>
</html>
