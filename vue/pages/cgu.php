<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Principale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style_mentionslegales.css">
</head>
<body>
    <?php include 'vue/partials/navbar.php'; ?>
    
<!-- Section iframe pour afficher le PDF -->
<div class="profile-container">
    <iframe src="documents/CGU.pdf" width="100%" height="600px"></iframe>
</div>

<!-- Footer -->
<footer class="bg-light text-center py-3 mt-5 fixed-bottom">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="cgu.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
    </footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
