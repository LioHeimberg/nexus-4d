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
require_once __DIR__ . '/../config/auth.php';

$response = ['success' => false, 'message' => ''];

try {
    $token = getTokenFromHeader();
    
    $stmt = $pdo->prepare('SELECT s.id, s.user_id, s.expires_at, u.email, u.role, u.first_name, u.last_name 
        FROM sessions s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.token = ?');
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(401);
        $response['message'] = 'Invalid or expired token';
        echo json_encode($response);
        exit();
    }
    
    if (strtotime($session['expires_at']) < time()) {
        $stmt = $pdo->prepare('DELETE FROM sessions WHERE token = ?');
        $stmt->execute([$token]);
        http_response_code(401);
        $response['message'] = 'Session expired';
        echo json_encode($response);
        exit();
    }
    
    $expiresAt = date('Y-m-d H:i:s', time() + (6 * 60 * 60));
    
    $stmt = $pdo->prepare('UPDATE sessions SET expires_at = ?, last_used = CURRENT_TIMESTAMP WHERE token = ?');
    $stmt->execute([$expiresAt, $token]);
    
    $response = [
        'success' => true,
        'message' => 'Token refreshed',
        'user' => [
            'id' => $session['user_id'],
            'email' => $session['email'],
            'role' => $session['role'],
            'first_name' => $session['first_name'],
            'last_name' => $session['last_name']
        ],
        'token' => $token,
        'expires_at' => $expiresAt
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
