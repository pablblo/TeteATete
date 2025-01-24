<?php
require 'db_connection.php'; // Connexion à la base de données


// Vérification de connexion administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['Admin'] != 1) {
    generateUrlFromFilename("Location: login.php");
    exit();
}

// Requête pour récupérer les cours avec les inscriptions
$query = "
    SELECT 
        c.idCours,
        c.Titre,
        c.Date,
        c.Heure,
        COUNT(CASE WHEN i.role = 'eleve' THEN 1 END) AS nbEleves,
        COUNT(CASE WHEN i.role = 'instructeur' THEN 1 END) AS nbInstructeurs
    FROM 
        Cours c
    LEFT JOIN 
        inscription i ON c.idCours = i.idCours
    GROUP BY 
        c.idCours
";
$courses = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);


$courseParticipants = [];
foreach ($courses as $course) {
    $stmt = $db->prepare("
        SELECT i.idInscription, u.Nom, u.Prenom, u.Mail, i.role 
        FROM inscription i
        JOIN User u ON i.idUser = u.idUser
        WHERE i.idCours = :idCours
    ");
    $stmt->execute(['idCours' => $course['idCours']]);
    $courseParticipants[$course['idCours']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des utilisateurs
$users = $db->query("SELECT * FROM User")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des questions du forum
$questions = $db->query("SELECT * FROM Forum")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="mb-4 text-center">Tableau de Bord Administrateur</h1>

        <!-- Section Gestion des Cours -->
        <section class="mb-5">
    <h2>Gestion des cours</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Élèves inscrits</th>
                <th>Tuteurs inscrits</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr id="course-row-<?php echo $course['idCours']; ?>">
                    <td><?php echo $course['idCours']; ?></td>
                    <td id="title-<?php echo $course['idCours']; ?>"><?php echo htmlspecialchars($course['Titre']); ?></td>
                    <td id="date-<?php echo $course['idCours']; ?>"><?php echo $course['Date']; ?></td>
                    <td id="time-<?php echo $course['idCours']; ?>"><?php echo $course['Heure']; ?></td>
                    <td><?php echo $course['nbEleves']; ?></td>
                    <td><?php echo $course['nbInstructeurs']; ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" onclick="openParticipantsModal(<?php echo $course['idCours']; ?>)">Participants</button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $course['idCours']; ?>)">Modifier</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteCourse(<?php echo $course['idCours']; ?>)">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Modifier le cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <input type="hidden" id="edit-course-id">
                    <div class="mb-3">
                        <label for="edit-course-title" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="edit-course-title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-course-date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit-course-date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-course-time" class="form-label">Heure</label>
                        <input type="time" class="form-control" id="edit-course-time" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveCourseChanges()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="participantsModal" tabindex="-1" aria-labelledby="participantsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participantsModalLabel">Participants du cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="participantsList" class="list-group">
                    <!-- Liste des participants -->
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    const participantsData = <?php echo json_encode($courseParticipants); ?>;

    function openParticipantsModal(courseId) {
        const participantsList = document.getElementById('participantsList');
        participantsList.innerHTML = '';

        if (participantsData[courseId]) {
            participantsData[courseId].forEach(participant => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
                    ${participant.Prenom} ${participant.Nom} (${participant.role}) - ${participant.Mail}
                    <button class="btn btn-danger btn-sm" onclick="removeParticipant(${participant.idInscription}, ${courseId})">Retirer</button>
                `;
                participantsList.appendChild(listItem);
            });
        } else {
            participantsList.innerHTML = '<li class="list-group-item">Aucun participant</li>';
        }

        const participantsModal = new bootstrap.Modal(document.getElementById('participantsModal'));
        participantsModal.show();
    }

    function removeParticipant(idInscription, courseId) {
    if (confirm('Êtes-vous sûr de vouloir retirer ce participant ?')) {
        fetch('remove_participant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ idInscription: idInscription })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Redirection vers la page admin après suppression réussie
                window.location.href = 'admin.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue.');
        });
    }
}

</script>

<script>
    function openEditModal(courseId) {
        // Récupérer les informations du cours à partir des éléments HTML existants
        const title = document.getElementById(`title-${courseId}`).textContent;
        const date = document.getElementById(`date-${courseId}`).textContent;
        const time = document.getElementById(`time-${courseId}`).textContent;

        // Remplir les champs de la modale
        document.getElementById('edit-course-id').value = courseId;
        document.getElementById('edit-course-title').value = title.trim();
        document.getElementById('edit-course-date').value = date.trim();
        document.getElementById('edit-course-time').value = time.trim();

        // Afficher la modale
        const editCourseModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
        editCourseModal.show();
    }

    function saveCourseChanges() {
        const courseId = document.getElementById('edit-course-id').value;
        const title = document.getElementById('edit-course-title').value;
        const date = document.getElementById('edit-course-date').value;
        const time = document.getElementById('edit-course-time').value;

        // Envoyer les modifications via AJAX
        fetch('update_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                idCours: courseId,
                Titre: title,
                Date: date,
                Heure: time
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour les informations dans le tableau
                document.getElementById(`title-${courseId}`).textContent = title;
                document.getElementById(`date-${courseId}`).textContent = date;
                document.getElementById(`time-${courseId}`).textContent = time;

                // Fermer la modale
                const editCourseModal = bootstrap.Modal.getInstance(document.getElementById('editCourseModal'));
                editCourseModal.hide();

                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert("Une erreur est survenue lors de la mise à jour du cours.");
        });
    }
</script>


<script>
    function deleteCourse(courseId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce cours ?")) {
            fetch('delete_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ idCours: courseId })
            })
            .then(response => response.json()) // Parse la réponse JSON
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Supprimer la ligne de la table sans recharger
                    document.querySelector(`#course-row-${courseId}`).remove();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Une erreur est survenue lors de la suppression.");
            });
        }
    }
</script>




        <!-- Section Gestion des Utilisateurs -->
        <!-- Section Gestion des Utilisateurs -->
<!-- Section Gestion des Utilisateurs -->
<section class="mb-5">
    <h2>Gestion des utilisateurs</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Bio</th>
                <th>Photo</th>
                <th>Admin</th>
                <th>Avertissements</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['idUser']; ?></td>
                    <td><?php echo htmlspecialchars($user['Nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['Mail'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['Bio'] ?? ''); ?></td>
                    <td>
                        <?php if ($user['Photo_de_Profil']): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['Photo_de_Profil']); ?>" alt="Photo de Profil" style="width: 50px; height: 50px; border-radius: 50%;">
                        <?php else: ?>
                            <span>Aucune photo</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user['Admin'] == 1 ? 'Oui' : 'Non'; ?></td>
                    <td><?php echo $user['nbAvertissements'] ?? 0; ?></td>
                    <td>
    <!-- Bouton Supprimer -->
    <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['idUser']; ?>)">Supprimer</button>

    <!-- Bouton Avertissement -->
    <button type="button" class="btn btn-warning btn-sm" onclick="showWarningModal(<?php echo $user['idUser']; ?>)">Avertissement</button>
    <br>
    <br>
    <!-- Bouton Promouvoir / Dépromouvoir Admin -->
    <?php if ($user['Admin'] == 1): ?>
        <button class="btn btn-secondary btn-sm" onclick="updateAdminStatus(<?php echo $user['idUser']; ?>, 0)">Dépromouvoir Admin</button>
    <?php else: ?>
        <button class="btn btn-success btn-sm" onclick="updateAdminStatus(<?php echo $user['idUser']; ?>, 1)">Promouvoir Admin</button>
    <?php endif; ?>
</td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>


        <!-- Section Modération du Forum -->
        <!-- Section Modération du Forum -->
        <section class="mb-5">
    <h2>Modération du forum</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Réponse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question): ?>
                <tr id="forum-row-<?php echo $question['id']; ?>">
                    <td><?php echo $question['id']; ?></td>
                    <td><?php echo htmlspecialchars($question['question']); ?></td>
                    <td>
                        <?php if (!empty($question['answer'])): ?>
                            <span id="answer-<?php echo $question['id']; ?>"><?php echo htmlspecialchars($question['answer']); ?></span>
                        <?php else: ?>
                            <em id="answer-<?php echo $question['id']; ?>">Pas encore de réponse</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (empty($question['answer'])): ?>
                            <button class="btn btn-primary btn-sm" onclick="openReplyModal(<?php echo $question['id']; ?>)">Répondre</button>
                        <?php else: ?>
                            <button class="btn btn-warning btn-sm" onclick="openEditAnswerModal(<?php echo $question['id']; ?>)">Modifier</button>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm" onclick="deleteQuestion(<?php echo $question['id']; ?>)">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Modale pour répondre -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Répondre à la question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="reply-content" class="form-control" rows="3" placeholder="Entrez votre réponse ici"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="reply-save-btn">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modale pour modifier une réponse -->
<div class="modal fade" id="editAnswerModal" tabindex="-1" aria-labelledby="editAnswerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnswerModalLabel">Modifier la réponse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="edit-answer-content" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="edit-answer-save-btn">Modifier</button>
            </div>
        </div>
    </div>
</div>
<script>
    function openReplyModal(questionId) {
    const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
    document.getElementById('reply-save-btn').onclick = function () {
        saveReply(questionId);
    };
    replyModal.show();
}

function saveReply(questionId) {
    const content = document.getElementById('reply-content').value;

    fetch('save_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id: questionId, answer: content })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById(`answer-${questionId}`).textContent = content;
            const replyModal = bootstrap.Modal.getInstance(document.getElementById('replyModal'));
            replyModal.hide();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function openEditAnswerModal(questionId) {
    const currentAnswer = document.getElementById(`answer-${questionId}`).textContent;
    document.getElementById('edit-answer-content').value = currentAnswer;

    const editAnswerModal = new bootstrap.Modal(document.getElementById('editAnswerModal'));
    document.getElementById('edit-answer-save-btn').onclick = function () {
        saveEditedAnswer(questionId);
    };
    editAnswerModal.show();
}

function saveEditedAnswer(questionId) {
    const content = document.getElementById('edit-answer-content').value;

    fetch('edit_answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id: questionId, answer: content })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById(`answer-${questionId}`).textContent = content;
            const editAnswerModal = bootstrap.Modal.getInstance(document.getElementById('editAnswerModal'));
            editAnswerModal.hide();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function deleteQuestion(questionId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
        fetch('delete_question.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: questionId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                document.getElementById(`forum-row-${questionId}`).remove();
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}
</script>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showWarningModal(userId) {
        const modal = document.createElement('div');
        modal.classList.add('modal', 'fade');
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Envoyer un avertissement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="motif-${userId}" class="form-control" placeholder="Entrez le motif" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" onclick="sendWarning(${userId})">Envoyer</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    function sendWarning(userId) {
        const motif = document.getElementById(`motif-${userId}`).value;

        fetch('send_warning_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ idUser: userId, motif: motif })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => console.error('Erreur:', error));
    }

    function updateAdminStatus(userId, newStatus) {
        fetch('update_admin_status_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ idUser: userId, newStatus: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => console.error('Erreur:', error));
    }
</script>
<script>
    function deleteUser(userId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ idUser: userId })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload(); // Recharger la page après suppression
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
    }
</script>
<br>
<li class="nav-item d-flex justify-content-center">
    <a class="btn btn-primary" style="background-color: #E2EAF4; color: black;" href="index.php?cible=utilisateurs&function=login">Déconnexion</a>
</li>


</body>
</html>
