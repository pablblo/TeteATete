<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Démarrer la session pour l'utilisateur
session_start();

// Vérifier si l'utilisateur est connecté (sinon redirection)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de login si l'utilisateur n'est pas connecté
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur depuis la base de données
$query = $db->prepare("SELECT * FROM User WHERE idUser = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Récupérer l'identifiant du message à modifier
$message_id = $_GET['id']; // Assurez-vous que l'ID est passé dans l'URL

// Récupérer le message à modifier
$message_query = $db->prepare("SELECT * FROM Message_Contact WHERE idMessage_Contact = ?");
$message_query->execute([$message_id]);
$Message_Contact = $message_query->fetch(PDO::FETCH_ASSOC);

// Mise à jour des informations si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = $_POST['mail'];
    $message = $_POST['message'];

    // Mettre à jour les informations dans la base de données
    $update_query = $db->prepare("
        UPDATE Message_Contact
        SET Mail = ?, message = ?
        WHERE idMessage_Contact = ?
    ");
    $update_query->execute([$mail, $message, $message_id]);

    // Recharger la page pour voir les nouvelles informations
    header("Location: contact.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link rel="stylesheet" href="style/styleprofil.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <a href="page_principale.php">
            <img src="images/logo.png" style="height: 100px; width: 100px;" alt="TAT Logo">
        </a>
    </div>
    <ul class="nav-links">
        <li><a href="#">Contact</a></li>
        <li><a href="FAQ.PHP">FAQ</a></li>
        <li><a href="#" class="post-btn">Poster</a></li>
        <li><a href="profil.php" class="user-profile">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>"
            style="object-fit: cover; height: 50px; width: 50px !important;border: 1px solid #ddd; border-radius: 50%;"
            alt="Photo de profil"></a>
        </li>
        <li><a href="login.html">Déconnexion</a></li>
    </ul>
</nav>

<div class="profile-container">
<div class="form-container" id="edit-form">
        <form action="contact.php?id=<?php echo $message_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="mail">Mail :</label>
            <input type="text" name="mail" value="<?php echo htmlspecialchars($Message_Contact['Mail']); ?>" required>

            <label for="message">Message :</label>
            <textarea name="message" required><?php echo htmlspecialchars($Message_Contact['message']); ?></textarea><br><br>

            <button type="submit">Envoyer</button>
        </form>
    </div>
</div>

</body>
</html>
