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

$response = ['success' => false, 'message' => '', 'participation' => []];

try {
    $session = requireRoles(['admin', 'boss', 'member']);
    
    $stmt = $pdo->prepare('SELECT ep.id, ep.status, ep.voted_at, e.id as event_id, e.title as event_title, e.event_date
        FROM event_participation ep 
        JOIN events e ON ep.event_id = e.id
        WHERE ep.member_id = ?
        ORDER BY e.event_date ASC');
    $stmt->execute([$session['user_id']]);
    $participation = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Participation retrieved successfully',
        'participation' => $participation
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
