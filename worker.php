<?php

while (true) {
    echo "[" . date('Y-m-d H:i:s') . "] Running price checker...\n";

    // Run your cron task
    require_once __DIR__ . '/src/price_checker.php';

    // Wait for 1 hour (3600 seconds)
    sleep(3600);
}
