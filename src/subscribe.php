<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Subscription.php';
require_once __DIR__ . '/Mailer.php';

$config = require __DIR__ . '/../config.php';

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);


// Validate input
if (!isset($input['ad_url']) || !filter_var($input['ad_url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing ad_url']);
    exit;
}

if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing email']);
    exit;
}

// Validate that the URL belongs to OLX.ua
if (strpos(parse_url($input['ad_url'], PHP_URL_HOST), 'olx.ua') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'The ad_url must be an OLX.ua URL']);
    exit;
}

$db = new Database($config['db']);
$subscription = new Subscription($db);

$token = bin2hex(random_bytes(30));

$subscriptionId = $subscription->create([
    'ad_url' => $input['ad_url'],
    'email' => $input['email'],
    'confirmation_token' => $token,
    'status' => 'pending',
]);

// Send confirmation email
$mailer = new Mailer($config['mail']);
$confirmationLink = 'http://' . $_SERVER['HTTP_HOST'] . '/confirm?token=' . $token;

$subject = 'Please confirm your subscription';

// Use TemplateRenderer to render the HTML message
$templateVariables = [
    'confirmation_link' => htmlspecialchars($confirmationLink),
];

$htmlMessage = TemplateRenderer::render(
    __DIR__ . '/../templates/confirmation_email.html',
    $templateVariables
);

// Render the plain text message using the template
$plainTextMessage = TemplateRenderer::render(
    __DIR__ . '/../templates/confirmation_email.txt',
    [
        'confirmation_link' => $confirmationLink,
    ]
);

$mailSent = $mailer->send($input['email'], $subject, $htmlMessage, $plainTextMessage);

if ($mailSent) {
    echo json_encode(['message' => 'Confirmation email sent']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send confirmation email']);
}
