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

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Nur Admins dürfen Dummy-Daten löschen
    $session = requireRoles(['admin']);

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        $input = [];
    }

    /*
     * Optional kann das Admin-Passwort wie beim Erstellen
     * zusätzlich verlangt werden.
     */
    if (!isset($input['AdminPass'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Admin password is required'
        ]);
        exit();
    }

    /*
     * Admin aus DB laden
     */
    $stmt = $pdo->prepare(
        'SELECT id, email, password_hash, role, first_name, last_name
         FROM users
         WHERE role = ?
         LIMIT 1'
    );

    $stmt->execute(['admin']);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$admin ||
        !password_verify($input['AdminPass'], $admin['password_hash'])
    ) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid Admin password'
        ]);

        exit();
    }

    /*
     * ---------------------------------------------------------
     * 1. Dummy-User ermitteln
     * ---------------------------------------------------------
     *
     * Diese beiden Patterns entsprechen exakt den Usern,
     * die dummydata_create.php erzeugt:
     *
     * boss-<name>@example.com
     * member-<name>@example.com
     *
     * Zusätzlich prüfen wir die Rolle, damit nicht versehentlich
     * ein anderer Account mit einer ähnlichen Mail gelöscht wird.
     */

    $stmt = $pdo->query(
        "SELECT id, role, email
         FROM users
         WHERE
             (
                 role = 'boss'
                 AND email LIKE 'boss-%@example.com'
             )
             OR
             (
                 role = 'member'
                 AND email LIKE 'member-%@example.com'
             )"
    );

    $dummyUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dummyBossIds = [];
    $dummyMemberIds = [];

    foreach ($dummyUsers as $dummyUser) {
        if ($dummyUser['role'] === 'boss') {
            $dummyBossIds[] = (int) $dummyUser['id'];
        } elseif ($dummyUser['role'] === 'member') {
            $dummyMemberIds[] = (int) $dummyUser['id'];
        }
    }

    /*
     * ---------------------------------------------------------
     * 2. IDs der Dummy-Events, Posts und Bars ermitteln
     * ---------------------------------------------------------
     *
     * Wichtig:
     * Wir suchen NICHT nach dem Titel/Namen.
     *
     * Ein Event/Post/Bar ist Dummy-Daten, wenn es von einem
     * Dummy-Boss erstellt wurde.
     */

    $dummyEventIds = [];
    $dummyPostIds = [];
    $dummyBarIds = [];

    if (count($dummyBossIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBossIds), '?'));

        // Dummy Events
        $stmt = $pdo->prepare(
            "SELECT id
             FROM events
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $dummyEventIds[] = (int) $id;
        }

        // Dummy Posts
        $stmt = $pdo->prepare(
            "SELECT id
             FROM posts
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $dummyPostIds[] = (int) $id;
        }

        // Dummy Bars
        $stmt = $pdo->prepare(
            "SELECT id
             FROM bars
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $dummyBarIds[] = (int) $id;
        }
    }

    /*
     * ---------------------------------------------------------
     * 3. Löschen in einer Transaktion
     * ---------------------------------------------------------
     */

    $pdo->beginTransaction();

    $deleted = [
        'reviews' => 0,
        'events' => 0,
        'posts' => 0,
        'bars' => 0,
        'bosses' => 0,
        'members' => 0
    ];

    /*
     * ---------------------------------------------------------
     * 3a. Dummy Reviews löschen
     * ---------------------------------------------------------
     *
     * Reviews aus dummydata_create.php sind eindeutig erkennbar
     * an:
     *
     * reviewer_type = guest
     * reviewer_name LIKE 'Dummy Guest - %'
     *
     * Zusätzlich löschen wir Reviews, die auf Dummy-Events
     * oder Dummy-Bars zeigen.
     *
     * Dadurch werden auch Dummy-Reviews entfernt, wenn sie
     * zufällig auf ein Dummy-Event oder eine Dummy-Bar zeigen.
     */

    $reviewConditions = [
        "(
            reviewer_type = 'guest'
            AND reviewer_name LIKE 'Dummy Guest - %'
        )"
    ];

    $reviewParams = [];

    if (count($dummyEventIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyEventIds), '?'));

        $reviewConditions[] = "event_id IN ($placeholders)";

        foreach ($dummyEventIds as $id) {
            $reviewParams[] = $id;
        }
    }

    if (count($dummyBarIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBarIds), '?'));

        $reviewConditions[] = "bar_id IN ($placeholders)";

        foreach ($dummyBarIds as $id) {
            $reviewParams[] = $id;
        }
    }

    $reviewSql =
        'DELETE FROM reviews WHERE ' .
        implode(' OR ', $reviewConditions);

    $stmt = $pdo->prepare($reviewSql);
    $stmt->execute($reviewParams);

    $deleted['reviews'] = $stmt->rowCount();

    /*
     * ---------------------------------------------------------
     * 3b. Dummy Events löschen
     * ---------------------------------------------------------
     */

    if (count($dummyBossIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBossIds), '?'));

        $stmt = $pdo->prepare(
            "DELETE FROM events
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        $deleted['events'] = $stmt->rowCount();
    }

    /*
     * ---------------------------------------------------------
     * 3c. Dummy Posts löschen
     * ---------------------------------------------------------
     */

    if (count($dummyBossIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBossIds), '?'));

        $stmt = $pdo->prepare(
            "DELETE FROM posts
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        $deleted['posts'] = $stmt->rowCount();
    }

    /*
     * ---------------------------------------------------------
     * 3d. Dummy Bars löschen
     * ---------------------------------------------------------
     */

    if (count($dummyBossIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBossIds), '?'));

        $stmt = $pdo->prepare(
            "DELETE FROM bars
             WHERE boss_id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        $deleted['bars'] = $stmt->rowCount();
    }

    /*
     * ---------------------------------------------------------
     * 3e. Dummy Bosses löschen
     * ---------------------------------------------------------
     *
     * Erst nachdem Events, Posts und Bars gelöscht wurden,
     * damit boss_id keine Foreign-Key-Probleme verursacht.
     */

    if (count($dummyBossIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyBossIds), '?'));

        $stmt = $pdo->prepare(
            "DELETE FROM users
             WHERE role = 'boss'
             AND email LIKE 'boss-%@example.com'
             AND id IN ($placeholders)"
        );

        $stmt->execute($dummyBossIds);

        $deleted['bosses'] = $stmt->rowCount();
    }

    /*
     * ---------------------------------------------------------
     * 3f. Dummy Members löschen
     * ---------------------------------------------------------
     */

    if (count($dummyMemberIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($dummyMemberIds), '?'));

        $stmt = $pdo->prepare(
            "DELETE FROM users
             WHERE role = 'member'
             AND email LIKE 'member-%@example.com'
             AND id IN ($placeholders)"
        );

        $stmt->execute($dummyMemberIds);

        $deleted['members'] = $stmt->rowCount();
    }

    /*
     * Alles erfolgreich
     */
    $pdo->commit();

    $totalDeleted = array_sum($deleted);

    echo json_encode([
        'success' => true,
        'message' => 'Dummy Data deleted successfully',
        'deleted' => $deleted,
        'total' => $totalDeleted
    ]);

} catch (Exception $e) {

    /*
     * Bei einem Fehler alles rückgängig machen.
     */
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

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