<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$dbConfig = require __DIR__ . '/config/database.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['dbname'],
        $dbConfig['charset']
    );
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

require_once __DIR__ . '/controleur/functions.php';

$apiConfig = require __DIR__ . '/config/api.php';

function apiConfig(): array
{
    global $apiConfig;
    return $apiConfig;
}

function apiEnabled(): bool
{
    return !empty(apiConfig()['enabled']);
}

function apiBaseUrl(): string
{
    return rtrim(apiConfig()['base_url'], '/');
}

function requireAuth(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin(): void
{
    requireAuth();
    if (empty($_SESSION['Admin'])) {
        header('Location: page_principale.php');
        exit();
    }
}
