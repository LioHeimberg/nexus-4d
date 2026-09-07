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

$response = ['success' => false, 'message' => '', 'event' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['title']) || !isset($input['event_date']) || !isset($input['location'])) {
        http_response_code(400);
        $response['message'] = 'Title, event_date, and location are required';
        echo json_encode($response);
        exit();
    }
    
    $title = trim($input['title']);
    $description = isset($input['description']) ? trim($input['description']) : null;
    $eventDate = $input['event_date'];
    $location = trim($input['location']);
    
    $stmt = $pdo->prepare('INSERT INTO events (title, description, event_date, location, boss_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$title, $description, $eventDate, $location, $session['user_id']]);
    
    $eventId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT e.id, e.title, e.description, e.event_date, e.location, 
        u.first_name, u.last_name
        FROM events e 
        JOIN users u ON e.boss_id = u.id 
        WHERE e.id = ?');
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Event created successfully',
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
