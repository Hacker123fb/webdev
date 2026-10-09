<?php
/**
 * Database Configuration & Connection
 * Uses PHP Data Objects (PDO) for secure, prepared-statement ready MySQL connectivity.
 */

// Database server credentials (default for local XAMPP/WAMP environment)
$db_host    = 'localhost';
$db_name    = 'library_management';
$db_user    = 'root';
$db_pass    = ''; // Default is blank in XAMPP
$db_charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

// PDO Connection Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use true prepared statements for security
];

try {
    // Establish connection instance
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Graceful error message without leaking sensitive internal credentials
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}

/**
 * Simple helper to return the database connection
 *
 * @return PDO
 */
function db() {
    global $pdo;
    return $pdo;
}
