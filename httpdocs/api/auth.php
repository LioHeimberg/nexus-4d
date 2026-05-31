<?php

declare(strict_types=1);

function getTokenFromHeader(): string {
    $headers = apache_request_headers();
    $authorization = $headers['Authorization'] ?? '';
    
    if (preg_match('/Bearer\s+(\S+)/', $authorization, $matches)) {
        return $matches[1];
    }
    
    throw new Exception('No token provided');
}

function requireAuth(): array {
    require_once __DIR__ . '/../config/database.php';
    
    $token = getTokenFromHeader();
    
    $stmt = $pdo->prepare('SELECT s.user_id, u.role FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.token = ? AND s.expires_at > NOW()');
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    return $session;
}

function requireRole(string $role): array {
    $session = requireAuth();
    
    if ($session['role'] !== $role) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit();
    }
    
    return $session;
}

function requireRoles(array $roles): array {
    $session = requireAuth();
    
    if (!in_array($session['role'], $roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit();
    }
    
    return $session;
}
