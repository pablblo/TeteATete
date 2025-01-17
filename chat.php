<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100%; /* Permet à la page de prendre 100% de la hauteur de la fenêtre */

        }
        

        h1 {
            color: #333;
            text-align: center;
            margin: 20px 0 10px;
            font-size: 2.5em;
        }

        .description {
            text-align: center;
            font-size: 1.1em;
            color: #004f80;
            margin-bottom: 20px;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .course-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .course-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .course-info {
            font-size: 0.9em;
            color: #555;
            text-align: center;
            margin-bottom: 15px;
        }

        .button-36 {
            background-image: linear-gradient(92.88deg, #455EB5 9.16%, #5643CC 43.89%, #673FD7 64.72%);
            border-radius: 8px;
            border-style: none;
            color: #FFFFFF;
            cursor: pointer;
            font-family: "Inter UI", sans-serif;
            font-size: 16px;
            font-weight: 500;
            height: 3rem;
            padding: 0 1.6rem;
            text-align: center;
            transition: all 0.5s;
            text-shadow: rgba(0, 0, 0, 0.25) 0 3px 8px;
        }

        .button-36:hover {
            box-shadow: rgba(80, 63, 205, 0.5) 0 1px 30px;
            transition-duration: 0.1s;
        }
        
 
    footer {
    background-color: #f8f9fa;
    text-align: center;
    padding: 1rem 0;
    margin-top: auto;
}
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>Vos Discussion</h1>
    <p class="description">Voici les groupes auxquels vous participez.<br> Cliquez sur un groupe pour accéder aux discussions et interagir avec vos collègues.</p>
    <br>
    <div class="container" id="course-list"></div>

    <script>
        // ID utilisateur (à remplacer dynamiquement selon votre application)
        const userId = 1; // Remplacez avec l'idUser connecté

        // Récupération des cours de l'utilisateur via l'API
        fetch(`http://localhost/TeteATete/get_courses.php?idUser=${userId}`)
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
    
</body>
</html>
