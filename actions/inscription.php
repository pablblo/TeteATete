<?php
// Connexion à la base de données
require 'db_connection.php';

// Récupérer les données POST
$idCours = $_POST['idCours'];
$role = $_POST['role'];
$idUser = $_SESSION['idUser'];

// Vérifier si l'utilisateur est déjà inscrit
$sqlCheck = "SELECT * FROM User_Cours WHERE idUser = :idUser AND idCours = :idCours";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute(['idUser' => $idUser, 'idCours' => $idCours]);

if ($stmtCheck->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Vous êtes déjà inscrit à ce cours.']);
    exit;
}

// Ajouter l'inscription
$sqlInsert = "INSERT INTO User_Cours (Tuteur_ou_Eleve, idUser, idCours) VALUES (:role, :idUser, :idCours)";
$stmtInsert = $conn->prepare($sqlInsert);
$success = $stmtInsert->execute([
    'role' => ($role === 'eleve' ? 0 : 1), // 0 pour élève, 1 pour tuteur
    'idUser' => $idUser,
    'idCours' => $idCours,
]);

echo json_encode(['success' => $success]);
?>
