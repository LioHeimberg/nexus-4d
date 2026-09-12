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

    function generateUniqueDummyName(PDO $pdo, string $prefix): string
    {
        // Maximal 20 Versuche
        for ($i = 0; $i < 20; $i++) {

            $name = trim(LipsumGenerator::getWords(1, false));

            // Nur erstes Wort
            $name = explode(' ', $name)[0];

            // Sonderzeichen entfernen
            $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

            // Falls Lipsum nichts Brauchbares geliefert hat
            if ($name === '') {
                $name = 'user';
            }

            $name = strtolower($name);

            // Erste Variante ohne Nummer
            $email = $prefix . '-' . $name . '@example.com';

            // Prüfen, ob E-Mail bereits existiert
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM users WHERE email = ?'
            );

            $stmt->execute([$email]);

            if ((int) $stmt->fetchColumn() === 0) {
                return $name;
            }

            // Name existiert bereits -> nächste Variante
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


    /* create dummy data */

    for ($i = 0; $i < 3; $i++) {
        generateDummyBoss($pdo, $passwordHash);
    }

    $bossId = $pdo->lastInsertId();

    for ($i = 0; $i < 8; $i++) {
        generateDummyMember($pdo, $passwordHash);
    }

    for ($i = 0; $i < 3; $i++) {
        generateDummyEvent($bossId, $pdo);
    }

    for ($i = 0; $i < 5; $i++) {
        generateDummyPost($bossId, $pdo);
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
