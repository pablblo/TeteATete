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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            background-color: #e0f7fa; /* Couleur de fond simple */
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="password"], input[type="email"] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            padding: 10px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .header-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 100px;
            height: auto;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .dimmed {
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>
        <div class="login-container">
            <h2>Changer le mot de passe</h2>
            <form action="changer_mot_de_passe.php" method="POST">
                <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <button type="submit">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</body>
</html>
