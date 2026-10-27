<?php
/**
 * Database Configuration — Campus Second-Hand Marketplace
 *
 * Uses PDO with MySQL. Update credentials to match your XAMPP setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'campus_marketplace');
define('DB_USER', 'root');        // default XAMPP user
define('DB_PASS', '');            // default XAMPP password (empty)
define('DB_CHARSET', 'utf8mb4');

/**
 * Return a shared PDO instance (singleton pattern).
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Fallback to local SQLite database so frontend functions with full mock/seed data
            $sqlitePath = dirname(__DIR__) . '/database/campus_marketplace.db';
            if (file_exists($sqlitePath)) {
                $pdo = new PDO('sqlite:' . str_replace('\\', '/', $sqlitePath), null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } else {
                http_response_code(500);
                die('Database connection failed. Please check your configuration.');
            }
        }
    }
    return $pdo;
}
