<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Ensure Composer autoloader is included
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Subscription.php';
require_once __DIR__ . '/Mailer.php';
use voku\helper\HtmlDomParser;

$config = require __DIR__ . '/../config.php';

$db = new Database($config['db']);
$subscriptionModel = new Subscription($db);
$mailer = new Mailer($config['mail']);

// Fetch all unique active ad URLs
$stmtAds = $db->getConnection()->prepare('SELECT DISTINCT ad_url FROM subscriptions WHERE status = "active"');
$stmtAds->execute();
$ads = $stmtAds->fetchAll(PDO::FETCH_ASSOC);

foreach ($ads as $ad) {
    $adUrl = $ad['ad_url'];

    // Fetch the current price
    $currentPrice = fetchCurrentPrice($adUrl);

    if ($currentPrice === null) {
        continue; // Skip if price couldn't be fetched
    }

    // Get all subscriptions for this ad
    $stmtSubs = $db->getConnection()->prepare('SELECT * FROM subscriptions WHERE ad_url = :ad_url AND status = "active"');
    $stmtSubs->execute([':ad_url' => $adUrl]);
    $subscriptions = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subscriptions as $subscription) {
        if ($subscription['last_known_price'] != $currentPrice) {
            // Update the last known price
            $stmtUpdate = $db->getConnection()->prepare('UPDATE subscriptions SET last_known_price = :price WHERE id = :id');
            $stmtUpdate->execute([
                ':price' => $currentPrice,
                ':id' => $subscription['id'],
            ]);

            // Send notification email
            $subject = 'Price Change Notification';

            // Use TemplateRenderer to render HTML and plain text messages
            $templateVariables = [
                'ad_url'     => htmlspecialchars($adUrl),
                'old_price'  => htmlspecialchars($subscription['last_known_price']),
                'new_price'  => htmlspecialchars($currentPrice),
            ];

            $htmlMessage = TemplateRenderer::render(
                __DIR__ . '/../templates/price_change_notification.html',
                $templateVariables
            );

            // Render the plain text message
            $plainTextMessage = TemplateRenderer::render(
                __DIR__ . '/../templates/price_change_notification.txt',
                [
                    'ad_url'     => $adUrl,
                    'old_price'  => $subscription['last_known_price'],
                    'new_price'  => $currentPrice,
                ]
            );

            $mailer->send($subscription['email'], $subject, $htmlMessage, $plainTextMessage);
        }
    }
}

/**
 * @param $adUrl
 * @return int|null
 */
function fetchCurrentPrice($adUrl)
{
    // Proxy setup
    $proxy = 'http://a7674226774c27db6adad3334ffaa303f5a79043:@api.zenrows.com:8001';

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $adUrl);
    curl_setopt($ch, CURLOPT_PROXY, $proxy); // Use ZenRows proxy
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36',
    ];

    $randomUserAgent = $userAgents[array_rand($userAgents)];
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: ' . $randomUserAgent]);

    // Execute the cURL request
    $htmlContent = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    // Parse the HTML content
    $dom = HtmlDomParser::str_get_html($htmlContent);

    if ($dom === false) {
        error_log("Failed to parse HTML content for URL: $adUrl");
        return null;
    }

    // Find the price element using the appropriate CSS selector
    $priceElement = $dom->findOne('div[data-testid="ad-price-container"] h3');

    if ($priceElement === null) {
        error_log("Price element not found on page: $adUrl");
        return null;
    }

    // Extract and sanitize the price value
    $price = trim($priceElement->text());
    $price = preg_replace('/[^\d]/', '', $price); // Remove non-numeric characters

    return $price ? (int)$price : null;
}

