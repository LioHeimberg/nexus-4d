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
require_once __DIR__ . '/../config/auth.php';

$response = ['success' => false, 'message' => '', 'reviews' => [], 'stats' => null];

try {
    $session = requireRoles(['admin', 'boss', 'member']);
    
    $stmt = $pdo->prepare('SELECT r.id, r.rating_friendly, r.rating_professional, r.rating_overall, r.comment, r.created_at,
        u.first_name as reviewer_first_name, u.last_name as reviewer_last_name,
        e.title as event_title, b.name as bar_name
        FROM reviews r 
        LEFT JOIN users u ON r.reviewer_id = u.id
        LEFT JOIN events e ON r.event_id = e.id
        LEFT JOIN bars b ON r.bar_id = b.id
        WHERE r.target_user_id = ?
        ORDER BY r.created_at DESC');
    $stmt->execute([$session['user_id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare('SELECT 
        COUNT(*) as total_reviews,
        ROUND(AVG(rating_friendly), 2) as avg_friendly,
        ROUND(AVG(rating_professional), 2) as avg_professional,
        ROUND(AVG(rating_overall), 2) as avg_overall
        FROM reviews WHERE target_user_id = ?');
    $stmt->execute([$session['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Reviews retrieved successfully',
        'reviews' => $reviews,
        'stats' => $stats
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
