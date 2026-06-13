# Tete-a-tete

Plateforme d'entraide entre élèves — tutorat, cours, chat et forum.

## Architecture MVC

```
PlatTutEntreEleves/
├── bootstrap.php              # Point d'entrée commun (session, PDO, helpers)
├── config/database.php        # Configuration base de données
├── db_connection.php            # Compatibilité — redirige vers bootstrap.php
│
├── controleur/                  # Logique métier (contrôleurs)
│   ├── functions.php            # Fonctions utilitaires partagées
│   ├── login.php, register.php, page_principale.php, profil.php, ...
│   └── messages.php             # API JSON messages
│
├── modele/                      # Accès aux données
│   ├── connexion.php            # Alias vers bootstrap.php
│   └── requetes.utilisateurs.php
│
├── vue/
│   ├── pages/                   # Templates HTML des pages
│   └── partials/                # header, footer, navbar
│
├── actions/                     # Endpoints AJAX/API
│   ├── get_courses.php, send_message.php, delete_course.php, ...
│   └── supprimer_cours.php      # Script maintenance cours expirés
│
├── login.php, admin.php, ...    # Points d'entrée (délèguent au contrôleur)
├── style/, images/, src/, documents/
└── vendor/                      # Dépendances Composer (PHPMailer)
```

### Flux d'une requête

1. Le navigateur appelle un fichier racine (ex. `page_principale.php`)
2. Le fichier racine inclut le contrôleur correspondant dans `controleur/`
3. Le contrôleur charge `bootstrap.php`, exécute la logique, puis inclut la vue dans `vue/pages/`
4. Les appels AJAX passent par le dossier `actions/`

## Technologies

- PHP, MySQL (PDO) — frontend MVC
- **Java 21, Spring Boot 3.4** — REST API (`backend/`)
- HTML, CSS, JavaScript
- Bootstrap 5
- PHPMailer (Composer)

## Spring Boot backend

The Java API lives in `backend/` and shares the same MySQL database.

```bash
cd backend
mvn spring-boot:run
```

Enable PHP → Spring integration in `config/api.php`:

```php
'enabled' => true,
'base_url' => 'http://localhost:8080',
```

See `backend/README.md` for endpoints and JWT authentication.

## Configuration

Modifier `config/database.php` avec vos identifiants MySQL, puis importer `BDD_TAT.sql`.

## Pages principales

| URL | Rôle |
|-----|------|
| `login.php` | Connexion |
| `register.php` | Inscription |
| `page_principale.php` | Liste et gestion des cours |
| `profil.php` | Profil utilisateur |
| `admin.php` | Administration |
| `chat.php` / `messages0.php` | Messagerie par cours |
| `FAQ.php` | Forum questions/réponses |
