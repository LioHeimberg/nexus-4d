<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

echo "Starting dummy data generation...\n\n";

try {
    $pdo->beginTransaction();
    
    $passwordHash = password_hash('member123', PASSWORD_DEFAULT);
    
    $bossData = [
        'email' => 'boss@nexus4d.ch',
        'password_hash' => $passwordHash,
        'role' => 'boss',
        'first_name' => 'Michael',
        'last_name' => 'Bossman'
    ];
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(array_values($bossData));
    $bossId = $pdo->lastInsertId();
    echo "✓ Created boss account (ID: $bossId)\n";
    
    $members = [
        ['Lukas', 'Müller', 'lukas.mueller@jugendteam.ch'],
        ['Sarah', 'Meier', 'sarah.meier@jugendteam.ch'],
        ['Jonas', 'Schmidt', 'jonas.schmidt@jugendteam.ch'],
        ['Fabienne', 'Wolf', 'fabienne.wolf@jugendteam.ch'],
        ['Niklas', 'Becker', 'niklas.becker@jugendteam.ch'],
        ['Lina', 'Fischer', 'lina.fischer@jugendteam.ch'],
        ['Tim', 'Weber', 'tim.weber@jugendteam.ch'],
        ['Marlene', 'Meyer', 'marlene.meyer@jugendteam.ch'],
        ['David', 'Hoppe', 'david.hoppe@jugendteam.ch'],
        ['Anna', 'Richter', 'anna.richter@jugendteam.ch'],
        ['Max', 'Klein', 'max.klein@jugendteam.ch'],
        ['Julia', 'Schneider', 'julia.schneider@jugendteam.ch']
    ];
    
    $memberIds = [];
    foreach ($members as $index => $member) {
        $userData = [
            'email' => $member[2],
            'password_hash' => $passwordHash,
            'role' => 'member',
            'first_name' => $member[0],
            'last_name' => $member[1]
        ];
        
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(array_values($userData));
        $memberId = $pdo->lastInsertId();
        $memberIds[] = $memberId;
        echo "✓ Created member #".($index + 1)." (ID: $memberId) - {$member[0]} {$member[1]}\n";
    }
    
    echo "\n✓ Created " . count($members) . " members\n\n";
    
    $barsData = [
        ['Rümli Wünnewil', 'Hauptplatz 1, Wünnewil'],
        ['Rümli Flamatt', 'Bahnhofstrasse 5, Flamatt'],
        ['Rümli Schmitten', 'Dorfplatz 2, Schmitten'],
        ['Rümli Ueberstorf', 'Hauptstrasse 12, Ueberstorf']
    ];
    
    foreach ($barsData as $bar) {
        $stmt = $pdo->prepare('INSERT INTO bars (name, location, boss_id) VALUES (?, ?, ?)');
        $stmt->execute([$bar[0], $bar[1], $bossId]);
        echo "✓ Created bar: {$bar[0]}\n";
    }
    
    echo "\n✓ Created " . count($barsData) . " bars\n\n";
    
    $eventsData = [
        [
            'title' => ' Sommerfest 2025',
            'description' => 'Jährliches Sommerfest mit Musik, Essen und vielen Aktivitäten draussen im Freien. Alle Jugendlichen sind willkommen!',
            'event_date' => '2025-07-15 14:00:00',
            'location' => 'Jugendraum Wünnewil'
        ],
        [
            'title' => 'Poolparty in Flamatt',
            'description' => 'Heisse Tage! Kommt zum Schwimmen und Spielen am Pool. Getränke und Snacks sind verfügbar.',
            'event_date' => '2025-08-02 15:00:00',
            'location' => 'Schulhausplatz Flamatt'
        ],
        [
            'title' => 'Laser Tag Challenge',
            'description' => 'Spannende Laser Tag Session für alle Jugendlichen. Teams bilden und um den Sieg kämpfen!',
            'event_date' => '2025-08-20 16:00:00',
            'location' => 'Sporthalle Schmitten'
        ],
        [
            'title' => 'Kino Abend im Jugendraum',
            'description' => 'Gemütlicher Kinoabend mit Popcorn, Snacks und den neuesten Blockbustern.',
            'event_date' => '2025-09-05 18:00:00',
            'location' => 'Jugendraum Ueberstorf'
        ],
        [
            'title' => 'Camping Weekend',
            'description' => 'Zelte aufschlagen, Feuer machen und die Natur geniessen. Ein unvergessliches Wochenende!',
            'event_date' => '2025-09-20 10:00:00',
            'location' => 'Campingplatz bei Wünnewil'
        ],
        [
            'title' => 'Weihnachtsfeier 2025',
            'description' => 'Festlicher Abend mit Musik, Geschenken und gemütlichen Stunden vor Weihnachten.',
            'event_date' => '2025-12-18 17:00:00',
            'location' => 'Jugendraum Wünnewil'
        ]
    ];
    
    $eventIds = [];
    foreach ($eventsData as $event) {
        $stmt = $pdo->prepare('INSERT INTO events (title, description, event_date, location, boss_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $event['title'],
            $event['description'],
            $event['event_date'],
            $event['location'],
            $bossId
        ]);
        $eventId = $pdo->lastInsertId();
        $eventIds[] = $eventId;
        echo "✓ Created event: {$event['title']} (ID: $eventId)\n";
    }
    
    echo "\n✓ Created " . count($eventsData) . " events\n\n";
    
    $postsData = [
        [
            'title' => 'Willkommen zum neuen Jahr!',
            'content' => 'Wir freuen uns auf ein tolles Jahr mit vielen neuen Aktivitäten, Events und spannenden Herausforderungen. Bleibt dran und seid dabei!'
        ],
        [
            'title' => 'Sommerprogramm 2025',
            'content' => 'Unser Sommerprogramm ist fertig! Von Poolpartys bis zu Laser Tag - es ist für jeden etwas dabei. Schaut euch die Liste an und meldet euch an!'
        ],
        [
            'title' => 'Neuer Öffnungszeitenplan',
            'content' => 'Ab nächsten Monat ändern sich unsere Öffnungszeiten leicht. Die neuen Zeiten sind dienstags bis freitags von 14-19 Uhr. Kommt vorbei!'
        ],
        [
            'title' => 'Dankeschön an alle Helfer',
            'content' => 'Das letzte Event war ein voller Erfolg! Vielen Dank an alle Jugendlichen, die geholfen haben, das Fest zu organisieren und durchzuführen.'
        ],
        [
            'title' => 'Neue Mitglieder gesucht!',
            'content' => 'Wir suchen neue Jugendliche für unser Team! Wenn du gerne mitmachst, verantwortungsvoll arbeiten willst und Spaß an der Jugendarbeit hast, melde dich!'
        ],
        [
            'title' => 'Herzlich willkommen!',
            'content' => 'Ein herzliches Willkommen an alle neuen Jugendlichen! Wir freuen uns darauf, euch kennenzulernen und gemeinsam tolle Sachen zu unternehmen.'
        ]
    ];
    
    $postIds = [];
    foreach ($postsData as $post) {
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, boss_id) VALUES (?, ?, ?)');
        $stmt->execute([$post['title'], $post['content'], $bossId]);
        $postId = $pdo->lastInsertId();
        $postIds[] = $postId;
        echo "✓ Created post: {$post['title']} (ID: $postId)\n";
    }
    
    echo "\n✓ Created " . count($postsData) . " posts\n\n";
    
    $barsStmt = $pdo->prepare('SELECT id FROM bars ORDER BY RAND() LIMIT 2');
    $barsStmt->execute();
    $bars = $barsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $reviewCount = 0;
    foreach ($memberIds as $memberId) {
        $randomBars = $bars;
        shuffle($randomBars);
        $barsToReview = array_slice($randomBars, 0, 2);
        
        foreach ($barsToReview as $barId) {
            $reviewData = [
                'reviewer_type' => 'member',
                'reviewer_id' => $memberId,
                'target_user_id' => $memberId,
                'event_id' => null,
                'bar_id' => $barId,
                'rating_friendly' => rand(3, 5),
                'rating_professional' => rand(3, 5),
                'rating_overall' => rand(3, 5),
                'comment' => 'Toller Ort, tolles Team!',
                'created_at' => date('Y-m-d H:i:s', rand(strtotime('-3 months'), strtotime('now')))
            ];
            
            $stmt = $pdo->prepare('INSERT INTO reviews (reviewer_type, reviewer_id, target_user_id, event_id, bar_id, rating_friendly, rating_professional, rating_overall, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute(array_values($reviewData));
            $reviewCount++;
        }
    }
    
    echo "✓ Created member reviews for bars\n";
    
    foreach ($eventIds as $eventId) {
        $eventStmt = $pdo->prepare('SELECT event_date FROM events WHERE id = ?');
        $eventStmt->execute([$eventId]);
        $eventDate = $eventStmt->fetchColumn();
        
        foreach (array_slice($memberIds, 0, rand(5, 10)) as $memberId) {
            $statuses = ['yes', 'maybe', 'no'];
            $status = $statuses[array_rand($statuses)];
            
            $partStmt = $pdo->prepare('INSERT INTO event_participation (event_id, member_id, status, voted_at) VALUES (?, ?, ?, ?)');
            $partStmt->execute([$eventId, $memberId, $status, date('Y-m-d H:i:s', strtotime($eventDate) - rand(86400, 604800))]);
        }
        
        echo "✓ Added participation for event ID: $eventId\n";
    }
    
    echo "\n✓ Created event participations\n";
    
    foreach ($memberIds as $memberId) {
        $randomEvents = $eventIds;
        shuffle($randomEvents);
        $eventsToReview = array_slice($randomEvents, 0, rand(1, 3));
        
        foreach ($eventsToReview as $eventId) {
            $reviewData = [
                'reviewer_type' => 'member',
                'reviewer_id' => $memberId,
                'target_user_id' => $memberId,
                'event_id' => $eventId,
                'bar_id' => null,
                'rating_friendly' => rand(3, 5),
                'rating_professional' => rand(3, 5),
                'rating_overall' => rand(3, 5),
                'comment' => 'Hatte viel Spass beim Event!',
                'created_at' => date('Y-m-d H:i:s', rand(strtotime('-2 months'), strtotime('now')))
            ];
            
            $stmt = $pdo->prepare('INSERT INTO reviews (reviewer_type, reviewer_id, target_user_id, event_id, bar_id, rating_friendly, rating_professional, rating_overall, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute(array_values($reviewData));
            $reviewCount++;
        }
    }
    
    echo "✓ Created member reviews for events\n";
    
    foreach (range(1, 25) as $i) {
        $randomMemberId = $memberIds[array_rand($memberIds)];
        $randomBarId = $bars[array_rand($bars)];
        
        $reviewData = [
            'reviewer_type' => 'guest',
            'reviewer_id' => null,
            'target_user_id' => $randomMemberId,
            'event_id' => null,
            'bar_id' => $randomBarId,
            'rating_friendly' => rand(3, 5),
            'rating_professional' => rand(3, 5),
            'rating_overall' => rand(3, 5),
            'comment' => 'Freundlicher Service, hat Spass gemacht!',
            'created_at' => date('Y-m-d H:i:s', rand(strtotime('-3 months'), strtotime('now')))
        ];
        
        $stmt = $pdo->prepare('INSERT INTO reviews (reviewer_type, reviewer_id, target_user_id, event_id, bar_id, rating_friendly, rating_professional, rating_overall, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(array_values($reviewData));
        $reviewCount++;
    }
    
    echo "✓ Created guest reviews\n";
    
    $pdo->commit();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ Dummy data generation complete!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "Summary:\n";
    echo "- 1 Boss account created\n";
    echo "- " . count($members) . " Members created\n";
    echo "- " . count($barsData) . " Bars created\n";
    echo "- " . count($eventsData) . " Events created\n";
    echo "- " . count($postsData) . " Posts created\n";
    echo "- $reviewCount Reviews created\n\n";
    
    echo "Default password for all accounts: 'member123'\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
