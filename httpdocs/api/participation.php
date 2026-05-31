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

$response = ['success' => false, 'message' => '', 'participation' => null];

try {
    $session = requireRoles(['admin', 'boss', 'member']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['event_id']) || !isset($input['status'])) {
        http_response_code(400);
        $response['message'] = 'event_id and status are required';
        echo json_encode($response);
        exit();
    }
    
    $eventId = $input['event_id'];
    $status = $input['status'];
    
    if (!in_array($status, ['yes', 'maybe', 'no'])) {
        http_response_code(400);
        $response['message'] = 'Status must be: yes, maybe, or no';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
    $stmt->execute([$eventId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        $response['message'] = 'Event not found';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $pdo->prepare('INSERT INTO event_participation (event_id, member_id, status) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), voted_at = CURRENT_TIMESTAMP');
    $stmt->execute([$eventId, $session['user_id'], $status]);
    
    $stmt = $pdo->prepare('SELECT id, event_id, member_id, status, voted_at FROM event_participation WHERE event_id = ? AND member_id = ?');
    $stmt->execute([$eventId, $session['user_id']]);
    $participation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Vote recorded successfully',
        'participation' => $participation
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
