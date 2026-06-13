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
    <?php include 'vue/partials/navbar.php'; ?>
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
        <a href="cgu.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
</footer>
</html>
