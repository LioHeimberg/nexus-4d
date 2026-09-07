<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => '', 'members' => []];

try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE role = 'member' ORDER BY first_name ASC");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Members retrieved successfully',
        'members' => $members
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
