<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Subscription.php';

class SubscriptionTest extends TestCase
{
    private $db;
    private $subscription;
    private $config;

    protected function setUp(): void
    {
        // Load configuration for database
        $this->config = require __DIR__ . '/../config.php';

        // Initialize the Database object
        $this->db = new Database($this->config['db']);

        // Truncate the subscriptions table before each test
        $pdo = $this->db->getConnection();
        $pdo->exec('TRUNCATE TABLE subscriptions');

        // Initialize the Subscription object
        $this->subscription = new Subscription($this->db);
    }

    public function testCreateSubscription()
    {
        $data = [
            'ad_url' => 'https://www.olx.ua/d/uk/obyavlenie/rasprodazha-vakuumnyy-upakovschik-produktov-vakuumator-dlya-edy-IDMFjhj.html?reason=hp%7Cpromoted',
            'email' => $this->config['test_email'],
            'confirmation_token' => 'testtoken',
            'status' => 'pending',
        ];

        // Call the create method and assert the result
        $id = $this->subscription->create($data);
        $this->assertIsInt((int)$id);

        // Verify that the record exists in the database
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($result);
        $this->assertEquals($data['ad_url'], $result['ad_url']);
        $this->assertEquals($data['email'], $result['email']);
        $this->assertEquals($data['confirmation_token'], $result['confirmation_token']);
        $this->assertEquals($data['status'], $result['status']);
    }
}
