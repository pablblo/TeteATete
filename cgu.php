<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Inclusion de la barre de navigation
include 'navbar.php';

// Démarrer la session pour l'utilisateur
session_start();
// 

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
    header("Location: cgu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link rel="stylesheet" href="style/styleFAQ.css">
</head>
<body>

<div class="profile-container"  style="border: 2px solid #0061A0;">
    <section>
        <div>
            <h1> Condition générales d'utilisation</h1>
        </div>
    </section>
</div>
<div class="profile-container"  style="border: 2px solid #0061A0;">
    <section>
        <div>
            <iframe src="documents/CGU.pdf" width="100%" height="600px"></iframe>
        </div>
    </section>
</div>

</body>
</html>
