<?php
// Connexion à la base de données
require 'db_connection.php';

if (!isset($_GET['idCours'])) {
    echo json_encode(['error' => 'idCours est requis']);
    exit;
}

$idCours = $_GET['idCours'];
$query = $db->prepare("SELECT Titre FROM cours WHERE idCours = ?");
$query->execute([$idCours]);
$course = $query->fetch(PDO::FETCH_ASSOC);

if ($course) {
    echo json_encode($course);
} else {
    echo json_encode(['error' => 'Cours non trouvé']);
}
?>
