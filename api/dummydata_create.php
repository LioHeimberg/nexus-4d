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
            echo json_encode([
                'success' => false,
                'message' => 'Admin password is required'
            ]);
            exit();
        }

        $bossCount = isset($input['bosses'])
            ? (int) $input['bosses']
            : 0;

        $memberCount = isset($input['members'])
            ? (int) $input['members']
            : 0;

        $eventCount = isset($input['events'])
            ? (int) $input['events']
            : 0;

        $postCount = isset($input['posts'])
            ? (int) $input['posts']
            : 0;

        $barCount = isset($input['bars'])
            ? (int) $input['bars']
            : 0;

        $reviewCount = isset($input['reviews'])
            ? (int) $input['reviews']
            : 0;

        $counts = [
            'bosses' => $bossCount,
            'members' => $memberCount,
            'events' => $eventCount,
            'posts' => $postCount,
            'bars' => $barCount,
            'reviews' => $reviewCount
        ];

        foreach ($counts as $type => $count) {
            if ($count < 0 || $count > 1000) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => "Invalid amount for {$type}. Must be between 0 and 1000."
                ]);

                exit();
            }
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

    function generateUniqueDummyName(PDO $pdo, string $prefix): string
    {
        for ($i = 0; $i < 20; $i++) {

            $name = trim(LipsumGenerator::getWords(1, false));

            $name = explode(' ', $name)[0];

            $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

            if ($name === '') {
                $name = 'user';
            }

            $name = strtolower($name);

            $email = $prefix . '-' . $name . '@example.com';

            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM users WHERE email = ?'
            );

            $stmt->execute([$email]);

            if ((int) $stmt->fetchColumn() === 0) {
                return $name;
            }

            for ($number = 2; $number <= 100; $number++) {

                $uniqueName = $name . $number;
                $email = $prefix . '-' . $uniqueName . '@example.com';

                $stmt->execute([$email]);

                if ((int) $stmt->fetchColumn() === 0) {
                    return $uniqueName;
                }
            }
        }

        throw new \RuntimeException(
            'Could not generate a unique dummy name.'
        );
    }


    function generateDummyBoss(PDO $pdo, string $passwordHash): int
    {
        $bossname = generateUniqueDummyName($pdo, 'boss');

        $email = 'boss-' . $bossname . '@example.com';

        $stmt = $pdo->prepare(
            'INSERT INTO users
            (email, password_hash, role, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $email,
            $passwordHash,
            'boss',
            'Boss',
            $bossname
        ]);

        return (int) $pdo->lastInsertId();
    }


    function generateDummyMember(PDO $pdo, string $passwordHash): int
    {
        $membername = generateUniqueDummyName($pdo, 'member');

        $email = 'member-' . $membername . '@example.com';

        $stmt = $pdo->prepare(
            'INSERT INTO users
            (email, password_hash, role, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $email,
            $passwordHash,
            'member',
            'Member',
            $membername
        ]);

        return (int) $pdo->lastInsertId();
    }


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

    function generateDummyPost($bossId, $pdo)
    {
        $title = trim(LipsumGenerator::getWords(3, false));
        $content = trim(LipsumGenerator::getWords(25));
        
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, boss_id) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $bossId]);
        
        $postId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('SELECT p.id, p.title, p.content, p.published_at, 
            u.first_name, u.last_name
            FROM posts p 
            JOIN users u ON p.boss_id = u.id 
            WHERE p.id = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function generateDummyBar($bossId, $pdo)
    {
        $name = 'Rümli - ' . trim(LipsumGenerator::getWords(1, false));
        $location = trim(LipsumGenerator::getWords(1, false));
        
        $stmt = $pdo->prepare('INSERT INTO bars (name, location, boss_id) VALUES (?, ?, ?)');
        $stmt->execute([$name, $location, $bossId]);
        
        $barId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('SELECT id, name, location FROM bars WHERE id = ?');
        $stmt->execute([$barId]);
        $bar = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function generateDummyReview(PDO $pdo): int
    {

        $stmt = $pdo->query(
            "SELECT id
            FROM users
            WHERE role = 'member'
            ORDER BY RAND()
            LIMIT 1"
        );

        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            throw new \RuntimeException(
                'No member found for dummy review.'
            );
        }

        $targetUserId = (int) $member['id'];

        $useEvent = (bool) random_int(0, 1);

        $eventId = null;
        $barId = null;

        if ($useEvent) {

            $stmt = $pdo->query(
                "SELECT id
                FROM events
                ORDER BY RAND()
                LIMIT 1"
            );

            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new \RuntimeException(
                    'No event found for dummy review.'
                );
            }

            $eventId = (int) $event['id'];

        } else {

            $stmt = $pdo->query(
                "SELECT id
                FROM bars
                ORDER BY RAND()
                LIMIT 1"
            );

            $bar = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bar) {
                throw new \RuntimeException(
                    'No bar found for dummy review.'
                );
            }

            $barId = (int) $bar['id'];
        }

        $ratingFriendly = random_int(1, 5);
        $ratingProfessional = random_int(1, 5);
        $ratingOverall = random_int(1, 5);

        $comment = trim(
            LipsumGenerator::getWords(random_int(5, 13), false)
        );

        if ($comment === '') {
            $comment = null;
        }

        $reviewerType = 'guest';
        $reviewerId = null;
        $reviewerName = 'Dummy Guest - ' . trim(LipsumGenerator::getWords(1, false));

        $stmt = $pdo->prepare(
            'INSERT INTO reviews
            (
                reviewer_type,
                reviewer_id,
                target_user_id,
                event_id,
                bar_id,
                rating_friendly,
                rating_professional,
                rating_overall,
                comment,
                reviewer_name
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $reviewerType,
            $reviewerId,
            $targetUserId,
            $eventId,
            $barId,
            $ratingFriendly,
            $ratingProfessional,
            $ratingOverall,
            $comment,
            $reviewerName
        ]);


        return (int) $pdo->lastInsertId();
    }

    /* create dummy data */

    $bossIds = [];

    for ($i = 0; $i < $bossCount; $i++) {
        $bossIds[] = generateDummyBoss($pdo, $passwordHash);
    }

    for ($i = 0; $i < $memberCount; $i++) {
        generateDummyMember($pdo, $passwordHash);
    }

    if (count($bossIds) > 0) {

        for ($i = 0; $i < $eventCount; $i++) {
            $bossId = $bossIds[array_rand($bossIds)];

            generateDummyEvent(
                $bossId,
                $pdo
            );
        }

        for ($i = 0; $i < $postCount; $i++) {
            $bossId = $bossIds[array_rand($bossIds)];

            generateDummyPost(
                $bossId,
                $pdo
            );
        }

        for ($i = 0; $i < $barCount; $i++) {
            $bossId = $bossIds[array_rand($bossIds)];

            generateDummyBar(
                $bossId,
                $pdo
            );
        }

    } else {

        if (
            $eventCount > 0 ||
            $postCount > 0 ||
            $barCount > 0
        ) {
            throw new \RuntimeException(
                'At least one boss is required to generate events, posts or bars.'
            );
        }
    }

    for ($i = 0; $i < $reviewCount; $i++) {
        generateDummyReview($pdo);
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
