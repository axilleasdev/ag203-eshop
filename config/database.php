<?php
/**
 * Database Connection - PDO
 * 
 * Uses PDO (PHP Data Objects) for database access because:
 * - Database-agnostic: can switch to PostgreSQL without code rewrite
 * - Named parameters (:param) in prepared statements — more readable
 * - Built-in exception handling via PDO::ERRMODE_EXCEPTION
 * - Real prepared statements (EMULATE_PREPARES = false) for security
 * 
 * Credentials loaded from Docker environment variables with fallback defaults.
 */

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'eshop';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'root';

try {
    $pdo = null;
    $maxRetries = 5;
    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 2
                ]
            );
            break;
        } catch (PDOException $e) {
            if ($i === $maxRetries - 1) throw $e;
            usleep(500000);
        }
    }
} catch (PDOException $e) {
    http_response_code(503);
    die('Database connection failed. Please try again later.');
}
