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

$response = ['success' => false, 'message' => '', 'post' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['title']) || !isset($input['content'])) {
        http_response_code(400);
        $response['message'] = 'Title and content are required';
        echo json_encode($response);
        exit();
    }
    
    $title = trim($input['title']);
    $content = trim($input['content']);
    
    $stmt = $pdo->prepare('INSERT INTO posts (title, content, boss_id) VALUES (?, ?, ?)');
    $stmt->execute([$title, $content, $session['user_id']]);
    
    $postId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT p.id, p.title, p.content, p.published_at, u.first_name, u.last_name
        FROM posts p 
        JOIN users u ON p.boss_id = u.id 
        WHERE p.id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Post created successfully',
        'post' => $post
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
