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

$response = ['success' => false, 'message' => '', 'post' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        $response['message'] = 'Post ID is required';
        echo json_encode($response);
        exit();
    }
    
    $postId = $input['id'];
    
    $stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        $response['message'] = 'Post not found';
        echo json_encode($response);
        exit();
    }
    
    $updates = [];
    $params = [];
    
    if (isset($input['title'])) {
        $updates[] = 'title = ?';
        $params[] = trim($input['title']);
    }
    
    if (isset($input['content'])) {
        $updates[] = 'content = ?';
        $params[] = trim($input['content']);
    }
    
    if (!empty($updates)) {
        $params[] = $postId;
        $sql = 'UPDATE posts SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $stmt = $pdo->prepare('SELECT p.id, p.title, p.content, p.published_at, u.first_name, u.last_name
        FROM posts p 
        JOIN users u ON p.boss_id = u.id 
        WHERE p.id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Post updated successfully',
        'post' => $post
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
