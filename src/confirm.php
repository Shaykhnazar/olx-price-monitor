<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Subscription.php';

if (!isset($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

$token = $_GET['token'];

$db = new Database($config['db']);
$subscription = new Subscription($db);

$subscriptionData = $subscription->findByToken($token);

if (!$subscriptionData) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Update the subscription status to 'active'
$subscription->activate($subscriptionData['id']);

echo json_encode(['message' => 'Email confirmed. Subscription is now active.']);
