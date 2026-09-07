<?php
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'request_headers' => apache_request_headers(),
    'authorization' => $_GET['auth_test'] ?? 'not provided'
], JSON_PRETTY_PRINT);
