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
require_once __DIR__ . '/lipsum_generator.php';

use nexus4d\api\LipsumGenerator;

$response = ['success' => false, 'message' => '', 'user' => null];

try {
    $session = requireRoles(['admin']);

    
    $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['AdminPass'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Admin password is required']);
        exit();
    }

    $stmt = $pdo->prepare('SELECT id, email, password_hash, role, first_name, last_name FROM users WHERE role = ?');
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($input['AdminPass'], $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid Admin password']);
        exit();
    }
    
    $passwordHash = password_hash("123", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['boss@example.com', $passwordHash, 'boss', 'Boss', 'User']);
    
    $bossId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$bossId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['member@example.com', $passwordHash, 'member', 'Member', 'User']);
    
    $userId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    
    /* --- Create Dummy Event --- */

    function generateDummyEvent($bossId, $pdo)
    {

        $title = trim(LipsumGenerator::getWords(3, false));
        $description = trim(LipsumGenerator::getParagraphs(1));
        $eventDate = date('Y-m-d H:i:s', mt_rand(1789214047, 2942992800));
        $location = trim(LipsumGenerator::getWords(1, false));
        
        $stmt = $pdo->prepare('INSERT INTO events (title, description, event_date, location, boss_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $eventDate, $location, $bossId]);
        
        $eventId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('SELECT e.id, e.title, e.description, e.event_date, e.location, 
            u.first_name, u.last_name
            FROM events e 
            JOIN users u ON e.boss_id = u.id 
            WHERE e.id = ?');
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

    }

    for ($i = 0; $i < 3; $i++) {
        generateDummyEvent($bossId, $pdo);
    }
    
    unset($user['password_hash']);
    
    $response = [
        'success' => true,
        'message' => 'Dummy Data created successfully',
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
