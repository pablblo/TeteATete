<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos Discussions</title>
    <link rel="stylesheet" href="style/style_messages0.css">
</head>
<body>
    <?php include 'vue/navbar.php'; ?>
    <div style="position: absolute; top: 150px; left: 50px;">
        <button onclick="window.location.href='index.php?cible=generique&function=chat'" class="button-36"> Retour</button>
    </div>

    <h1>Vos Discussions</h1>

    <div id="messages"></div>

    <div id="send-message-form">
        <form method="POST" action="http://localhost/TeteATete/vue/send_message.php" style="display: flex; width: 100%;">
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
            window.location.href = "index.php?cible=generique&function=chat"; // Redirection vers la liste des cours
        }

        // Insérer l'idCours dans le champ caché du formulaire
        document.getElementById('course-id').value = courseId;

        // Charger les messages depuis l'API
        function loadMessages() {
            fetch(`http://localhost/TeteATete/messages.php?idCours=${courseId}`)
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
        fetch(`http://localhost/TeteATete/course_name.php?idCours=${courseId}`)
    .then(response => response.json())
    .then(data => {
        if (data && data.Titre) {
            document.querySelector('h1').textContent = data.Titre;
        }
    })
    .catch(error => console.error('Erreur lors de la récupération du nom du cours :', error));

    </script>