<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Mailer.php';

class MailerTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        // Load configuration for the tests
        $this->config = require __DIR__ . '/../config.php';

        // Mock Database Initialization
        $this->mockDatabase();
    }

    private function mockDatabase()
    {
        $dbConfig = $this->config['db'];
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
            $dbConfig['user'],
            $dbConfig['pass']
        );
        $pdo->exec('TRUNCATE TABLE subscriptions'); // Clear the table for each test
    }

    public function testSendEmail()
    {
        $mailConfig = [
            'smtp_host' => $this->config['mail']['smtp_host'],
            'smtp_port' => $this->config['mail']['smtp_port'],
            'smtp_user' => $this->config['mail']['smtp_user'],
            'smtp_pass' => $this->config['mail']['smtp_pass'],
            'from_email' => $this->config['mail']['from_email'],
            'from_name' => $this->config['mail']['from_name'],
        ];

        $mailer = new Mailer($mailConfig);

        $result = $mailer->send(
            $this->config['test_email'],
            'Test Subject',
            '<h1>Test Message</h1>',
            'Test Message'
        );

        $this->assertTrue($result);
    }
}
