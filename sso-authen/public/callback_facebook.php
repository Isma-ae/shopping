<?php
/**
 * sso-authen/public/callback_facebook.php
 * รับ Callback หลังจากผู้ใช้ยืนยันตัวตนกับ Facebook
 */

$providerConfig = require_once __DIR__ . '/../config/providers/facebook.php';
require_once __DIR__ . '/helpers.php';

use SsoAuthen\SocialOAuthHandler;

try {
    $handler = new SocialOAuthHandler($providerConfig);
    $internalUser = $handler->handleCallback();

    if (!empty($internalUser)) {
        header('Location: ' . rtrim(APP_BASE_PATH, '/') . '/index.php');
        exit;
    }

    throw new Exception('ไม่สามารถสร้างข้อมูลเซสชันผู้ใช้ได้');
} catch (Exception $e) {
    render_alert_and_redirect(
        'ไม่สามารถเข้าสู่ระบบด้วย Facebook ได้',
        $e->getMessage(),
        'error',
        rtrim(APP_BASE_PATH, '/') . '/login/'
    );
}
