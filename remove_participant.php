<?php
require 'db_connection.php';

// Vérification des paramètres POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idInscription'])) {
    $idInscription = $_POST['idInscription'];

    try {
        // Récupérer les informations d'inscription (role et idCours)
        $query = $db->prepare("
            SELECT i.idCours, i.role 
            FROM inscription i 
            WHERE i.idInscription = ?
        ");
        $query->execute([$idInscription]);
        $inscription = $query->fetch(PDO::FETCH_ASSOC);

        if (!$inscription) {
            throw new Exception("Inscription non trouvée.");
        }

        $courseId = $inscription['idCours'];
        $role = $inscription['role'];

        // Supprimer l'inscription
        $deleteStmt = $db->prepare("DELETE FROM inscription WHERE idInscription = ?");
        $deleteStmt->execute([$idInscription]);

        // Mettre à jour les places restantes
        if ($role === 'eleve') {
            $updateStmt = $db->prepare("
                UPDATE Cours 
                SET Places_restants_Eleve = Places_restants_Eleve + 1 
                WHERE idCours = ?
            ");
        } elseif ($role === 'instructeur') {
            $updateStmt = $db->prepare("
                UPDATE Cours 
                SET Places_restants_Tuteur = Places_restants_Tuteur + 1 
                WHERE idCours = ?
            ");
        } else {
            throw new Exception("Rôle inconnu.");
        }

        $updateStmt->execute([$courseId]);

        // Réponse JSON en cas de succès
        echo json_encode([
            'success' => true,
            'message' => "Participant retiré avec succès, les places ont été mises à jour."
        ]);
    } catch (Exception $e) {
        // Réponse JSON en cas d'erreur
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Requête invalide."
    ]);
}
