<?php

declare(strict_types=1);

// Nexus 4D - Database Configuration
// Database connection settings
// For production, update these values

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'nexus4ddata';
$user = getenv('DB_USER') ?: 'nexus4ddata';
$pass = getenv('DB_PASS') ?: 's1f83@O7w';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    throw new Exception('Database connection failed: ' . $e->getMessage());
}
