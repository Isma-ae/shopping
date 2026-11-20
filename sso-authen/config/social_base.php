<?php
/**
 * sso-authen/config/social_base.php
 * ตัวช่วยตั้งค่าพื้นฐานสำหรับ Social OAuth (LINE / Facebook)
 */

if (!session_id()) {
    session_start();
}

if (!defined('APP_BASE_PATH')) {
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = in_array($httpHost, ['localhost', '127.0.0.1'], true)
        || strpos($httpHost, 'localhost:') === 0
        || strpos($httpHost, '127.0.0.1:') === 0;

    if ($isLocal) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        define('APP_BASE_PATH', $protocol . $httpHost . '/shopping');
    } else {
        define('APP_BASE_PATH', 'https://shopping.oas.psu.ac.th');
    }
}

if (!defined('SOCIAL_BASE_URL')) {
    define('SOCIAL_BASE_URL', rtrim(APP_BASE_PATH, '/') . '/sso-authen');
}

require_once __DIR__ . '/../src/SocialOAuthHandler.php';
