<?php
require 'db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password
    $classe = $_POST['classe'];

    // Check if the user already exists
    $stmt = $db->prepare("SELECT * FROM `User` WHERE `Mail` = ?");
    $stmt->execute([$email]);
    $userExists = $stmt->fetch();

    if ($userExists) {
        die("Cet utilisateur existe déjà.");
    }

    // Insert the new user into the database
    $stmt = $db->prepare("INSERT INTO `User` (Nom, Prenom, Mail, Mot_de_passe, Classe) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $email, $password, $classe]);

    echo "Inscription réussie !";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tête à Tête - Inscription</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="page-container"> <!-- Main container -->
        
        <!-- Logo, title, and tagline -->
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>

        <!-- Registration form -->
        <div class="login-container">
            <div class="form-container">
                <form id="registrationForm" action="register.php" method="POST"> <!-- Self-submitting form -->
                    <input type="text" placeholder="Nom" id="nom" name="nom" required>
                    <input type="text" placeholder="Prénom" id="prenom" name="prenom" required>
                    <input type="email" placeholder="Mail" id="email" name="email" required>
                    <input type="password" placeholder="Mot de passe" id="password" name="password" required>

                    <label for="classe">Classe :</label>
                    <select id="classe" name="classe" required>
                        <option value="" disabled selected>Choisissez votre classe</option>
                        <option value="I1">I1</option>
                        <option value="B1">B1</option>
                        <option value="P1">P1</option>
                        <option value="I2">I2</option>
                        <option value="B2">B2</option>
                        <option value="P2">P2</option>
                        <option value="A1">A1</option>
                        <option value="B3">B3</option>
                        <option value="A2">A2</option>
                        <option value="A3">A3</option>
                    </select>
                    
                    <button id="myBtn" type="submit">S'inscrire</button>
                    <div class="links">
                        <a href="login.php">Déjà inscrit ? Se connecter</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Spacer -->
        <div class="container-fluid" style="height: 125px"></div>

        <!-- Modal for CGU -->
        <div id="myCGUModal" class="cgu-modal">
            <div class="cgu-modal-content">
                <iframe src="documents/CGU.pdf" height="80%"></iframe>
                <div class="checkbox-container">
                    <input id="checkbox" type="checkbox" style="background-color: aliceblue;">
                    <label for="checkbox" style="padding: 10px;">J'ai lu et accepté les conditions generales d'utilisation</label>
                </div>
                <style>
                    .checkbox-container {
                        display: flex;
                        flex-direction: row;
                        justify-content: center;
                    }

                    #checkbox {
                        display: inline-block;
                        padding: 10px;
                        margin: 0;
                        width: auto;
                        height: auto;
                    }
                </style>
                <div>
                    <button id="myOtherBtn" type="submit">S'inscrire</button>
                </div>
            </div>
        </div>
        <style>
        .cgu-modal {
            display: none;
            position: fixed;
            justify-content: center;
            align-items: center;
            z-index: 2;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .cgu-modal-content {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: stretch;
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            height: 80%;
            width: 60%;
        }
        </style>


        <script>
        var cgumodal = document.getElementById("myCGUModal");
        var form  = document.getElementById("registrationForm");
        var check = document.getElementById("checkbox");
        var btn   = document.getElementById("myOtherBtn");
    
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            cgumodal.style.display = "flex";
        });
    
        btn.onclick = function(){
            if (check.checked){
                form.submit();
            }
        }
    
        window.onclick = function(event) {
            if (event.target == cgumodal) {
                cgumodal.style.display = "none";
            }
        }
        </script> 
    </div>
</body>
</html>
