<?php
// Inclusion du fichier de connexion à la base de données
require 'db_connection.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer la requête de recherche
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    header("Location: page_principale.php");
    exit();
}

try {
    // Rechercher les utilisateurs par prénom et nom
    $stmt = $db->prepare("
        SELECT idUser, Prenom, Nom, Photo_de_Profil 
        FROM User 
        WHERE CONCAT(Prenom, ' ', Nom) LIKE ?
    ");
    $stmt->execute(['%' . $query . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors de la recherche : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img-small {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h3 class="mb-4">Résultats pour "<?php echo htmlspecialchars($query); ?>"</h3>
        <div class="row">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card p-3">
                            <div class="d-flex align-items-center">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>" 
                                     class="profile-img-small me-3" 
                                     alt="Photo de profil">
                                <div>
                                    <h5 class="card-title mb-0">
                                        <a href="profil_public.php?id=<?php echo $user['idUser']; ?>">
                                            <?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?>
                                        </a>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
<footer class="bg-light text-center py-3 mt-5 fixed-bottom">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="CGU.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
</footer>
</html>
