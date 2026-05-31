<?php

declare(strict_types=1);

function getTokenFromHeader(): string {
    $authorization = '';
    
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $headersLower = array_change_key_case($headers, CASE_LOWER);
        $authorization = $headersLower['authorization'] ?? '';
    }
    
    if (empty($authorization) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    if (empty($authorization)) {
        throw new Exception('No token provided');
    }
    
    if (preg_match('/bearer\s+(.+)$/', $authorization, $matches)) {
        return trim($matches[1]);
    }
    
    throw new Exception('No token provided');
}

function getPDO() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db';
        $dbname = getenv('DB_NAME') ?: 'nexusData';
        $user = getenv('DB_USER') ?: 'dev';
        $pass = getenv('DB_PASS') ?: 'dev#123';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    
    return $pdo;
}

function requireAuth(): array {
    $token = getTokenFromHeader();
    $pdo = getPDO();
    
    $stmt = $pdo->prepare('SELECT s.user_id, u.role FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.token = ? AND s.expires_at > NOW()');
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        throw new Exception('Unauthorized');
    }
    
    return $session;
}

function requireRole(string $role): array {
    $session = requireAuth();
    
    if ($session['role'] !== $role) {
        throw new Exception('Forbidden');
    }
    
    return $session;
}

function requireRoles(array $roles): array {
    $session = requireAuth();
    
    if (!in_array($session['role'], $roles)) {
        throw new Exception('Forbidden');
    }
    
    return $session;
}
