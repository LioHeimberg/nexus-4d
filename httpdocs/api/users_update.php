<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$response = ['success' => false, 'message' => '', 'user' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        $response['message'] = 'User ID is required';
        echo json_encode($response);
        exit();
    }
    
    $userId = $input['id'];
    
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        $response['message'] = 'User not found';
        echo json_encode($response);
        exit();
    }
    
    if ($session['role'] === 'boss' && $user['role'] === 'admin') {
        http_response_code(403);
        $response['message'] = 'Boss cannot modify admin accounts';
        echo json_encode($response);
        exit();
    }
    
    $updates = [];
    $params = [];
    
    if (isset($input['email'])) {
        $email = trim($input['email']);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            exit();
        }
        
        $updates[] = 'email = ?';
        $params[] = $email;
    }
    
    if (isset($input['role'])) {
        $role = $input['role'];
        
        if (!in_array($role, ['admin', 'boss', 'member'])) {
            http_response_code(400);
            $response['message'] = 'Invalid role';
            echo json_encode($response);
            exit();
        }
        
        if ($session['role'] === 'boss' && $role === 'admin') {
            http_response_code(403);
            $response['message'] = 'Boss cannot assign admin role';
            echo json_encode($response);
            exit();
        }
        
        $updates[] = 'role = ?';
        $params[] = $role;
    }
    
    if (isset($input['first_name'])) {
        $updates[] = 'first_name = ?';
        $params[] = trim($input['first_name']);
    }
    
    if (isset($input['last_name'])) {
        $updates[] = 'last_name = ?';
        $params[] = trim($input['last_name']);
    }
    
    if (!empty($updates)) {
        $params[] = $userId;
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    unset($user['password_hash']);
    
    $response = [
        'success' => true,
        'message' => 'User updated successfully',
        'user' => $user
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
