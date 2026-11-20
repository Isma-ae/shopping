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
        case 'select_product' : select_product(); break;
        case 'edit_product'   : edit_product();   break;
        case 'delete_product' : delete_product(); break;
        case 'change_status'  : change_status();  break;
        default: break;
    }

    function select_product() {
        global $DB;
        $sql = "SELECT
                    p.product_id,
                    p.product_name,
                    p.product_detail,
                    p.category_id,
                    p.reserve_id,
                    p.product_status,
                    COALESCE(c.category_name, 'ไม่ระบุประเภท') AS category_name,
                    i.img_name,
                    COALESCE(i.img_count, 0) AS img_count,
                    COALESCE(vs.variant_count, 0) AS variant_count,
                    COALESCE(vs.min_price, 0) AS min_price,
                    COALESCE(vs.max_price, 0) AS max_price,
                    COALESCE(vs.total_stock, 0) AS total_stock,
                    COALESCE(os.total_ordered, 0) AS total_ordered,
                    COALESCE(os.order_ref_count, 0) AS order_ref_count,
                    COALESCE(vs.total_stock, 0) - COALESCE(os.total_ordered, 0) AS remaining_stock
                FROM product p
                LEFT JOIN category c
                    ON p.category_id = c.category_id
                LEFT JOIN (
                    SELECT
                        product_id,
                        COALESCE(MAX(CASE WHEN img_main = '1' THEN img_name END), MIN(img_name)) AS img_name,
                        COUNT(*) AS img_count
                    FROM img
                    GROUP BY product_id
                ) i ON p.product_id = i.product_id
                LEFT JOIN (
                    SELECT
                        product_id,
                        COUNT(*) AS variant_count,
                        COALESCE(SUM(variant_stock), 0) AS total_stock,
                        MIN(CASE WHEN variant_sale > 0 THEN variant_sale ELSE variant_price END) AS min_price,
                        MAX(CASE WHEN variant_sale > 0 THEN variant_sale ELSE variant_price END) AS max_price
                    FROM variants
                    GROUP BY product_id
                ) vs ON p.product_id = vs.product_id
                LEFT JOIN (
                    SELECT
                        v.product_id,
                        COALESCE(SUM(CASE WHEN ord.status_id NOT IN (1, 4) THEN o.qty ELSE 0 END), 0) AS total_ordered,
                        COUNT(DISTINCT o.order_id) AS order_ref_count
                    FROM order_detail o
                    INNER JOIN variants v
                        ON o.variant_id = v.variant_id
                    INNER JOIN orders ord
                        ON o.order_id = ord.order_id
                    GROUP BY v.product_id
                ) os ON p.product_id = os.product_id
                ORDER BY p.product_id DESC";

        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function edit_product() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        $product_name = trim((string)($_POST["product_name"] ?? ""));
        $product_detail = (string)($_POST["product_detail"] ?? "");
        $category_id = (int)($_POST["category_id"] ?? 0);
        $reserve_id = (int)($_POST["reserve_id"] ?? 2);
        if (!in_array($reserve_id, [1, 2], true)) {
            $reserve_id = 2;
        }

        if ($product_id === "" || $product_name === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณากรอกข้อมูล",
                "message" => "กรุณากรอกชื่อสินค้าให้ครบถ้วน",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($category_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณาเลือกประเภทสินค้า",
                "message" => "กรุณาเลือกประเภทสินค้าก่อนบันทึก",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc = $DB->Escape($product_id);
        $update = $DB->QueryUpdate("product", [
            'product_name'   => $product_name,
            'product_detail' => $product_detail,
            'category_id'    => $category_id,
            'reserve_id'     => $reserve_id
        ], "product_id = '{$pidEsc}'");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถแก้ไขข้อมูลสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_product() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        if ($product_id === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสสินค้าที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc = $DB->Escape($product_id);

        // Protect historical orders referencing this product's variants
        $used = $DB->QueryObj("
            SELECT COUNT(DISTINCT o.order_id) AS cnt
            FROM order_detail o
            INNER JOIN variants v ON o.variant_id = v.variant_id
            WHERE v.product_id = '{$pidEsc}'
        ");
        $usedCount = (int)($used[0]["cnt"] ?? 0);
        if ($usedCount > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถลบสินค้าได้",
                "message" => "มีคำสั่งซื้อ {$usedCount} รายการอ้างอิงสินค้านี้อยู่ แนะนำให้เปลี่ยนสถานะเป็น \"รอวางจำหน่าย\" แทนการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $dir = "../../file/product/";
        $imgs = $DB->QueryObj("SELECT img_name FROM img WHERE product_id = '{$pidEsc}'");

        $delete = $DB->QueryDelete("product", "product_id = '{$pidEsc}'");
        if ($delete) {
            $DB->QueryDelete("variants", "product_id = '{$pidEsc}'");
            $DB->QueryDelete("img", "product_id = '{$pidEsc}'");
            foreach ($imgs as $value) {
                if (!empty($value["img_name"])) {
                    deleteFile($dir, $value["img_name"]);
                }
            }
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบรายการสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบรายการสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function change_status() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        $status_id = (int)($_POST["status_id"] ?? 0);

        if ($product_id === "" || !in_array($status_id, [1, 2], true)) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "รหัสสินค้าหรือสถานะไม่ถูกต้อง",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc = $DB->Escape($product_id);
        $update = $DB->QueryUpdate("product", [
            'product_status' => $status_id
        ], "product_id = '{$pidEsc}'");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "อัปเดตสถานะสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถอัปเดตสถานะสินค้าได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
?>
