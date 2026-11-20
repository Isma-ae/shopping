<?php
/**
 * sso-authen/config/providers/facebook.php
 * ตั้งค่า Facebook Login (OAuth 2.0 + Graph API)
 *
 * วิธีตั้งค่า:
 * 1. ไปที่ https://developers.facebook.com/ -> Create App -> เลือก "Authenticate and request data from users with Facebook Login"
 * 2. นำ App ID และ App Secret (จากเมนู App settings -> Basic) มาใส่ด้านล่าง
 * 3. ในเมนู Facebook Login -> Settings -> ใส่ Valid OAuth Redirect URIs:
 *    https://shopping.oas.psu.ac.th/sso-authen/public/callback_facebook.php
 *    (หากทดสอบบนเครื่อง local ให้เพิ่ม http://localhost/shopping/sso-authen/public/callback_facebook.php ด้วย)
 * 4. ใส่ Privacy Policy URL และสลับแอปเป็น Live Mode เพื่อให้บุคคลทั่วไปล็อกอินได้
 */

require_once __DIR__ . '/../social_base.php';

return [
    'provider'      => 'facebook',
    'label'         => 'Facebook',
    'show_button'   => true, // เปลี่ยนเป็น false หากต้องการซ่อนปุ่มชั่วคราว

    'clientID'      => 'YOUR_FACEBOOK_APP_ID',
    'clientSecret'  => 'YOUR_FACEBOOK_APP_SECRET',

    'authorizeUrl'  => 'https://www.facebook.com/v20.0/dialog/oauth',
    'tokenUrl'      => 'https://graph.facebook.com/v20.0/oauth/access_token',
    'profileUrl'    => 'https://graph.facebook.com/v20.0/me',

    'redirectUri'   => SOCIAL_BASE_URL . '/public/callback_facebook.php',
    'scopes'        => ['public_profile', 'email'],
];
