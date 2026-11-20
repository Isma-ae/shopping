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
        case 'select_stock'   : select_stock();   break;
        case 'edit_variant'   : edit_variant();   break;
        case 'delete_variant' : delete_variant(); break;
        default: break;
    }

    function computeSizeOrder($sizeStr) {
        $key = strtoupper(trim((string)$sizeStr));
        $map = [
            "XS"  => "0",
            "SS"  => "0",
            "S"   => "1",
            "M"   => "2",
            "L"   => "3",
            "XL"  => "4",
            "XXL" => "5",
            "2XL" => "5",
            "3XL" => "6",
            "4XL" => "7",
            "5XL" => "8"
        ];
        return $map[$key] ?? "9";
    }

    function select_stock() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        if ($product_id === "") {
            echo json_encode(["data" => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pidEsc = $DB->Escape($product_id);
        $sql = "SELECT
                    v.variant_id,
                    v.product_api_id,
                    v.variant_style,
                    v.variant_color,
                    v.variant_size,
                    v.size_detail,
                    v.size_order,
                    v.variant_price,
                    v.variant_sale,
                    v.variant_stock,
                    v.variant_img,
                    v.product_id,
                    v.img_id,
                    i.img_name,
                    COALESCE(v.variant_stock, 0) AS total_stock,
                    COALESCE(os.total_ordered, 0) AS total_ordered,
                    COALESCE(os.order_ref_count, 0) AS order_ref_count,
                    COALESCE(v.variant_stock, 0) - COALESCE(os.total_ordered, 0) AS remaining_stock
                FROM variants v
                LEFT JOIN img i
                    ON v.img_id = i.img_id
                LEFT JOIN (
                    SELECT
                        od.variant_id,
                        COALESCE(SUM(CASE WHEN o.status_id NOT IN (1, 4) THEN od.qty ELSE 0 END), 0) AS total_ordered,
                        COUNT(DISTINCT od.order_id) AS order_ref_count
                    FROM order_detail od
                    INNER JOIN orders o
                        ON od.order_id = o.order_id
                    GROUP BY od.variant_id
                ) os ON v.variant_id = os.variant_id
                WHERE v.product_id = '{$pidEsc}'
                ORDER BY
                    v.variant_color ASC,
                    CAST(COALESCE(NULLIF(v.size_order, ''), '9') AS UNSIGNED) ASC,
                    v.variant_id ASC";

        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function edit_variant() {
        global $DB;
        $variant_id     = (int)($_POST["variant_id"] ?? 0);
        $product_id     = trim((string)($_POST["product_id"] ?? ""));
        $variant_style  = trim((string)($_POST["variant_style"] ?? ""));
        $variant_color  = trim((string)($_POST["variant_color"] ?? ""));
        $variant_size   = trim((string)($_POST["variant_size"] ?? ""));
        $size_detail    = trim((string)($_POST["size_detail"] ?? ""));
        $variant_price  = $_POST["variant_price"] ?? "";
        $variant_sale   = $_POST["variant_sale"] ?? "";
        $variant_stock  = $_POST["variant_stock"] ?? "";
        $img_id         = (int)($_POST["img_id"] ?? 0);
        $sync_color_img = isset($_POST["sync_color_img"]) && (string)$_POST["sync_color_img"] === "1";

        if ($variant_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสตัวเลือกสินค้าที่ต้องการแก้ไข",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if (!is_numeric($variant_price) || (float)$variant_price < 0 ||
            !is_numeric($variant_sale)  || (float)$variant_sale < 0 ||
            !is_numeric($variant_stock) || (int)$variant_stock < 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลตัวเลขไม่ถูกต้อง",
                "message" => "ราคาต้นทุน ราคาขาย และสต็อกสินค้าต้องเป็นตัวเลขที่ไม่ติดลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $priceVal = round((float)$variant_price, 2);
        $saleVal  = round((float)$variant_sale, 2);
        $stockVal = (int)$variant_stock;
        $sizeOrder = computeSizeOrder($variant_size);

        $update = $DB->QueryUpdate("variants", [
            "variant_style" => $variant_style,
            "variant_color" => $variant_color,
            "variant_size"  => $variant_size,
            "size_detail"   => $size_detail,
            "size_order"    => $sizeOrder,
            "variant_price" => $priceVal,
            "variant_sale"  => $saleVal,
            "variant_stock" => $stockVal,
            "img_id"        => $img_id
        ], "variant_id = {$variant_id}");

        if ($update) {
            if ($sync_color_img && $product_id !== "" && $variant_color !== "") {
                $pidEsc   = $DB->Escape($product_id);
                $colorEsc = $DB->Escape($variant_color);
                $DB->QueryUpdate("variants", [
                    "img_id" => $img_id
                ], "variant_color = '{$colorEsc}' AND product_id = '{$pidEsc}'");
            }

            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "แก้ไขข้อมูลตัวเลือกสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถแก้ไขข้อมูลตัวเลือกสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_variant() {
        global $DB;
        $variant_id = (int)($_POST["variant_id"] ?? 0);
        if ($variant_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสตัวเลือกสินค้าที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $used = $DB->QueryObj("
            SELECT COUNT(DISTINCT order_id) AS cnt
            FROM order_detail
            WHERE variant_id = {$variant_id}
        ");
        $usedCount = (int)($used[0]["cnt"] ?? 0);
        if ($usedCount > 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถลบตัวเลือกได้",
                "message" => "มีคำสั่งซื้อ {$usedCount} รายการอ้างอิงตัวเลือกสินค้านี้อยู่ แนะนำให้ปรับสต็อกตั้งต้นให้เท่ากับยอดขายแล้ว (คงเหลือ 0) แทนการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $delete = $DB->QueryDelete("variants", "variant_id = {$variant_id}");
        if ($delete) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบตัวเลือกสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบตัวเลือกสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
?>
