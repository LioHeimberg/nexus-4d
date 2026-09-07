<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => ''];

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
    
    if ($user['role'] === 'admin') {
        http_response_code(403);
        $response['message'] = 'Cannot delete admin account';
        echo json_encode($response);
        exit();
    }
    
    if ($session['role'] === 'boss' && $user['role'] === 'admin') {
        http_response_code(403);
        $response['message'] = 'Boss cannot delete admin accounts';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    
    $response = [
        'success' => true,
        'message' => 'User deleted successfully'
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
