<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'db' => [
        'host' => $_ENV['DB_HOST'],
        'dbname' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'],
    ],
    'mail' => [
        'smtp_host' => $_ENV['SMTP_HOST'],
        'smtp_port' => $_ENV['SMTP_PORT'],
        'smtp_user' => $_ENV['SMTP_USER'],
        'smtp_pass' => $_ENV['SMTP_PASS'],
        'from_email' => $_ENV['SMTP_FROM'],
        'from_name' => $_ENV['SMTP_NAME'],
    ],
    'test_email' => $_ENV['TEST_EMAIL'],
];
