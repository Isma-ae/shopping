<?php
/**
 * www/user_handler.php
 * เทมเพลตสำหรับจัดการข้อมูลผู้ใช้ในฐานข้อมูลของแอปพลิเคชัน ที่แต่ละแอปพลิเคชันต้องสร้างขึ้นเอง
 */

/**
 * ค้นหาผู้ใช้จากฐานข้อมูลด้วยข้อมูลจาก SSO หากไม่พบจะสร้างผู้ใช้ใหม่
 *
 * @param array $normalizedUser ข้อมูลผู้ใช้ที่ผ่านการแปลงชื่อฟิลด์เป็นมาตรฐานแล้ว
 * @param object $ssoUserInfo ข้อมูลผู้ใช้ดิบที่ได้จาก PSU SSO
 * @return array ข้อมูลผู้ใช้จากฐานข้อมูลภายในของแอปพลิเคชัน (รวม role)
 */
function findOrCreateUser(array $normalizedUser, object $ssoUserInfo): array
{
    // 1. เรียกใช้ไฟล์ตั้งค่าฐานข้อมูล
    require_once __DIR__ . '/db_config.php';

    // 2. ตั้งค่าการเชื่อมต่อ PDO
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // 3. เชื่อมต่อฐานข้อมูล
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // 4. เตรียมข้อมูลสำหรับค้นหาผู้ใช้ (รองรับทั้ง PSU / Google / LINE)
        $externalId = trim((string)($normalizedUser['id'] ?? ''));
        $rawEmail = trim((string)($normalizedUser['email'] ?? ''));
        $hasRealEmail = ($rawEmail !== '' && filter_var($rawEmail, FILTER_VALIDATE_EMAIL) !== false);
        $displayName = trim((string)($normalizedUser['name'] ?? ''));
        if ($displayName === '') {
            $displayName = $hasRealEmail ? $rawEmail : ($externalId ?: 'Member');
        }

        // ค้นหาจาก user_id ก่อน (สำคัญสำหรับ LINE ที่อาจไม่มีอีเมล)
        $user = false;
        if ($externalId !== '') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
            $stmt->execute([$externalId]);
            $user = $stmt->fetch();
        }

        // หากยังไม่พบ และมีอีเมลจริง ให้ค้นหาจาก email เพื่อเชื่อมกับบัญชีเดิม
        if (!$user && $hasRealEmail) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$rawEmail]);
            $user = $stmt->fetch();
        }

        if ($user) {
            // --- กรณีที่ 1: พบผู้ใช้ในระบบ (สมาชิกเก่า) ---
            $updatedUserId = !empty($user['user_id']) ? $user['user_id'] : $externalId;
            $updatedEmail = $user['email'];
            // หากบัญชีเดิมเป็นอีเมลจำลอง (@social.local) แล้วรอบนี้ได้อีเมลจริง ให้ลองอัปเดตเฉพาะเมื่อไม่ซ้ำใคร
            if ($hasRealEmail && substr((string)$updatedEmail, -13) === '@social.local') {
                $dupStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
                $dupStmt->execute([$rawEmail, $user['id']]);
                if (!$dupStmt->fetch()) {
                    $updatedEmail = $rawEmail;
                }
            }

            $updateStmt = $pdo->prepare(
                "UPDATE users SET name = ?, user_id = ?, email = ? WHERE id = ?"
            );
            $updateStmt->execute([
                $displayName,
                $updatedUserId,
                $updatedEmail,
                $user['id']
            ]);

            $user['name'] = $displayName;
            $user['user_id'] = $updatedUserId;
            $user['email'] = $updatedEmail;
            if (!isset($user['user_phone'])) {
                $user['user_phone'] = '';
            }

            return $user;
        } else {
            // --- กรณีที่ 2: ไม่พบผู้ใช้ในระบบ (สมาชิกใหม่) ---
            $defaultRole = 'user';

            // หากไม่มีอีเมลจาก LINE ให้สร้างอีเมลจำลองที่ไม่ซ้ำ เพื่อไม่ให้ติดข้อจำกัด NOT NULL / UNIQUE
            if ($hasRealEmail) {
                $emailToSave = $rawEmail;
            } else {
                $safeId = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', $externalId));
                if ($safeId === '') {
                    $safeId = 'social_' . bin2hex(random_bytes(8));
                }
                $emailToSave = $safeId . '@social.local';
            }

            $insertStmt = $pdo->prepare(
                "INSERT INTO users (user_id, email, name, role) VALUES (?, ?, ?, ?)"
            );
            $insertStmt->execute([
                $externalId,
                $emailToSave,
                $displayName,
                $defaultRole
            ]);

            $newUserId = $pdo->lastInsertId();

            return [
                'id' => $newUserId,
                'user_id' => $externalId,
                'email' => $emailToSave,
                'name' => $displayName,
                'role' => $defaultRole,
                'user_phone' => ''
            ];
        }
    } catch (\PDOException $e) {
        // หากมีปัญหาในการเชื่อมต่อหรือคิวรีฐานข้อมูล ให้หยุดการทำงานและแสดงข้อผิดพลาด
        // ในระบบจริง ควรจะบันทึก Log แทนการ die()
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}