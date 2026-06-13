# Tete a Tete — Spring Boot Backend

REST API Java 21 / Spring Boot 3.4 for the Tete a Tete tutoring platform. It connects to the same MySQL database as the PHP frontend (`bdd_tat`).

## Requirements

- Java 21+
- Maven 3.9+
- MySQL with schema imported from `../BDD_TAT.sql`

## Configuration

Edit `src/main/resources/application.yml`:

```yaml
spring:
  datasource:
    url: jdbc:mysql://localhost:3306/bdd_tat
    username: root
    password:

app:
  jwt:
    secret: change-me-to-a-long-random-secret-key-for-production
  cors:
    allowed-origins: http://localhost,http://127.0.0.1
```

## Run

```bash
cd backend
mvn spring-boot:run
```

API base URL: `http://localhost:8080`

Health check: `GET /api/health`

## Authentication

JWT bearer tokens are returned by:

- `POST /api/auth/login`
- `POST /api/auth/register`

Example:

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teteatete.innowave@gmail.com","password":"your-password"}'
```

Use the token in subsequent requests:

```bash
curl http://localhost:8080/api/courses \
  -H "Authorization: Bearer <token>"
```

## Main endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/courses` | Courses for current user |
| GET | `/api/courses/{id}/title` | Course title (replaces `course_name.php`) |
| GET | `/api/messages?idCours=` | Messages for a course |
| POST | `/api/messages` | Send a message |
| GET | `/api/users/search?query=` | Search profiles |
| GET | `/api/forum` | Forum posts |
| DELETE | `/api/admin/users/{id}` | Delete user (admin) |

Admin routes require `ROLE_ADMIN`.

## PHP integration

Set `config/api.php` in the PHP project:

```php
'enabled' => true,
'base_url' => 'http://localhost:8080',
```

The frontend can then call Spring Boot instead of legacy `actions/*.php` endpoints.
