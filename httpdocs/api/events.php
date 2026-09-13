<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$response = [
    'success' => false,
    'message' => '',
    'events' => []
];

try {
    $session = requireRoles(['admin', 'boss', 'member']);

    /*
     * =========================
     * SAVE EVENT PARTICIPATION
     * =========================
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Nur Member dürfen abstimmen
        if ($session['role'] !== 'member') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Only members can vote on events.'
            ]);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $eventId = isset($input['event_id']) ? (int)$input['event_id'] : 0;
        $status = $input['status'] ?? '';

        if ($eventId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid event ID.'
            ]);
            exit();
        }

        if (!in_array($status, ['yes', 'maybe', 'no'], true)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid participation status.'
            ]);
            exit();
        }

        // Prüfen, ob Event existiert
        $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
        $stmt->execute([$eventId]);

        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Event not found.'
            ]);
            exit();
        }

        /*
         * Wenn bereits eine Auswahl existiert:
         * -> UPDATE
         *
         * Wenn noch keine Auswahl existiert:
         * -> INSERT
         *
         * Dadurch kann ein Member pro Event
         * immer nur EINE Auswahl haben.
         */
        $stmt = $pdo->prepare(
            'SELECT id
             FROM event_participation
             WHERE event_id = ? AND member_id = ?'
        );
        $stmt->execute([
            $eventId,
            $session['user_id']
        ]);

        $participationId = $stmt->fetchColumn();

        if ($participationId) {

            $stmt = $pdo->prepare(
                'UPDATE event_participation
                 SET status = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $status,
                $participationId
            ]);

        } else {

            $stmt = $pdo->prepare(
                'INSERT INTO event_participation
                    (event_id, member_id, status)
                 VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $eventId,
                $session['user_id'],
                $status
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Participation updated successfully.',
            'event_id' => $eventId,
            'my_vote' => $status
        ]);

        exit();
    }


    /*
     * =========================
     * GET EVENTS
     * =========================
     */

    $stmt = $pdo->prepare("
        SELECT
            e.id,
            e.title,
            e.description,
            e.event_date,
            e.location,
            u.first_name,
            u.last_name,

            (
                SELECT COUNT(*)
                FROM event_participation ep
                WHERE ep.event_id = e.id
                AND ep.status = 'yes'
            ) AS yes_count,

            (
                SELECT COUNT(*)
                FROM event_participation ep
                WHERE ep.event_id = e.id
                AND ep.status = 'maybe'
            ) AS maybe_count,

            (
                SELECT COUNT(*)
                FROM event_participation ep
                WHERE ep.event_id = e.id
                AND ep.status = 'no'
            ) AS no_count

        FROM events e
        JOIN users u ON e.boss_id = u.id
        ORDER BY e.event_date ASC
    ");

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * =========================
     * GET MY VOTES
     * =========================
     */

    if ($session['role'] === 'member') {

        $stmt = $pdo->prepare(
            'SELECT event_id, status
             FROM event_participation
             WHERE member_id = ?'
        );

        $stmt->execute([
            $session['user_id']
        ]);

        $myVotes = [];

        while ($vote = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $myVotes[(int)$vote['event_id']] = $vote['status'];
        }

        foreach ($events as &$event) {
            $eventId = (int)$event['id'];

            $event['my_vote'] = $myVotes[$eventId] ?? null;
        }

        unset($event);
    }


    $response = [
        'success' => true,
        'message' => 'Events retrieved successfully',
        'events' => $events
    ];

    echo json_encode($response);

} catch (Exception $e) {

    if (
        $e->getMessage() === 'Unauthorized' ||
        $e->getMessage() === 'No token provided'
    ) {
        http_response_code(401);

    } elseif ($e->getMessage() === 'Forbidden') {
        http_response_code(403);

    } else {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}