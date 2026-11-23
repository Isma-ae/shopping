<?php
/**
 * sso-authen/public/callback_line.php
 * รับ Callback หลังจากผู้ใช้ยืนยันตัวตนกับ LINE
 */

$providerConfig = require_once __DIR__ . '/../config/providers/line.php';
require_once __DIR__ . '/helpers.php';

use SsoAuthen\SocialOAuthHandler;

try {
    $handler = new SocialOAuthHandler($providerConfig);
    $internalUser = $handler->handleCallback();

    if (!empty($internalUser)) {
        header('Location: ' . rtrim(APP_BASE_PATH, '/') . '/');
        exit;
    }

    throw new Exception('ไม่สามารถสร้างข้อมูลเซสชันผู้ใช้ได้');
} catch (Exception $e) {
    render_alert_and_redirect(
        'ไม่สามารถเข้าสู่ระบบด้วย LINE ได้',
        $e->getMessage(),
        'error',
        rtrim(APP_BASE_PATH, '/') . '/login/'
    );
}
