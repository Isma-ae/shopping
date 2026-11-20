<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    function respondJson($status, $title, $message, $icon, $refreshCaptcha = true) {
        echo json_encode([
            "status"          => $status,
            "title"           => $title,
            "message"         => $message,
            "icon"            => $icon,
            "refresh_captcha" => $refreshCaptcha
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // 1. Honeypot check (bots auto-fill hidden text fields)
    $honeypot = trim((string)($_POST["website"] ?? ""));
    if ($honeypot !== "") {
        unset($_SESSION["contact_captcha"]);
        respondJson("error", "ไม่สามารถส่งข้อความได้", "ตรวจพบคำขอที่ผิดปกติ กรุณาลองใหม่อีกครั้ง", "error", true);
    }

    // 2. Basic input validation
    $rawEmail = trim((string)($_POST["email"] ?? ""));
    $rawMsg = trim((string)($_POST["msg"] ?? ""));
    $rawCaptcha = strtoupper(trim((string)($_POST["captcha"] ?? "")));

    if ($rawEmail === "" || !filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
        respondJson("error", "อีเมลไม่ถูกต้อง", "กรุณากรอกที่อยู่อีเมลที่ถูกต้อง", "warning", false);
    }

    if ($rawMsg === "") {
        respondJson("error", "กรุณากรอกข้อความ", "กรุณาระบุข้อความที่ต้องการติดต่อเรา", "warning", false);
    }

    if (mb_strlen($rawMsg, "UTF-8") > 2000) {
        respondJson("error", "ข้อความยาวเกินไป", "กรุณาระบุข้อความไม่เกิน 2,000 ตัวอักษร", "warning", false);
    }

    if ($rawCaptcha === "") {
        respondJson("error", "กรุณากรอกรหัสยืนยัน", "กรุณากรอกตัวอักษร CAPTCHA ที่เห็นในภาพ", "warning", false);
    }

    // 3. Server-side CAPTCHA verification (one-time use)
    $captchaData = $_SESSION["contact_captcha"] ?? null;
    unset($_SESSION["contact_captcha"]); // Always invalidate after 1 attempt to prevent replay attacks

    if (!is_array($captchaData) || empty($captchaData["code"]) || time() > (int)($captchaData["expires"] ?? 0)) {
        respondJson("error", "รหัสยืนยันหมดอายุ", "รหัส CAPTCHA หมดอายุแล้ว กรุณากรอกรหัสใหม่", "warning", true);
    }

    $expectedCode = strtoupper((string)$captchaData["code"]);
    if (!hash_equals($expectedCode, $rawCaptcha)) {
        respondJson("error", "รหัสยืนยันไม่ถูกต้อง", "กรุณากรอกตัวอักษร CAPTCHA ใหม่อีกครั้ง", "error", true);
    }

    // 4. Rate limiting (cooldown 15s + max 5 messages per 10 minutes per session)
    $now = time();
    $history = $_SESSION["contact_rate_limit"] ?? [];
    $history = array_values(array_filter($history, function ($ts) use ($now) {
        return ($now - (int)$ts) < 600;
    }));

    if (count($history) > 0 && ($now - (int)end($history)) < 15) {
        respondJson("error", "ส่งข้อความถี่เกินไป", "กรุณารอสักครู่ก่อนส่งข้อความใหม่อีกครั้ง", "warning", true);
    }

    if (count($history) >= 5) {
        respondJson("error", "ส่งข้อความเกินกำหนด", "คุณส่งข้อความหลายครั้งแล้ว กรุณารอสัก 10 นาทีแล้วลองใหม่", "warning", true);
    }

    // 5. Save message to database
    $message_id = $DB->QueryMaxId('message', 'message_id');
    $email = $DB->Escape($rawEmail);
    $msg = $DB->Escape($rawMsg);
    $send_date = date('Y-m-d H:i:s');
    $insert = $DB->QueryInsert('message', [
        'message_id' => $message_id,
        'email'      => $email,
        'msg'        => $msg,
        'send_date'  => $send_date,
        'read'       => 0
    ]);

    if ($insert) {
        $history[] = $now;
        $_SESSION["contact_rate_limit"] = $history;
        respondJson("success", "สำเร็จ", "ข้อความของคุณถูกส่งไปยังผู้ดูแลระบบแล้ว", "success", true);
    } else {
        respondJson("error", "ไม่สำเร็จ", "ไม่สามารถส่งข้อความได้ กรุณาลองใหม่อีกครั้ง", "error", true);
    }
