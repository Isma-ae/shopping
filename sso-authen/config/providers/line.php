<?php
/**
 * sso-authen/config/providers/line.php
 * ตั้งค่า LINE Login (OAuth 2.0 / OpenID Connect)
 *
 * วิธีตั้งค่า:
 * 1. ไปที่ https://developers.line.biz/console/ -> สร้าง Provider และ Channel ประเภท "LINE Login"
 * 2. นำ Channel ID และ Channel secret มาใส่ใน clientID / clientSecret ด้านล่าง
 * 3. ในแท็บ LINE Login -> ใส่ Callback URL:
 *    https://shopping.oas.psu.ac.th/sso-authen/public/callback_line.php
 *    (หากทดสอบบนเครื่อง local ให้เพิ่ม http://localhost/shopping/sso-authen/public/callback_line.php ด้วย)
 * 4. กดเปลี่ยนสถานะ Channel จาก Developing เป็น Published เพื่อให้ผู้ใช้ทั่วไปล็อกอินได้
 */

require_once __DIR__ . '/../social_base.php';

return [
    'provider'      => 'line',
    'label'         => 'LINE',
    'show_button'   => true, // เปลี่ยนเป็น false หากต้องการซ่อนปุ่มชั่วคราว

    'clientID'      => '2012728703',
    'clientSecret'  => '97832d9386ba2d42feb8ff7d510e3303',

    'authorizeUrl'  => 'https://access.line.me/oauth2/v2.1/authorize',
    'tokenUrl'      => 'https://api.line.me/oauth2/v2.1/token',
    'profileUrl'    => 'https://api.line.me/v2/profile',
    'verifyUrl'     => 'https://api.line.me/oauth2/v2.1/verify',

    'redirectUri'   => SOCIAL_BASE_URL . '/public/callback_line.php',
    'scopes'        => ['profile', 'openid', 'email'],
];
