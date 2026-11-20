<?php
// db_config.php
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($httpHost, ['localhost', '127.0.0.1'], true)
    || strpos($httpHost, 'localhost:') === 0
    || strpos($httpHost, '127.0.0.1:') === 0;

define('DB_HOST', 'localhost');
define('DB_NAME', 'shopping');
if ($isLocal) {
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_USER', 'shopping');
    define('DB_PASS', '**123shopping321**');
}
define('DB_CHARSET', 'utf8mb4');
