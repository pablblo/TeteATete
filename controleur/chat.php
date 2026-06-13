<?php

require_once __DIR__ . '/../bootstrap.php';

requireAuth();

$userId = (int) $_SESSION['user_id'];

require __DIR__ . '/../vue/pages/chat.php';
