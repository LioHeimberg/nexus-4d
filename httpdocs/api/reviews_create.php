<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'message' => '', 'review' => null];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['target_user_id']) || !isset($input['rating_friendly']) || !isset($input['rating_professional']) || !isset($input['rating_overall'])) {
        http_response_code(400);
        $response['message'] = 'target_user_id, rating_friendly, rating_professional, and rating_overall are required';
        echo json_encode($response);
        exit();
    }
    
    $targetUserId = $input['target_user_id'];
    $ratingFriendly = $input['rating_friendly'];
    $ratingProfessional = $input['rating_professional'];
    $ratingOverall = $input['rating_overall'];
    $reviewerType = $input['reviewer_type'] ?? 'guest';
    $reviewerId = $input['reviewer_id'] ?? null;
    $eventId = $input['event_id'] ?? null;
    $barId = $input['bar_id'] ?? null;
    $comment = isset($input['comment']) ? trim($input['comment']) : null;
    
    if (!in_array($ratingFriendly, [1, 2, 3, 4, 5]) || !in_array($ratingProfessional, [1, 2, 3, 4, 5]) || !in_array($ratingOverall, [1, 2, 3, 4, 5])) {
        http_response_code(400);
        $response['message'] = 'Ratings must be between 1 and 5';
        echo json_encode($response);
        exit();
    }
    
    if (!in_array($reviewerType, ['guest', 'member', 'boss'])) {
        http_response_code(400);
        $response['message'] = 'Invalid reviewer type';
        echo json_encode($response);
        exit();
    }
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$targetUserId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        $response['message'] = 'Target user not found';
        echo json_encode($response);
        exit();
    }
    
    if ($eventId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
        $stmt->execute([$eventId]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            $response['message'] = 'Event not found';
            echo json_encode($response);
            exit();
        }
    }
    
    if ($barId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM bars WHERE id = ?');
        $stmt->execute([$barId]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            $response['message'] = 'Bar not found';
            echo json_encode($response);
            exit();
        }
    }
    
    // For guest reviewers, we don't need to validate reviewer_id
    // The reviewer_id can be null for guests, which is handled by the database
    
    $stmt = $pdo->prepare('INSERT INTO reviews (reviewer_type, reviewer_id, target_user_id, event_id, bar_id, rating_friendly, rating_professional, rating_overall, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$reviewerType, $reviewerId, $targetUserId, $eventId, $barId, $ratingFriendly, $ratingProfessional, $ratingOverall, $comment]);
    
    $reviewId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('SELECT r.id, r.reviewer_type, r.rating_friendly, r.rating_professional, r.rating_overall, r.comment, r.created_at,
        u.first_name as reviewer_first_name, u.last_name as reviewer_last_name
        FROM reviews r 
        LEFT JOIN users u ON r.reviewer_id = u.id
        WHERE r.id = ?');
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Review submitted successfully',
        'review' => $review
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
}
