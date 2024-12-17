<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$idCours = $_GET['idCours'] ?? null; // Récupérer l'ID du cours via GET

// Vérifier si le cours est fini
$query = $db->prepare("SELECT fini FROM Cours WHERE idCours = ?");
$query->execute([$idCours]);
$cours = $query->fetch(PDO::FETCH_ASSOC);

if (!$cours || $cours['fini'] == 0) {
    echo "Le cours n'est pas encore fini, vous ne pouvez pas encore évaluer.";
    exit();
}

// Récupérer les participants et leur rôle (Tuteur ou Élève) dans le cours
$queryParticipants = $db->prepare("
    SELECT uc.idUser, uc.Tuteur_ou_Eleve, u.Nom, u.Prenom
    FROM User_Cours uc
    JOIN User u ON uc.idUser = u.idUser
    WHERE uc.idCours = ?
");
$queryParticipants->execute([$idCours]);
$participants = $queryParticipants->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = $_POST['notes'];
    $commentaires = $_POST['commentaires'];
    $roles = $_POST['roles'];  // Récupérer les rôles soumis

    foreach ($notes as $idUserReceveur => $note) {
        $commentaire = $commentaires[$idUserReceveur] ?? '';
        $tuteurOuEleve = $roles[$idUserReceveur];

        // Insérer chaque évaluation dans la table Evaluation
        $query = $db->prepare("
            INSERT INTO Evaluation (Tuteur_ou_Eleve, Note, Commentaire, idUserAuteur, idUserReceveur)
            VALUES (?, ?, ?, ?, ?)
        ");
        $query->execute([$tuteurOuEleve, $note, $commentaire, $user_id, $idUserReceveur]);
    }

    echo "Évaluations enregistrées avec succès !";
}

// Afficher le formulaire d'évaluation
?>

<h2>Évaluer les participants</h2>
<form action="evaluer.php?idCours=<?php echo $idCours; ?>" method="POST">
    <?php foreach ($participants as $participant): ?>
        <?php if ($participant['idUser'] != $user_id): // Empêcher l'auto-évaluation ?>
            <h3><?php echo htmlspecialchars($participant['Prenom']) . ' ' . htmlspecialchars($participant['Nom']); ?></h3>

            <label for="note_<?php echo $participant['idUser']; ?>">Note (0-5) :</label>
            <select name="notes[<?php echo $participant['idUser']; ?>]" id="note_<?php echo $participant['idUser']; ?>" required>
                <option value="1">1 étoile</option>
                <option value="2">2 étoiles</option>
                <option value="3">3 étoiles</option>
                <option value="4">4 étoiles</option>
                <option value="5">5 étoiles</option>
            </select><br>

            <label for="commentaire_<?php echo $participant['idUser']; ?>">Commentaire :</label>
            <textarea name="commentaires[<?php echo $participant['idUser']; ?>]" id="commentaire_<?php echo $participant['idUser']; ?>"></textarea><br>

            <!-- Rôle : le tuteur note les élèves et les élèves notent les tuteurs -->
            <input type="hidden" name="roles[<?php echo $participant['idUser']; ?>]" value="<?php echo $participant['Tuteur_ou_Eleve'] == 1 ? 0 : 1; ?>">
        <?php endif; ?>
    <?php endforeach; ?>

    <button type="submit">Soumettre les évaluations</button>
</form>
