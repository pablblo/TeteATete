    <div class="page-container">
        
        <div class="header-container">
            <img src="images/logo.png" alt="Logo Tête à Tête" class="logo">
            <h1>Tête à Tête</h1>
            <p>L'application d'entraides</p>
        </div>

        <div class="form-container" id="login-container">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="email" placeholder="Mail" id="login-email" name="login-email" required>
                <input type="password" placeholder="Mot de passe" id="login-password" name="login-password" required>
                <div class="g-recaptcha" data-sitekey="6Lc9KrMqAAAAAPSGlsM294Va-fL6FUhavCjtPpPC"></div>
                <button type="submit" name="form" value="login">Se Connecter</button>
                <div class="links">
                    <a href="#" target-id="mdpo-container">Mot de passe oublié</a><br><br>
                    <a href="#" target-id="register-container">Inscription</a>
                </div>
            </form>
        </div>

        <div class="form-container" id="mdpo-container">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'Erreur') !== false || strpos($message, 'Aucun compte') !== false ? 'error' : ''; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="email" placeholder="Votre e-mail" id="mdpo-email" name="mdpo-email" required>
                <button type="submit" name="form" value="mdpo">Envoyer le lien de réinitialisation</button>
                <div class="links">
                    <a href="#" target-id="login-container">Retour à la connexion</a>
                </div>
            </form>
        </div>

        <div class="form-container" id="register-container">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form id="registrationForm" action="" method="POST">
                <input type="text" placeholder="Nom" id="register-nom" name="register-nom" required>
                <input type="text" placeholder="Prénom" id="register-prenom" name="register-prenom" required>
                <input type="email" placeholder="Mail" id="register-email" name="register-email" required>
                <input type="password" placeholder="Mot de passe" id="register-password" name="register-password" required>
                <label for="classe">Classe :</label>
                <select id="register-classe" name="register-classe" required>
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
                <div class="g-recaptcha" data-sitekey="6Lf8HLMqAAAAAGBlyucu9ccoRRYKzxlg6u6dqN3g"></div>
                <button id="myBtn" type="submit">S'inscrire</button>
                <div class="links">
                    <a href="#" target-id="login-container">Déjà inscrit ? Se connecter</a>
                </div>
            </form>
        </div>

        <div id="myCGUModal" class="cgu-modal">
            <div class="cgu-modal-content">
                <iframe src="documents/CGU.pdf" height="80%"></iframe>
                <div class="checkbox-container">
                    <input id="checkbox" type="checkbox" style="background-color: aliceblue;">
                    <label for="checkbox" style="padding: 10px;">J'ai lu et accepté les conditions generales d'utilisation</label>
                </div>
                <div>
                    <button id="myOtherBtn" type="submit" name="form" value="register">S'inscrire</button>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="height: 125px"></div>
    </div>
    <script type="module" src="src/login.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>