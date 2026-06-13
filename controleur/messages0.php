<?php

require_once __DIR__ . '/../bootstrap.php';

requireAuth();

$useSpringApi = apiEnabled();
$messagesUrlTemplate = $useSpringApi
    ? apiBaseUrl() . '/api/messages?idCours='
    : 'messages.php?idCours=';
$courseTitleUrlTemplate = $useSpringApi
    ? apiBaseUrl() . '/api/courses/'
    : 'actions/course_name.php?idCours=';
$sendMessageUrl = $useSpringApi
    ? apiBaseUrl() . '/api/messages'
    : 'actions/send_message.php';
$apiToken = $_SESSION['api_token'] ?? '';

require __DIR__ . '/../vue/pages/messages0.php';
