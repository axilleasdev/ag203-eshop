<?php
try {
    $pdo = new PDO(
        "mysql:host=" . ($_ENV['DB_HOST'] ?? 'db') . ";dbname=" . ($_ENV['DB_NAME'] ?? 'eshop'),
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? 'root',
        [PDO::ATTR_TIMEOUT => 2]
    );
    http_response_code(200);
    echo "OK";
} catch (Exception $e) {
    http_response_code(503);
    echo "NOT READY";
}
