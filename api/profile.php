<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"])) {
        echo json_encode([
            "status"  => "error",
            "title"   => "กรุณาเข้าสู่ระบบ",
            "message" => "เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $currentUser = $_SESSION["user_info"];
    $fn = $_POST["fn"] ?? "";

    switch ($fn) {
        case "update_profile":  update_profile();  break;
        case "change_password": change_password(); break;
        default:
            echo json_encode([
                "status"  => "error",
                "title"   => "คำขอไม่ถูกต้อง",
                "message" => "ไม่พบคำสั่งที่ระบุ",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
            break;
    }

    function update_profile() {
        global $DB, $currentUser;
        $userId = (int)($currentUser["id"] ?? 0);
        $name = trim((string)($_POST["name"] ?? ""));
        $phone = trim((string)($_POST["user_phone"] ?? ""));

        if ($name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกชื่อ-นามสกุล",
                "message" => "ชื่อ-นามสกุลไม่สามารถเว้นว่างได้",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($phone !== "" && !preg_match('/^[0-9\-\s\+]{8,20}$/', $phone)) {
            echo json_encode([
                "status"  => "error",
                "title"   => "เบอร์โทรศัพท์ไม่ถูกต้อง",
                "message" => "กรุณากรอกหมายเลขโทรศัพท์ให้ถูกต้อง (ตัวเลข 8-20 หลัก)",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $updated = $DB->QueryUpdate("users", [
            "name"       => $name,
            "user_phone" => $phone
        ], "id = {$userId}");

        if ($updated) {
            $_SESSION["user_info"]["name"] = $name;
            $_SESSION["user_info"]["user_phone"] = $phone;

            echo json_encode([
                "status"  => "success",
                "title"   => "บันทึกสำเร็จ",
                "message" => "อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว",
                "icon"    => "success",
                "user"    => [
                    "name"       => $name,
                    "user_phone" => $phone
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "เกิดข้อผิดพลาด",
                "message" => "ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function change_password() {
        global $DB, $currentUser;
        $userId = (int)($currentUser["id"] ?? 0);
        $oldPass = (string)($_POST["old_password"] ?? "");
        $newPass = (string)($_POST["new_password"] ?? "");
        $confirmPass = (string)($_POST["confirm_password"] ?? "");

        if ($oldPass === "" || $newPass === "" || $confirmPass === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกข้อมูลให้ครบ",
                "message" => "กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่ให้ครบถ้วน",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if (strlen($newPass) < 6) {
            echo json_encode([
                "status"  => "error",
                "title"   => "รหัสผ่านสั้นเกินไป",
                "message" => "รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($newPass !== $confirmPass) {
            echo json_encode([
                "status"  => "error",
                "title"   => "รหัสผ่านไม่ตรงกัน",
                "message" => "รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $rows = $DB->QueryObj("SELECT user_password FROM users WHERE id = {$userId} LIMIT 1");
        if (count($rows) === 0 || empty($rows[0]["user_password"])) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถเปลี่ยนรหัสผ่านได้",
                "message" => "บัญชีนี้เข้าสู่ระบบผ่าน PSU Passport หรือ Google",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if (!password_verify($oldPass, $rows[0]["user_password"])) {
            echo json_encode([
                "status"  => "error",
                "title"   => "รหัสผ่านเดิมไม่ถูกต้อง",
                "message" => "กรุณาตรวจสอบรหัสผ่านปัจจุบันอีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $updated = $DB->QueryUpdate("users", [
            "user_password" => $hashed
        ], "id = {$userId}");

        if ($updated) {
            echo json_encode([
                "status"  => "success",
                "title"   => "เปลี่ยนรหัสผ่านสำเร็จ",
                "message" => "อัปเดตรหัสผ่านใหม่เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "เกิดข้อผิดพลาด",
                "message" => "ไม่สามารถเปลี่ยนรหัสผ่านได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
