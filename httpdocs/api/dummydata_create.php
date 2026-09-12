<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => '', 'user' => null];

try {
    $session = requireRoles(['admin']);

    
    $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['AdminPass'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Admin password is required']);
        exit();
    }

    $stmt = $pdo->prepare('SELECT id, email, password_hash, role, first_name, last_name FROM users WHERE role = ?');
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($input['AdminPass'], $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid Admin password']);
        exit();
    }
    
    $passwordHash = password_hash("123", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['boss@liolp.ch', $passwordHash, 'boss', 'Boss', 'User']);
    
    $userId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    unset($user['password_hash']);
    
    $response = [
        'success' => true,
        'message' => 'Dummy Data created successfully',
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
