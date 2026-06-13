<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($profile_user['Prenom'] . ' ' . $profile_user['Nom']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style_profilpublic.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'vue/partials/navbar.php'; ?>

          

    <!-- Contenu du profil -->
    <div class="container mt-5">
        <div class="row">
            <!-- Informations principales -->
            <div class="col-md-4 text-center">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($profile_user['Photo_de_Profil']); ?>" 
                     class="profile-img mb-3" 
                     alt="Photo de profil">
                <h2><?php echo htmlspecialchars($profile_user['Prenom'] . " " . $profile_user['Nom']); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($profile_user['Mail']); ?></p>
                <br>
                <div class="notes-container">
                    
    <h3 class="section-title">Notes</h3>
    <div class="note-details">
        <div class="note-item">
            <h4>En tant qu'élève :</h4>
            <p>
                <span class="note-value">
                    <?php echo $moyenneEleve > 0 ? number_format($moyenneEleve, 1) . '/5' : 'Pas encore noté'; ?>
                </span>
                <?php if ($moyenneEleve > 0): ?>
                    <span class="stars">
                        <?php 
                        // Afficher des étoiles en fonction de la note
                        $stars = round($moyenneEleve);
                        echo str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
                        ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="note-item">
            <h4>En tant que tuteur :</h4>
            <p>
                <span class="note-value">
                    <?php echo $moyenneTuteur > 0 ? number_format($moyenneTuteur, 1) . '/5' : 'Pas encore noté'; ?>
                </span>
                <?php if ($moyenneTuteur > 0): ?>
                    <span class="stars">
                        <?php 
                        $stars = round($moyenneTuteur);
                        echo str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
                        ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

                 <div class="mt-5">
        <h3>Commentaires</h3>
        <?php if (!empty($evaluations)): ?>
            <?php foreach ($evaluations as $evaluation): ?>
                <div class="evaluation-card d-flex">
                    <img                                                              class="profile-img-small" 
                    src="data:image/jpeg;base64,<?php echo base64_encode($evaluation['Photo_de_Profil']); ?>" alt="Photo de l'auteur">
                    <div>
                        <h5><?php echo htmlspecialchars($evaluation['Prenom'] . ' ' . $evaluation['Nom']); ?> 
                            <small class="text-muted">(<?php echo $evaluation['roleAuteur']; ?>)</small>
                        </h5>
                        <p><strong>Cours :</strong> <?php echo htmlspecialchars($evaluation['coursTitre']); ?></p>
                        <p><strong>Note :</strong> 
    <?php echo $evaluation['Note']; ?>/5
    <span class="stars">
        <?php
        // Afficher des étoiles en fonction de la note
        $stars = (int)$evaluation['Note'];
        echo str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
        ?>
    </span>
</p>
                        <p><strong>Commentaire :</strong> <?php echo htmlspecialchars($evaluation['Commentaire']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune évaluation reçue pour le moment.</p>
        <?php endif; ?>
    </div>
            </div>

            <!-- Bio et autres informations -->
            <div class="col-md-8">
                <div class="card mb-0">
                    <div class="card-body">
                        <h3 class="card-title">À propos</h3>
                        <p><?php echo nl2br(htmlspecialchars($profile_user['Bio'])); ?></p>
                    </div>
                </div>

                <!-- Section des cours -->
                <div class="container mt-5">
                    <h3 class="mb-4 text-center">Cours auxquels <?php echo htmlspecialchars($profile_user['Prenom']); ?> est inscrit</h3>
                    <div class="row">
                        <?php if (!empty($user_courses)): ?>
                            <?php foreach ($user_courses as $course): ?>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <!-- Titre et informations de base -->
                                            <h5 class="card-title"><?php echo htmlspecialchars($course['Titre']); ?></h5>
                                            <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?> à <?php echo htmlspecialchars($course['Heure']); ?></p>
                                            <p><strong>Rôle :</strong> <?php echo $course['role'] === 'instructeur' ? 'Tuteur' : 'Élève'; ?></p>

                                            <!-- Section des élèves inscrits -->
                                            <p><strong>Élèves inscrits :</strong> <?php echo htmlspecialchars($course['eleves_inscrits']); ?> / <?php echo htmlspecialchars($course['Taille']); ?></p>
                                            <div class="profile-container mb-3">
                                                <?php
                                                $eleve_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                                    FROM Inscription i
                                                                    JOIN User u ON i.idUser = u.idUser
                                                                    WHERE i.idCours = ? AND i.role = 'eleve'");
                                                $eleve_stmt->execute([$course['idCours']]);
                                                $eleves = $eleve_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($eleves as $eleve): ?>
                                                    <a href="profil_public.php?id=<?php echo $eleve['idUser']; ?>">
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($eleve['Photo_de_Profil']); ?>" 
                                                             class="profile-img-small" 
                                                             alt="Profil Élève"
                                                             title="Voir le profil">
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Section des tuteurs inscrits -->
                                            <p><strong>Tuteurs inscrits :</strong> <?php echo htmlspecialchars($course['tuteurs_inscrits']); ?> / 1</p>
                                            <div class="profile-container mb-3">
                                                <?php
                                                $tuteur_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                                     FROM Inscription i
                                                                     JOIN User u ON i.idUser = u.idUser
                                                                     WHERE i.idCours = ? AND i.role = 'instructeur'");
                                                $tuteur_stmt->execute([$course['idCours']]);
                                                $tuteurs = $tuteur_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($tuteurs as $tuteur): ?>
                                                    <a href="profil_public.php?id=<?php echo $tuteur['idUser']; ?>">
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                                             class="profile-img-small" 
                                                             alt="Profil Tuteur"
                                                             title="Voir le profil">
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Boutons d'inscription/désinscription pour l'utilisateur connecté -->
                                            <form method="POST" action="">
                                                <input type="hidden" name="course_id" value="<?php echo $course['idCours']; ?>">
                                                <?php
                                                // Vérifier si l'utilisateur connecté est inscrit à ce cours
                                                $check_stmt = $db->prepare("SELECT * FROM Inscription WHERE idCours = ? AND idUser = ?");
                                                $check_stmt->execute([$course['idCours'], $current_user_id]);
                                                $user_inscription = $check_stmt->fetch();

                                                if ($user_inscription) {
                                                    echo '<p class="text-success">Vous êtes inscrit en tant que ' . htmlspecialchars($user_inscription['role']) . '.</p>';
                                                    echo '<button type="submit" name="unregister_course" class="btn btn-danger me-2 mb-2">Se désinscrire</button>';
                                                } elseif ($course['eleves_inscrits'] >= $course['Taille'] && $course['tuteurs_inscrits'] >= 1) {
                                                    echo '<p class="text-danger">Le cours est complet.</p>';
                                                } else {
                                                    if ($course['tuteurs_inscrits'] == 0) {
                                                        echo '<button type="submit" name="register_course" value="instructeur" class="btn btn-secondary me-2 mb-2" onclick="this.form.role.value=\'instructeur\';">S\'inscrire en tant que tuteur</button>';
                                                    }
                                                    if ($course['eleves_inscrits'] < $course['Taille']) {
                                                        echo '<button type="submit" name="register_course" value="eleve" class="btn btn-primary me-2 mb-2" onclick="this.form.role.value=\'eleve\';">S\'inscrire en tant qu\'élève</button>';
                                                    }
                                                    echo '<input type="hidden" name="role" value="">';
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">Cet utilisateur n'est inscrit à aucun cours pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <h3 class="mb-4 text-center">Anciens Cours</h3>
<div class="row">
    <?php if (!empty($old_courses)): ?>
        <?php foreach ($old_courses as $course): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Titre et informations de base -->
                        <h5 class="card-title"><?php echo htmlspecialchars($course['Titre']); ?></h5>
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?> à <?php echo htmlspecialchars($course['Heure']); ?></p>
                        <p><strong>Rôle :</strong> <?php echo $course['role'] === 'instructeur' ? 'Tuteur' : 'Élève'; ?></p>

                        <!-- Section des élèves inscrits -->
                        <p><strong>Élèves inscrits :</strong> <?php echo htmlspecialchars($course['eleves_inscrits']); ?> / <?php echo htmlspecialchars($course['Taille']); ?></p>
                        <div class="profile-container mb-3">
                            <?php
                            $eleve_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                        FROM Inscription i
                                                        JOIN User u ON i.idUser = u.idUser
                                                        WHERE i.idCours = ? AND i.role = 'eleve'");
                            $eleve_stmt->execute([$course['idCours']]);
                            $eleves = $eleve_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($eleves as $eleve): ?>
                                <a href="profil_public.php?id=<?php echo $eleve['idUser']; ?>">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($eleve['Photo_de_Profil']); ?>" 
                                         class="profile-img-small" 
                                         alt="Profil Élève"
                                         title="Voir le profil">
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Section des tuteurs inscrits -->
                        <p><strong>Tuteurs inscrits :</strong> <?php echo htmlspecialchars($course['tuteurs_inscrits']); ?> / 1</p>
                        <div class="profile-container mb-3">
                            <?php
                            $tuteur_stmt = $db->prepare("SELECT u.Photo_de_Profil, u.idUser 
                                                         FROM Inscription i
                                                         JOIN User u ON i.idUser = u.idUser
                                                         WHERE i.idCours = ? AND i.role = 'instructeur'");
                            $tuteur_stmt->execute([$course['idCours']]);
                            $tuteurs = $tuteur_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tuteurs as $tuteur): ?>
                                <a href="profil_public.php?id=<?php echo $tuteur['idUser']; ?>">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($tuteur['Photo_de_Profil']); ?>" 
                                         class="profile-img-small" 
                                         alt="Profil Tuteur"
                                         title="Voir le profil">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">Aucun ancien cours trouvé.</p>
    <?php endif; ?>
</div>


            </div>
        </div>
    </div>


    <br>    
    <br>
    <br>

    <br>
    <br>
    <br>
    <br>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>