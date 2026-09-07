<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    
    require_once __DIR__ . '/auth.php';
    
    $pdo = getPDO();
    $session = requireRoles(['admin', 'boss']);
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users ORDER BY created_at DESC');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as &$user) {
        unset($user['password_hash']);
    }
    
    $response = [
        'success' => true,
        'message' => 'Users retrieved successfully',
        'users' => $users
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    if ($e->getMessage() === 'Unauthorized' || $e->getMessage() === 'No token provided') {
        http_response_code(401);
    } elseif ($e->getMessage() === 'Forbidden') {
        http_response_code(403);
    } else {
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
