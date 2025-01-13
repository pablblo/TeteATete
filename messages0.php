<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos Discussions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin-top: 20px;
            font-size: 2.5em;
            text-align: center;
        }

        #messages {
            max-height: 400px; /* Réduit la hauteur */
            width: 90%;
            max-width: 900px; /* Réduit la largeur */
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
            overflow-y: auto; /* Ajout du scroll */
        }

        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
            padding: 5px 5px;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .message:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .message.user {
            align-self: flex-end;
            background-color: #d6e4ff;
            color: #333;
        }

        .message.other {
            align-self: flex-start;
            background-color: #f0f0f0;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .timestamp {
            color: #888;
            font-size: 12px;
        }

        #send-message-form {
            display: flex;
            gap: 50px; /* Espacement entre textarea et bouton */
            width: 90%;
            max-width: 900px; /* Aligné avec la largeur de #messages */
        }

        textarea {
    flex: 1; /* Permet au champ de texte de prendre toute la largeur disponible */
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-size: 1em;
    resize: none;
    transition: box-shadow 0.3s ease;
    height: 3rem; /* Réduit la hauteur du textarea */
    margin-right: 50px; /* Ajoute un espace supplémentaire entre le textarea et le bouton */

}


        textarea:focus {
            outline: none;
            border-color: #5643cc;
            box-shadow: 0 4px 8px rgba(80, 63, 205, 0.2);
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
            text-shadow: rgba(0, 0, 0, 0.25) 0 3px 8px;
            transition: all 0.5s;
        }

        .button-36:hover {
            box-shadow: rgba(80, 63, 205, 0.5) 0 1px 30px;
            transition-duration: 0.1s;
        }
        
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div style="position: absolute; top: 150px; left: 50px;">
        <button onclick="window.location.href='chat.php'" class="button-36"> Retour</button>
    </div>

    <h1>Vos Discussions</h1>

    <div id="messages"></div>

    <div id="send-message-form">
        <form method="POST" action="http://localhost/APP2/send_message.php" style="display: flex; width: 100%;">
            <textarea name="message" id="message-input" placeholder="Écrivez votre message..." rows="3" required></textarea>
            <input type="hidden" name="idCours" id="course-id">
            <button type="submit" class="button-36">Envoyer</button>
        </form>
    </div>

    <script>
        // Récupérer l'idCours depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const courseId = urlParams.get('idCours');

        // Vérifier si idCours est présent
        if (!courseId) {
            alert("Aucun cours sélectionné !");
            window.location.href = "chat.html"; // Redirection vers la liste des cours
        }

        // Insérer l'idCours dans le champ caché du formulaire
        document.getElementById('course-id').value = courseId;

        // Charger les messages depuis l'API
        function loadMessages() {
            fetch(`http://localhost/APP2/messages.php?idCours=${courseId}`)
                .then(response => response.json())
                .then(messages => {
                    const messagesContainer = document.getElementById('messages');
                    messagesContainer.innerHTML = ''; // Réinitialiser la liste des messages

                    messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message');
                        messageDiv.classList.add(msg.role === 'user' ? 'user' : 'other');

                        messageDiv.innerHTML = `
                            <div class="user-info">
                                <img src="data:image/jpeg;base64,${msg.Photo_de_Profil}" alt="Photo de Profil" class="profile-pic">
                                <div>
                                    <strong>${msg.Prenom} ${msg.Nom}</strong> (${msg.role || 'Élève'})
                                    <div class="timestamp">${msg.timestamp}</div>
                                </div>
                            </div>
                            <p>${msg.message}</p>
                        `;
                        messagesContainer.appendChild(messageDiv);
                    });

                    // Scroller vers le bas après avoir chargé les messages
                    document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des messages :', error);
                    document.getElementById('messages').innerHTML = `<p style="color: red;">Erreur lors du chargement des messages.</p>`;
                });
        }

        // Charger les messages initialement
        loadMessages();
        fetch(`http://localhost/APP2/course_name.php?idCours=${courseId}`)
    .then(response => response.json())
    .then(data => {
        if (data && data.Titre) {
            document.querySelector('h1').textContent = data.Titre;
        }
    })
    .catch(error => console.error('Erreur lors de la récupération du nom du cours :', error));

    </script>
    <footer class="bg-light text-center py-3 mt-5">
        <a class="text-decoration-none mx-3 text-dark">© 2024 Tete A Tete. Tous droits réservés.</a>
        <a href="CGU.php" class="text-decoration-none mx-3 text-dark">
            Conditions générales d'utilisation
        </a>
        |
        <a href="mentionslegales.php" class="text-decoration-none mx-3 text-dark">
            Mentions légales
        </a>
    </footer>
</body>
</html>
