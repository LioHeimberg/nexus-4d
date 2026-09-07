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

$response = ['success' => false, 'message' => '', 'bar' => null];

try {
    $session = requireRoles(['admin', 'boss']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['location'])) {
        http_response_code(400);
        $response['message'] = 'Name and location are required';
        echo json_encode($response);
        exit();
    }
    
    $name = trim($input['name']);
    $location = trim($input['location']);
    
    $stmt = $pdo->prepare('INSERT INTO bars (name, location, boss_id) VALUES (?, ?, ?)');
    $stmt->execute([$name, $location, $session['user_id']]);
    
    $barId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT id, name, location FROM bars WHERE id = ?');
    $stmt->execute([$barId]);
    $bar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Bar created successfully',
        'bar' => $bar
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
