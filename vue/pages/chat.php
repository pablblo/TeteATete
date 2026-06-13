<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Chat</title>
    <link rel="stylesheet" href="style/style_chat.css">
</head>
<body>
    <?php include 'vue/partials/navbar.php'; ?>
    <main>
        <h1>Vos Discussion</h1>
        <p class="description">Voici les groupes auxquels vous participez.<br> Cliquez sur un groupe pour accéder aux discussions et interagir avec vos collègues.</p>
        <br>
        <div class="container" id="course-list"></div>
    </main>
    <script>
        const userId = <?php echo $userId; ?>;
        const coursesUrl = <?php echo json_encode($coursesUrl); ?>;
        const apiToken = <?php echo json_encode($apiToken); ?>;
        const useSpringApi = <?php echo $useSpringApi ? 'true' : 'false'; ?>;

        const fetchOptions = useSpringApi && apiToken
            ? { headers: { 'Authorization': 'Bearer ' + apiToken } }
            : {};

        fetch(coursesUrl, fetchOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur HTTP : ' + response.status);
                }
                return response.json();
            })
            .then(courses => {
                const courseList = document.getElementById('course-list');

                // Afficher chaque cours dans une carte
                courses.forEach(course => {
                    const listItem = document.createElement('div');
                    listItem.classList.add('course-card');

                    listItem.innerHTML = `
                        <div class="course-title">${course.Titre}</div>
                        <div class="course-info">
                            <p><strong>Statut :</strong> ${course.role || 'Élève'}</p>
                        </div>
                        <button class="button-36" onclick="window.location.href='messages0.php?idCours=${course.idCours}'">
                            Ouvrir
                        </button>
                    `;
                    courseList.appendChild(listItem);
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des cours :', error);
                const courseList = document.getElementById('course-list');
                courseList.innerHTML = `<p style="color: red;">Erreur lors du chargement des cours.</p>`;
            });
    </script>
    
     <!-- Footer -->
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
</body>
</html>
