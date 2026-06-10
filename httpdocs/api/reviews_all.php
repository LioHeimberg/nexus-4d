<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => '', 'reviews' => []];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $stmt = $pdo->prepare('SELECT r.id, r.reviewer_type, r.rating_friendly, r.rating_professional, r.rating_overall, r.comment, r.created_at,
        u.first_name as reviewer_first_name, u.last_name as reviewer_last_name,
        e.title as event_title, b.name as bar_name
        FROM reviews r 
        LEFT JOIN users u ON r.reviewer_id = u.id
        LEFT JOIN events e ON r.event_id = e.id
        LEFT JOIN bars b ON r.bar_id = b.id
        ORDER BY r.created_at DESC');
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'All reviews retrieved successfully',
        'reviews' => $reviews
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
