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

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => ''];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['review_id'])) {
        http_response_code(400);
        $response['message'] = 'review_id is required';
        echo json_encode($response);
        exit();
    }
    
    $reviewId = $input['review_id'];
    
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt->execute([$reviewId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        $response['message'] = 'Review not found';
    } else {
        $response['success'] = true;
        $response['message'] = 'Review deleted successfully';
    }
    
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