<?php

require_once __DIR__ . '/../bootstrap.php';

requireAuth();

$userId = (int) $_SESSION['user_id'];
$useSpringApi = apiEnabled();
$coursesUrl = $useSpringApi
    ? apiBaseUrl() . '/api/courses?idUser=' . $userId
    : 'actions/get_courses.php?idUser=' . $userId;
$apiToken = $_SESSION['api_token'] ?? '';

require __DIR__ . '/../vue/pages/chat.php';
