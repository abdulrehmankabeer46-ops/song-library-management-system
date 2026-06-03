<?php
// ============================================================
//  Database Configuration
//  Song Library Management System
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // default XAMPP user
define('DB_PASS', '');           // default XAMPP password (blank)
define('DB_NAME', 'song_library_db');

/**
 * Returns a MySQLi connection object.
 * Kills script with error message if connection fails.
 */
function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
