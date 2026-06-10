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
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password']) || !isset($input['role']) || !isset($input['first_name']) || !isset($input['last_name'])) {
        http_response_code(400);
        $response['message'] = 'All fields are required: email, password, role, first_name, last_name';
        echo json_encode($response);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    $role = $input['role'];
    $firstName = trim($input['first_name']);
    $lastName = trim($input['last_name']);
    
    if (!in_array($role, ['admin', 'boss', 'member'])) {
        http_response_code(400);
        $response['message'] = 'Invalid role. Must be: admin, boss, or member';
        echo json_encode($response);
        exit();
    }
    
    if ($session['role'] === 'boss' && $role === 'admin') {
        http_response_code(403);
        $response['message'] = 'Boss cannot create admin accounts';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        $response['message'] = 'Email already exists';
        echo json_encode($response);
        exit();
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$email, $passwordHash, $role, $firstName, $lastName]);
    
    $userId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    unset($user['password_hash']);
    
    $response = [
        'success' => true,
        'message' => 'User created successfully',
        'user' => $user
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
