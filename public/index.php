<?php

$config = require __DIR__ . '/../config.php';

// Autoload classes (we'll set up autoloading later)

// Handle CORS and request methods
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Parse the request URI to get the path without query string
$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
$path = $parsedUrl['path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/subscribe') {
    require __DIR__ . '/../src/subscribe.php';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/confirm') {
    require __DIR__ . '/../src/confirm.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
