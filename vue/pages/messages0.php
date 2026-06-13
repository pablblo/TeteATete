<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos Discussions</title>
    <link rel="stylesheet" href="style/style_messages0.css">
</head>
<body>
    <?php include 'vue/partials/navbar.php'; ?>
    <div style="position: absolute; top: 150px; left: 50px;">
        <button onclick="window.location.href='chat.php'" class="button-36"> Retour</button>
    </div>

    <h1>Vos Discussions</h1>

    <div id="messages"></div>

    <div id="send-message-form">
        <form id="message-form" method="POST" action="<?php echo htmlspecialchars($sendMessageUrl); ?>" style="display: flex; width: 100%;">
            <textarea name="message" id="message-input" placeholder="Écrivez votre message..." rows="3" required></textarea>
            <input type="hidden" name="idCours" id="course-id">
            <button type="submit" class="button-36">Envoyer</button>
        </form>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const courseId = urlParams.get('idCours');
        const messagesUrlTemplate = <?php echo json_encode($messagesUrlTemplate); ?>;
        const courseTitleUrlTemplate = <?php echo json_encode($courseTitleUrlTemplate); ?>;
        const sendMessageUrl = <?php echo json_encode($sendMessageUrl); ?>;
        const apiToken = <?php echo json_encode($apiToken); ?>;
        const useSpringApi = <?php echo $useSpringApi ? 'true' : 'false'; ?>;

        const authHeaders = useSpringApi && apiToken
            ? { 'Authorization': 'Bearer ' + apiToken }
            : {};

        if (!courseId) {
            alert("Aucun cours sélectionné !");
            window.location.href = "chat.php";
        }

        document.getElementById('course-id').value = courseId;

        document.getElementById('message-form').addEventListener('submit', function (event) {
            if (!useSpringApi) {
                return;
            }

            event.preventDefault();
            const message = document.getElementById('message-input').value.trim();
            fetch(sendMessageUrl, {
                method: 'POST',
                headers: {
                    ...authHeaders,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idCours: Number(courseId), message })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP : ' + response.status);
                    }
                    document.getElementById('message-input').value = '';
                    loadMessages();
                })
                .catch(error => console.error('Erreur lors de l\'envoi du message :', error));
        });

        function loadMessages() {
            fetch(`${messagesUrlTemplate}${courseId}`, { headers: authHeaders })
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
        const titleUrl = useSpringApi
            ? `${courseTitleUrlTemplate}${courseId}/title`
            : `${courseTitleUrlTemplate}${courseId}`;
        fetch(titleUrl, { headers: authHeaders })
    .then(response => response.json())
    .then(data => {
        if (data && data.Titre) {
            document.querySelector('h1').textContent = data.Titre;
        }
    })
    .catch(error => console.error('Erreur lors de la récupération du nom du cours :', error));

    </script>
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
