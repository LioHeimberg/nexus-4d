<?php

$env = getenv('ENV');

if ($env) {
    define('ENV', getenv('ENV'));
} else {
    define('ENV', 'prod');
}

require_once(__DIR__ . '/database.' . ENV . '.php');
