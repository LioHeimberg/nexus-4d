<?php

declare(strict_types=1);

header('Content-Type: application/json');

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'nexusData';
$user = getenv('DB_USER') ?: 'dev';
$pass = getenv('DB_PASS') ?: 'dev#123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connected successfully',
        'host' => $host,
        'dbname' => $dbname
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
