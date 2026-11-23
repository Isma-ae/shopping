<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    function registerJson($data, $title, $message, $icon) {
        echo json_encode([
            "data"    => $data,
            "title"   => $title,
            "message" => $message,
            "icon"    => $icon
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'register': register(); break;
        default:
            registerJson('n', 'คำขอไม่ถูกต้อง', 'ไม่พบคำสั่งที่ระบุ', 'error');
            break;
    }

    function register() {
        global $DB;

        $name = trim((string)($_POST["name"] ?? ""));
        $email = strtolower(trim((string)($_POST["user_id"] ?? "")));
        $password = (string)($_POST["user_password"] ?? "");
        $confirmPassword = (string)($_POST["confirm_password"] ?? "");
        $phone = trim((string)($_POST["user_phone"] ?? ""));

        if ($name === "") {
            registerJson('n', 'กรุณากรอกชื่อ-นามสกุล', 'กรุณากรอกชื่อ-นามสกุลของคุณ', 'warning');
        }

        if (mb_strlen($name, "UTF-8") > 255) {
            registerJson('n', 'ชื่อ-นามสกุลยาวเกินไป', 'กรุณากรอกชื่อ-นามสกุลไม่เกิน 255 ตัวอักษร', 'warning');
        }

        if ($email === "") {
            registerJson('n', 'กรุณากรอกอีเมล', 'กรุณากรอกที่อยู่อีเมลสำหรับใช้เข้าสู่ระบบ', 'warning');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            registerJson('n', 'รูปแบบอีเมลไม่ถูกต้อง', 'กรุณาตรวจสอบรูปแบบอีเมลอีกครั้ง เช่น name@example.com', 'warning');
        }

        if ($phone !== "" && !preg_match('/^[0-9\-\s\+]{8,20}$/', $phone)) {
            registerJson('n', 'เบอร์โทรศัพท์ไม่ถูกต้อง', 'กรุณากรอกหมายเลขโทรศัพท์ให้ถูกต้อง (ตัวเลข 8-20 หลัก)', 'warning');
        }

        if ($password === "") {
            registerJson('n', 'กรุณากรอกรหัสผ่าน', 'กรุณากำหนดรหัสผ่านสำหรับบัญชีของคุณ', 'warning');
        }

        if (strlen($password) < 8) {
            registerJson('n', 'รหัสผ่านสั้นเกินไป', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร', 'warning');
        }

        if ($confirmPassword === "") {
            registerJson('n', 'กรุณายืนยันรหัสผ่าน', 'กรุณากรอกยืนยันรหัสผ่านอีกครั้ง', 'warning');
        }

        if ($password !== $confirmPassword) {
            registerJson('n', 'รหัสผ่านไม่ตรงกัน', 'กรุณากรอกรหัสผ่านและยืนยันรหัสผ่านให้ตรงกัน', 'warning');
        }

        // ตรวจสอบอีเมล / user_id ซ้ำแบบปลอดภัย (ป้องกัน SQL Injection และเช็กทั้ง 2 คอลัมน์)
        $emailEsc = $DB->Escape($email);
        $checkSql = "SELECT id, user_password FROM users WHERE user_id = '{$emailEsc}' OR email = '{$emailEsc}' LIMIT 1";
        $existing = $DB->QueryObj($checkSql);
        if (!empty($existing)) {
            $hasPassword = !empty($existing[0]["user_password"]);
            $dupMsg = $hasPassword
                ? "อีเมลนี้ถูกใช้งานแล้ว กรุณาเข้าสู่ระบบหรือใช้อีเมลอื่น"
                : "อีเมลนี้เคยลงทะเบียนผ่าน PSU Passport / Google / LINE แล้ว กรุณาเข้าสู่ระบบด่วนผ่านหน้าเข้าสู่ระบบ";
            registerJson('n', 'อีเมลนี้มีอยู่ในระบบแล้ว', $dupMsg, 'warning');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // บันทึกลงคอลัมน์จริงของตาราง users (name, email, user_id, user_password, user_phone, role)
        $insert = $DB->QueryInsert("users", [
            "user_id"       => $email,
            "email"         => $email,
            "name"          => $name,
            "user_password" => $hashedPassword,
            "user_phone"    => $phone,
            "role"          => "user"
        ]);

        if ($insert) {
            registerJson('y', 'สมัครสมาชิกสำเร็จ', 'สร้างบัญชีเรียบร้อยแล้ว กรุณาเข้าสู่ระบบเพื่อเริ่มใช้งาน', 'success');
        } else {
            registerJson('n', 'สมัครสมาชิกไม่สำเร็จ', 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง', 'error');
        }
    }
