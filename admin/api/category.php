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
        case 'select_category' : select_category(); break;
        case 'add_category'    : add_category();    break;
        case 'edit_category'   : edit_category();   break;
        case 'delete_category' : delete_category(); break;

        case 'select_shipping' : select_shipping(); break;
        case 'add_shipping'    : add_shipping();    break;
        case 'edit_shipping'   : edit_shipping();   break;
        case 'delete_shipping' : delete_shipping(); break;
        default: break;
    }

    function select_category() {
        global $DB;
        $sql = "SELECT
                    c.category_id,
                    c.category_name,
                    COUNT(DISTINCT p.product_id) AS product_count
                FROM category c
                LEFT JOIN product p ON c.category_id = p.category_id
                GROUP BY c.category_id, c.category_name
                ORDER BY c.category_id ASC";
        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function add_category() {
        global $DB;
        $name = trim((string)($_POST["category_name"] ?? ""));
        if ($name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกข้อมูล",
                "message" => "กรุณากรอกชื่อประเภทสินค้า",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $nameEsc = $DB->Escape($name);
        $dup = $DB->QueryObj("SELECT category_id FROM category WHERE TRIM(category_name) = '{$nameEsc}' LIMIT 1");
        if (count($dup) > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ชื่อซ้ำในระบบ",
                "message" => "มีประเภทสินค้า \"{$name}\" อยู่ในระบบแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $category_id = $DB->QueryMaxId("category", "category_id");
        $insert = $DB->QueryInsert("category", [
            'category_id'   => $category_id,
            'category_name' => $name
        ]);
        if ($insert) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "เพิ่มประเภทสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถเพิ่มประเภทสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function edit_category() {
        global $DB;
        $category_id = (int)($_POST["category_id"] ?? 0);
        $name = trim((string)($_POST["category_name"] ?? ""));

        if ($category_id <= 0 || $name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ครบถ้วน",
                "message" => "กรุณากรอกชื่อประเภทสินค้าให้ถูกต้อง",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $nameEsc = $DB->Escape($name);
        $dup = $DB->QueryObj("SELECT category_id FROM category WHERE TRIM(category_name) = '{$nameEsc}' AND category_id != {$category_id} LIMIT 1");
        if (count($dup) > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ชื่อซ้ำในระบบ",
                "message" => "มีประเภทสินค้า \"{$name}\" อยู่ในระบบแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $update = $DB->QueryUpdate("category", [
            'category_name' => $name
        ], "category_id = {$category_id}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "แก้ไขประเภทสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถแก้ไขประเภทสินค้าได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_category() {
        global $DB;
        $category_id = (int)($_POST["category_id"] ?? 0);
        if ($category_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสประเภทสินค้าที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $used = $DB->QueryObj("SELECT COUNT(*) AS cnt FROM product WHERE category_id = {$category_id}");
        $usedCount = (int)($used[0]["cnt"] ?? 0);
        if ($usedCount > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถลบได้",
                "message" => "มีสินค้า {$usedCount} รายการกำลังใช้ประเภทสินค้านี้อยู่ กรุณาย้ายประเภทสินค้าก่อนลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $delete = $DB->QueryDelete("category", "category_id = {$category_id}");
        if ($delete) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบประเภทสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบประเภทสินค้าได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function select_shipping() {
        global $DB;
        $sql = "SELECT
                    t.transported_id,
                    t.transported_name,
                    COUNT(DISTINCT o.order_id) AS order_count
                FROM transported t
                LEFT JOIN orders o ON t.transported_id = o.transported_id
                GROUP BY t.transported_id, t.transported_name
                ORDER BY t.transported_id ASC";
        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function add_shipping() {
        global $DB;
        $name = trim((string)($_POST["transported_name"] ?? ""));
        if ($name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกข้อมูล",
                "message" => "กรุณากรอกชื่อบริษัทขนส่ง",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $nameEsc = $DB->Escape($name);
        $dup = $DB->QueryObj("SELECT transported_id FROM transported WHERE TRIM(transported_name) = '{$nameEsc}' LIMIT 1");
        if (count($dup) > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ชื่อซ้ำในระบบ",
                "message" => "มีบริษัทขนส่ง \"{$name}\" อยู่ในระบบแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $transported_id = $DB->QueryMaxId("transported", "transported_id");
        $insert = $DB->QueryInsert("transported", [
            'transported_id'   => $transported_id,
            'transported_name' => $name
        ]);
        if ($insert) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "เพิ่มบริษัทขนส่งเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถเพิ่มบริษัทขนส่งได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function edit_shipping() {
        global $DB;
        $transported_id = (int)($_POST["transported_id"] ?? 0);
        $name = trim((string)($_POST["transported_name"] ?? ""));

        if ($transported_id <= 0 || $name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ครบถ้วน",
                "message" => "กรุณากรอกชื่อบริษัทขนส่งให้ถูกต้อง",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $nameEsc = $DB->Escape($name);
        $dup = $DB->QueryObj("SELECT transported_id FROM transported WHERE TRIM(transported_name) = '{$nameEsc}' AND transported_id != {$transported_id} LIMIT 1");
        if (count($dup) > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ชื่อซ้ำในระบบ",
                "message" => "มีบริษัทขนส่ง \"{$name}\" อยู่ในระบบแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $update = $DB->QueryUpdate("transported", [
            'transported_name' => $name
        ], "transported_id = {$transported_id}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "แก้ไขบริษัทขนส่งเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถแก้ไขบริษัทขนส่งได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_shipping() {
        global $DB;
        $transported_id = (int)($_POST["transported_id"] ?? 0);
        if ($transported_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสบริษัทขนส่งที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $used = $DB->QueryObj("SELECT COUNT(*) AS cnt FROM orders WHERE transported_id = {$transported_id}");
        $usedCount = (int)($used[0]["cnt"] ?? 0);
        if ($usedCount > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถลบได้",
                "message" => "มีคำสั่งซื้อ {$usedCount} รายการอ้างอิงบริษัทขนส่งนี้อยู่ จึงไม่สามารถลบได้",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $delete = $DB->QueryDelete("transported", "transported_id = {$transported_id}");
        if ($delete) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบบริษัทขนส่งเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบบริษัทขนส่งได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
