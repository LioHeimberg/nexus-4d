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

$response = ['success' => false, 'message' => '', 'events' => []];

try {
    $session = requireRoles(['admin', 'boss', 'member']);
    
    $stmt = $pdo->prepare('SELECT e.id, e.title, e.description, e.event_date, e.location, 
        u.first_name, u.last_name,
        (SELECT COUNT(*) FROM event_participation ep WHERE ep.event_id = e.id AND ep.status = 'yes') as yes_count,
        (SELECT COUNT(*) FROM event_participation ep WHERE ep.event_id = e.id AND ep.status = 'maybe') as maybe_count,
        (SELECT COUNT(*) FROM event_participation ep WHERE ep.event_id = e.id AND ep.status = 'no') as no_count
        FROM events e 
        JOIN users u ON e.boss_id = u.id 
        ORDER BY e.event_date ASC');
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($session['role'] === 'member') {
        $stmt = $pdo->prepare('SELECT event_id FROM event_participation WHERE member_id = ?');
        $stmt->execute([$session['user_id']]);
        $myVotes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($events as &$event) {
            $event['my_vote'] = in_array($event['id'], $myVotes) 
                ? (function() use ($event, $stmt, $session) {
                    $stmt = $pdo->prepare('SELECT status FROM event_participation WHERE event_id = ? AND member_id = ?');
                    $stmt->execute([$event['id'], $session['user_id']]);
                    return $stmt->fetchColumn();
                })()
                : null;
        }
    }
    
    $response = [
        'success' => true,
        'message' => 'Events retrieved successfully',
        'events' => $events
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
