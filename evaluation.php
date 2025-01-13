<?php
require 'db_connection.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la connexion si non connecté
    exit();
}

$idUser = $_SESSION['user_id']; // ID de l'utilisateur connecté

try {
    // Récupérer les cours terminés éligibles pour une évaluation
    $query = "
        SELECT c.idCours, c.Titre, c.Date, c.Heure
        FROM inscription i
        INNER JOIN cours c ON i.idCours = c.idCours
        WHERE i.idUser = :idUser
          AND TIMESTAMP(c.Date, c.Heure) <= NOW() - INTERVAL 5 HOUR
          AND c.idCours NOT IN (
              SELECT idCours
              FROM evaluation
              WHERE idUserAuteur = :idUser
          )
    ";
    $stmt = $db->prepare($query);
    $stmt->execute(['idUser' => $idUser]);

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des cours : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation des cours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .course-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .course {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .course h2 {
            margin: 0 0 10px;
            color: #455EB5;
        }
        .course button {
            background-image: linear-gradient(92.88deg, #455EB5 9.16%, #5643CC 43.89%, #673FD7 64.72%);
            border-radius: 8px;
            border: none;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .course button:hover {
            background-image: linear-gradient(92.88deg, #5643CC 9.16%, #455EB5 43.89%, #673FD7 64.72%);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>Évaluez vos cours terminés</h1>
    <div class="course-container">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course">
                    <h2><?php echo htmlspecialchars($course['Titre']); ?></h2>
                    <p><strong>Date :</strong> <?php echo htmlspecialchars($course['Date']); ?></p>
                    <p><strong>Heure :</strong> <?php echo htmlspecialchars($course['Heure']); ?></p>
                    <button onclick="window.location.href='submit_evaluation.php?idCours=<?php echo $course['idCours']; ?>'">
                        Évaluer ce cours
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun cours éligible à une évaluation pour le moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
