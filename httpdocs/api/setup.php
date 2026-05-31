<?php

declare(strict_types=1);

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
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password']) || !isset($input['first_name']) || !isset($input['last_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    $firstName = trim($input['first_name']);
    $lastName = trim($input['last_name']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$email, $passwordHash, 'admin', $firstName, $lastName]);
    
    $userId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve created user']);
        exit();
    }
    
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + (6 * 60 * 60));
    
    $stmt = $pdo->prepare('INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$user['id'], $token, $expiresAt]);
    
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Setup successful',
        'user' => $user,
        'token' => $token,
        'expires_at' => $expiresAt
    ]);
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Admin account already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Setup failed: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
