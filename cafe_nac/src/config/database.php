<?php
/**
 * NAC Cafe - Database Configuration
 */

$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'nac_user';
$db_pass = getenv('DB_PASS') ?: 'nac_password';
$db_name = getenv('DB_NAME') ?: 'nac_cafe';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please try again later.");
}

// Also create mysqli connection (used by some legacy features - intentionally vulnerable)
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Database connection failed.");
}
$mysqli->set_charset("utf8mb4");
