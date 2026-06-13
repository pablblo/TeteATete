<?php

return [
    'enabled' => false,
    'base_url' => 'http://localhost:8080',
    'endpoints' => [
        'courses' => '/api/courses',
        'course_title' => '/api/courses/%d/title',
        'messages' => '/api/messages?idCours=%d',
        'send_message' => '/api/messages',
        'login' => '/api/auth/login',
        'register' => '/api/auth/register',
    ],
];
