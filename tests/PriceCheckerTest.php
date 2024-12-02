<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/price_checker.php';

class PriceCheckerTest extends TestCase
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

    public function testFetchCurrentPrice()
    {
        // Example URL for testing
        $adUrl = 'https://www.olx.ua/d/uk/obyavlenie/rasprodazha-vakuumnyy-upakovschik-produktov-vakuumator-dlya-edy-IDMFjhj.html?reason=hp%7Cpromoted';

        // Mock HTML content to simulate a real OLX ad page
        $htmlContent = '<div data-testid="ad-price-container"><h3>1 850 грн.</h3></div>';

        // Mocking cURL response
        $this->mockCurl($htmlContent);

        // Call the fetchCurrentPrice function
        $price = fetchCurrentPrice($adUrl);

        // Assert that the price is extracted correctly
        $this->assertEquals(1850, $price);
    }

    private function mockCurl($htmlContent)
    {
        // Use Mockery to mock cURL
        $mock = \Mockery::mock('alias:curl_exec');
        $mock->shouldReceive('__invoke')
            ->andReturn($htmlContent);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }
}
