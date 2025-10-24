<?php
include("../config/all.php");

$fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
switch ($fn) {
    case 'login'    : login();  break;
    case 'logout'   : logout(); break;
    default:  break;
}

function login() {
    global $DB;
    $user_id = trim($_POST["user_name"] ?? '');
    $user_password = trim($_POST["user_password"] ?? '');
    if ($user_id == "" || $user_password == "") {
        echo json_encode([
            "data" => 'n',
            "title" => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "กรุณากรอกอีเมลและรหัสผ่าน",
            "icon" => "error"
        ]);
        exit();
    }
    $sql = "SELECT * FROM users WHERE user_id = '".$user_id."' LIMIT 1";
    $user = $DB->QueryObj($sql);
    if (sizeof($user) == 0) {
        echo json_encode([
            "data" => 'n',
            "title" => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "ไม่พบอีเมลนี้ในระบบ",
            "icon" => "error"
        ]);
        exit();
    }
    $row = $user[0];
    $hashed_password = $row["user_password"];
    if (password_verify($user_password, $hashed_password)) {
        $internalUser = [
            'id' => $row["id"],
            'user_id' => $row["user_id"],
            'name' => $row["name"],
            'email' => $row["email"],
            'role' => $row["role"]
        ];
        $_SESSION['user_info'] = $internalUser;

        echo json_encode([
            "data" => 'y',
            "title" => "เข้าสู่ระบบสำเร็จ",
            "message" => "ยินดีต้อนรับ " . $row["name"],
            "icon" => "success"
        ]);
    } else {
        echo json_encode([
            "data" => 'n',
            "title" => "เข้าสู่ระบบไม่สำเร็จ",
            "message" => "รหัสผ่านไม่ถูกต้อง",
            "icon" => "error"
        ]);
    }
}

function logout() {
    session_destroy();
    echo json_encode([
        "data" => 't'
    ]);
}
?>