<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=bdd_tat;charset=utf8", "root", "");

// Récupérer tous les cours
$sql = "SELECT * FROM Cours";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'utilisateur connecté (remplacez par votre système de session)
$idUser = $_SESSION['user_id']; // ID de l'utilisateur connecté

foreach ($cours as $coursItem) {
    $idCours = $coursItem['idCours'];
    
    // Vérifier si l'utilisateur est déjà inscrit
    $sqlInscription = "SELECT * FROM User_Cours WHERE idUser = :idUser AND idCours = :idCours";
    $stmtInscription = $conn->prepare($sqlInscription);
    $stmtInscription->execute(['idUser' => $idUser, 'idCours' => $idCours]);
    $isInscrit = $stmtInscription->fetch();

    // Récupérer les utilisateurs inscrits au cours
    $sqlParticipants = "SELECT u.Prenom, u.Photo_de_Profil FROM User_Cours uc
                        JOIN User u ON uc.idUser = u.idUser
                        WHERE uc.idCours = :idCours";
    $stmtParticipants = $conn->prepare($sqlParticipants);
    $stmtParticipants->execute(['idCours' => $idCours]);
    $participants = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='course-box'>
            <h3>{$coursItem['Titre']}</h3>
            <p>Date : {$coursItem['Date']} | Heure : {$coursItem['Heure']}</p>
            <p>Participants inscrits :</p>
            <div class='participants'>";
    
    foreach ($participants as $participant) {
        $photo = base64_encode($participant['Photo_de_Profil']);
        echo "<img src='data:image/jpeg;base64,{$photo}' alt='Photo de profil' class='profile-pic'>";
    }
    
    echo "</div>";
    
    if ($isInscrit) {
        echo "<p class='status-inscrit'>Vous êtes inscrit</p>";
    } else {
        echo "<button class='btn-inscrire' data-cours='{$idCours}' data-role='eleve'>S'inscrire en tant qu'élève</button>
              <button class='btn-inscrire' data-cours='{$idCours}' data-role='tuteur'>S'inscrire en tant que tuteur</button>";
    }
    
    echo "</div>";
}
?>
