<?php
/**
 * sso-authen/src/SocialOAuthHandler.php
 * จัดการกระบวนการ OAuth 2.0 สำหรับ LINE Login และ Facebook Login ด้วย PHP cURL
 */

namespace SsoAuthen;

use Exception;

class SocialOAuthHandler
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * ตรวจสอบว่าตั้งค่า Client ID / Client Secret เรียบร้อยแล้วหรือไม่
     */
    private function assertConfigured(): void
    {
        $clientId = trim((string)($this->config['clientID'] ?? ''));
        $clientSecret = trim((string)($this->config['clientSecret'] ?? ''));
        $label = $this->config['label'] ?? strtoupper($this->config['provider'] ?? 'Social');

        if (
            $clientId === '' || $clientSecret === ''
            || strpos($clientId, 'YOUR_') === 0
            || strpos($clientSecret, 'YOUR_') === 0
        ) {
            throw new Exception("ระบบยังไม่ได้ตั้งค่า {$label} Login (กรุณาใส่ Client ID และ Client Secret ในไฟล์ config/providers/{$this->config['provider']}.php)");
        }
    }

    /**
     * เริ่มต้นการเข้าสู่ระบบโดยส่งผู้ใช้ไปยังหน้ายินยอมของ LINE หรือ Facebook
     */
    public function login(): void
    {
        $this->assertConfigured();

        $provider = $this->config['provider'];
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state_' . $provider] = $state;

        $params = [
            'response_type' => 'code',
            'client_id'     => $this->config['clientID'],
            'redirect_uri'  => $this->config['redirectUri'],
            'state'         => $state,
        ];

        if ($provider === 'line') {
            $params['scope'] = implode(' ', $this->config['scopes'] ?? ['profile', 'openid', 'email']);
            $nonce = bin2hex(random_bytes(12));
            $_SESSION['oauth_nonce_line'] = $nonce;
            $params['nonce'] = $nonce;
        } else {
            $params['scope'] = implode(',', $this->config['scopes'] ?? ['public_profile', 'email']);
        }

        $authUrl = $this->config['authorizeUrl'] . '?' . http_build_query($params);
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * รับ Callback ตรวจสอบ State แลก Token ดึงข้อมูลผู้ใช้ และสร้าง Session
     */
    public function handleCallback(): array
    {
        $this->assertConfigured();

        $provider = $this->config['provider'];
        $label = $this->config['label'] ?? strtoupper($provider);

        // กรณีผู้ใช้กดยกเลิกที่หน้าล็อกอินของ LINE / Facebook
        if (!empty($_GET['error'])) {
            $errCode = (string)$_GET['error'];
            if ($errCode === 'access_denied') {
                throw new Exception("คุณได้ยกเลิกการเข้าสู่ระบบด้วย {$label}");
            }
            $errDesc = (string)($_GET['error_description'] ?? $errCode);
            throw new Exception("การเข้าสู่ระบบด้วย {$label} ไม่สำเร็จ: {$errDesc}");
        }

        $code = (string)($_GET['code'] ?? '');
        $state = (string)($_GET['state'] ?? '');
        $expectedState = (string)($_SESSION['oauth_state_' . $provider] ?? '');
        unset($_SESSION['oauth_state_' . $provider]);

        if ($code === '' || $state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            throw new Exception('การยืนยันตัวตนล้มเหลว (State ไม่ถูกต้องหรือหมดอายุ) กรุณาลองใหม่อีกครั้ง');
        }

        if ($provider === 'line') {
            [$normalizedUser, $rawUserInfo] = $this->fetchLineUser($code);
        } elseif ($provider === 'facebook') {
            [$normalizedUser, $rawUserInfo] = $this->fetchFacebookUser($code);
        } else {
            throw new Exception("ไม่รองรับผู้ให้บริการ: {$provider}");
        }

        // เรียกใช้ user_handler.php ร่วมกับระบบ PSU / Google เดิม
        $userHandlerPath = __DIR__ . '/../user_handler.php';
        if (!file_exists($userHandlerPath)) {
            throw new Exception('ไม่พบไฟล์ user_handler.php');
        }
        require_once $userHandlerPath;

        if (!function_exists('findOrCreateUser')) {
            throw new Exception('ไม่พบฟังก์ชัน findOrCreateUser() ในไฟล์ user_handler.php');
        }

        $internalUser = findOrCreateUser($normalizedUser, (object)$rawUserInfo);

        $_SESSION['user_is_logged_in'] = true;
        $_SESSION['user_info'] = $internalUser;

        return $internalUser;
    }

    /**
     * แลก Authorization Code และดึงโปรไฟล์จาก LINE
     */
    private function fetchLineUser(string $code): array
    {
        $tokenData = $this->httpRequest('POST', $this->config['tokenUrl'], [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->config['redirectUri'],
            'client_id'     => $this->config['clientID'],
            'client_secret' => $this->config['clientSecret'],
        ]);

        if (empty($tokenData['access_token'])) {
            $msg = $tokenData['error_description'] ?? ($tokenData['error'] ?? 'ไม่ได้รับ Access Token จาก LINE');
            throw new Exception('เชื่อมต่อ LINE ไม่สำเร็จ: ' . $msg);
        }

        $profile = $this->httpRequest('GET', $this->config['profileUrl'], [], [
            'Authorization: Bearer ' . $tokenData['access_token'],
        ]);

        if (empty($profile['userId'])) {
            throw new Exception('ไม่สามารถดึงข้อมูลโปรไฟล์จาก LINE ได้');
        }

        // ดึง email จาก id_token (หาก LINE Channel ได้รับสิทธิ์ email และผู้ใช้ยินยอม)
        $email = null;
        if (!empty($tokenData['id_token']) && !empty($this->config['verifyUrl'])) {
            $verifyPayload = [
                'id_token'  => $tokenData['id_token'],
                'client_id' => $this->config['clientID'],
            ];
            if (!empty($_SESSION['oauth_nonce_line'])) {
                $verifyPayload['nonce'] = $_SESSION['oauth_nonce_line'];
                unset($_SESSION['oauth_nonce_line']);
            }
            try {
                $verified = $this->httpRequest('POST', $this->config['verifyUrl'], $verifyPayload);
                if (!empty($verified['email']) && filter_var($verified['email'], FILTER_VALIDATE_EMAIL)) {
                    $email = $verified['email'];
                }
            } catch (Exception $e) {
                // หาก verify id_token ไม่ผ่านหรือไม่มีสิทธิ์ email ยังให้ล็อกอินต่อด้วย userId + displayName ได้
                $email = null;
            }
        }

        $rawInfo = $profile;
        $rawInfo['email'] = $email;
        $rawInfo['provider'] = 'line';

        $normalized = [
            'id'         => 'line_' . $profile['userId'],
            'username'   => $email ?: ('line_' . $profile['userId']),
            'name'       => trim((string)($profile['displayName'] ?? '')) ?: 'LINE User',
            'firstName'  => null,
            'lastName'   => null,
            'email'      => $email,
            'department' => null,
            'provider'   => 'line',
        ];

        return [$normalized, $rawInfo];
    }

    /**
     * แลก Authorization Code และดึงโปรไฟล์จาก Facebook Graph API
     */
    private function fetchFacebookUser(string $code): array
    {
        $tokenUrl = $this->config['tokenUrl'] . '?' . http_build_query([
            'client_id'     => $this->config['clientID'],
            'redirect_uri'  => $this->config['redirectUri'],
            'client_secret' => $this->config['clientSecret'],
            'code'          => $code,
        ]);

        $tokenData = $this->httpRequest('GET', $tokenUrl);

        if (empty($tokenData['access_token'])) {
            $msg = $tokenData['error']['message'] ?? 'ไม่ได้รับ Access Token จาก Facebook';
            throw new Exception('เชื่อมต่อ Facebook ไม่สำเร็จ: ' . $msg);
        }

        $profileUrl = $this->config['profileUrl'] . '?' . http_build_query([
            'fields'       => 'id,name,email,first_name,last_name',
            'access_token' => $tokenData['access_token'],
        ]);

        $profile = $this->httpRequest('GET', $profileUrl);

        if (empty($profile['id'])) {
            $msg = $profile['error']['message'] ?? 'ไม่สามารถดึงข้อมูลโปรไฟล์จาก Facebook ได้';
            throw new Exception($msg);
        }

        $email = (!empty($profile['email']) && filter_var($profile['email'], FILTER_VALIDATE_EMAIL))
            ? $profile['email']
            : null;

        $rawInfo = $profile;
        $rawInfo['provider'] = 'facebook';

        $normalized = [
            'id'         => 'fb_' . $profile['id'],
            'username'   => $email ?: ('fb_' . $profile['id']),
            'name'       => trim((string)($profile['name'] ?? '')) ?: 'Facebook User',
            'firstName'  => $profile['first_name'] ?? null,
            'lastName'   => $profile['last_name'] ?? null,
            'email'      => $email,
            'department' => null,
            'provider'   => 'facebook',
        ];

        return [$normalized, $rawInfo];
    }

    /**
     * ส่งคำขอ HTTP ด้วย cURL และถอดรหัส JSON
     */
    private function httpRequest(string $method, string $url, array $postData = [], array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            throw new Exception('เซิร์ฟเวอร์ยังไม่ได้เปิดใช้งาน PHP cURL extension');
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ผู้ให้บริการได้: ' . $curlErr);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new Exception('รูปแบบข้อมูลตอบกลับจากผู้ให้บริการไม่ถูกต้อง');
        }

        return $decoded;
    }
}
