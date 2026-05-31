<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/auth.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    $stmt = $pdo->prepare('SELECT id, email, password_hash, role, first_name, last_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }
    
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + (6 * 60 * 60));
    
    $stmt = $pdo->prepare('INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$user['id'], $token, $expiresAt]);
    
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
        'expires_at' => $expiresAt
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in login.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
