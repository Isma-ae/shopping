<?php
include("../config/all.php");
header("Content-Type: application/json; charset=utf-8");

$fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
switch ($fn) {
    case 'login'  : login();  break;
    case 'logout' : logout(); break;
    default: break;
}

function login() {
    global $DB;
    $user_input = trim((string)($_POST["user_name"] ?? ''));
    $user_password = (string)($_POST["user_password"] ?? '');

    if ($user_input === "" || $user_password === "") {
        echo json_encode([
            "data"    => 'n',
            "title"   => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "กรุณากรอกบัญชีผู้ใช้หรืออีเมลและรหัสผ่าน",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $esc = $DB->Escape($user_input);
    $sql = "SELECT * FROM users WHERE user_id = '{$esc}' OR email = '{$esc}' LIMIT 1";
    $user = $DB->QueryObj($sql);

    if (empty($user)) {
        echo json_encode([
            "data"    => 'n',
            "title"   => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "ไม่พบบัญชีผู้ใช้หรืออีเมลนี้ในระบบ",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $row = $user[0];
    $hashed_password = (string)($row["user_password"] ?? "");

    if ($hashed_password === "") {
        echo json_encode([
            "data"    => 'n',
            "title"   => "บัญชีนี้เข้าสู่ระบบผ่าน SSO",
            "message" => "บัญชีนี้ลงทะเบียนผ่าน PSU Passport / Google / LINE กรุณากดปุ่มเข้าสู่ระบบด่วนด้านล่าง",
            "icon"    => "info"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (password_verify($user_password, $hashed_password)) {
        $_SESSION['user_info'] = [
            'id'         => $row["id"],
            'user_id'    => $row["user_id"],
            'name'       => $row["name"],
            'email'      => $row["email"],
            'role'       => $row["role"],
            'user_phone' => $row["user_phone"] ?? ''
        ];

        echo json_encode([
            "data"    => 'y',
            "title"   => "เข้าสู่ระบบสำเร็จ",
            "message" => "ยินดีต้อนรับ " . $row["name"],
            "icon"    => "success"
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "data"    => 'n',
            "title"   => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "รหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
    }
}

function logout() {
    session_destroy();
    echo json_encode([
        "data" => 't'
    ], JSON_UNESCAPED_UNICODE);
}
?>
