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
require_once __DIR__ . '/auth.php';

$response = ['success' => false, 'message' => '', 'event' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        $response['message'] = 'Event ID is required';
        echo json_encode($response);
        exit();
    }
    
    $eventId = $input['id'];
    
    $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
    $stmt->execute([$eventId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        $response['message'] = 'Event not found';
        echo json_encode($response);
        exit();
    }
    
    $updates = [];
    $params = [];
    
    if (isset($input['title'])) {
        $updates[] = 'title = ?';
        $params[] = trim($input['title']);
    }
    
    if (isset($input['description'])) {
        $updates[] = 'description = ?';
        $params[] = trim($input['description']);
    }
    
    if (isset($input['event_date'])) {
        $updates[] = 'event_date = ?';
        $params[] = $input['event_date'];
    }
    
    if (isset($input['location'])) {
        $updates[] = 'location = ?';
        $params[] = trim($input['location']);
    }
    
    if (!empty($updates)) {
        $params[] = $eventId;
        $sql = 'UPDATE events SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $stmt = $pdo->prepare('SELECT e.id, e.title, e.description, e.event_date, e.location, 
        u.first_name, u.last_name
        FROM events e 
        JOIN users u ON e.boss_id = u.id 
        WHERE e.id = ?');
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Event updated successfully',
        'event' => $event
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
