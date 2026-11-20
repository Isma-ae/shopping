<?php
/**
 * sso-authen/public/login_line.php
 * จุดเริ่มต้นการเข้าสู่ระบบด้วย LINE
 */

$providerConfig = require_once __DIR__ . '/../config/providers/line.php';
require_once __DIR__ . '/helpers.php';

use SsoAuthen\SocialOAuthHandler;

try {
    $handler = new SocialOAuthHandler($providerConfig);
    $handler->login();
} catch (Exception $e) {
    render_alert_and_redirect(
        'ไม่สามารถเข้าสู่ระบบด้วย LINE ได้',
        $e->getMessage(),
        'error',
        rtrim(APP_BASE_PATH, '/') . '/login/'
    );
}
