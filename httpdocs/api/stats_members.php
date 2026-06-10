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

$response = ['success' => false, 'message' => '', 'members' => []];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name,
        (SELECT COUNT(*) FROM reviews r WHERE r.target_user_id = u.id) as review_count,
        (SELECT ROUND(AVG(rating_overall), 2) FROM reviews r WHERE r.target_user_id = u.id) as avg_rating
        FROM users u 
        WHERE u.role = 'member'
        ORDER BY u.first_name ASC");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Member stats retrieved successfully',
        'members' => $members
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
