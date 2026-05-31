# Nexus 4D - Database Configuration

# Database connection settings
# For production, update these values

<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'nexusData';
$user = getenv('DB_USER') ?: 'dev';
$pass = getenv('DB_PASS') ?: 'dev#123';
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
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}
