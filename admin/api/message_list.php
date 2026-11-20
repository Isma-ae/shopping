<?php

    include("../../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        echo json_encode([
            "status"  => "error",
            "title"   => "ไม่มีสิทธิ์เข้าถึง",
            "message" => "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
            "icon"    => "error",
            "data"    => []
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'change_read'    : change_read();    break;
        case 'select_message' : select_message(); break;
        case 'delete_message' : delete_message(); break;
        default: break;
    }

    function change_read() {
        global $DB;
        $message_id = (int)($_POST["message_id"] ?? 0);
        $read = $DB->QueryUpdate("message", [
            "read" => 1
        ], "message_id = {$message_id}");
        if ($read) {
            echo json_encode([
                "status" => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "อัปเดตสถานะการอ่านไม่สำเร็จ",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function select_message() {
        global $DB;
        $filter = $_POST["filter"] ?? "all";
        $where = "";
        if ($filter === "unread") {
            $where = "WHERE `read` = 0";
        } elseif ($filter === "unreplied") {
            $where = "WHERE (message_reply IS NULL OR TRIM(message_reply) = '')";
        } elseif ($filter === "replied") {
            $where = "WHERE (message_reply IS NOT NULL AND TRIM(message_reply) != '')";
        }

        $rows = $DB->QueryObj("SELECT * FROM message {$where} ORDER BY message_id DESC");

        $countsRow = $DB->QueryObj("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN `read` = 0 THEN 1 ELSE 0 END) AS unread,
                SUM(CASE WHEN message_reply IS NULL OR TRIM(message_reply) = '' THEN 1 ELSE 0 END) AS unreplied,
                SUM(CASE WHEN message_reply IS NOT NULL AND TRIM(message_reply) != '' THEN 1 ELSE 0 END) AS replied
            FROM message
        ");
        $c = $countsRow[0] ?? [];

        echo json_encode([
            "data"   => $rows,
            "counts" => [
                "total"     => (int)($c["total"] ?? 0),
                "unread"    => (int)($c["unread"] ?? 0),
                "unreplied" => (int)($c["unreplied"] ?? 0),
                "replied"   => (int)($c["replied"] ?? 0)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    function delete_message() {
        global $DB;
        $message_id = (int)($_POST["message_id"] ?? 0);
        if ($message_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสข้อความที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        $deleted = $DB->QueryDelete("message", "message_id = {$message_id}");
        if ($deleted) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบข้อความเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบข้อความได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
