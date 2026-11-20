<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case "check_login":     check_login();     break;
        case "add_cart":        add_cart();        break;
        case "reserve_product": reserve_product(); break;
        case "buy_product":     buy_product();     break;
        default: break;
    }

    /**
     * Find matching variant safely, supporting single-color, single-size, and optionless products.
     */
    function findMatchingVariant($DB, $productHash, $rawColor, $rawSize) {
        $pid = $DB->Escape($productHash);
        $colorVal = trim((string)$rawColor);
        $sizeVal = trim((string)$rawSize);

        $conds = ["MD5(v.product_id) = '{$pid}'"];
        if ($colorVal !== "") {
            $conds[] = "COALESCE(v.variant_color, '') = '" . $DB->Escape($colorVal) . "'";
        }
        if ($sizeVal !== "") {
            $conds[] = "COALESCE(v.variant_size, '') = '" . $DB->Escape($sizeVal) . "'";
        }

        $sql = "SELECT
                    v.variant_id,
                    MD5(v.variant_id) AS variant_hash,
                    COALESCE(v.variant_color, '') AS variant_color,
                    COALESCE(v.variant_size, '') AS variant_size,
                    COALESCE(v.variant_stock, 0) AS variant_stock,
                    COALESCE(p.reserve_id, 2) AS reserve_id
                FROM variants v
                INNER JOIN product p
                    ON v.product_id = p.product_id
                WHERE " . implode(" AND ", $conds) . "
                ORDER BY v.variant_id ASC
                LIMIT 1";
        $rows = $DB->QueryObj($sql);
        if (count($rows) > 0) {
            return $rows[0];
        }

        // Fallback: if product has exactly one variant overall, return it cleanly
        $fallback = $DB->QueryObj("
            SELECT
                v.variant_id,
                MD5(v.variant_id) AS variant_hash,
                COALESCE(v.variant_color, '') AS variant_color,
                COALESCE(v.variant_size, '') AS variant_size,
                COALESCE(v.variant_stock, 0) AS variant_stock,
                COALESCE(p.reserve_id, 2) AS reserve_id
            FROM variants v
            INNER JOIN product p
                ON v.product_id = p.product_id
            WHERE MD5(v.product_id) = '{$pid}'
            ORDER BY v.variant_id ASC
            LIMIT 2
        ");
        return count($fallback) === 1 ? $fallback[0] : null;
    }

    function getRemainingStock($DB, $variantId, $variantStock) {
        $vid = (int)$variantId;
        $ordered = $DB->QueryObj("
            SELECT IFNULL(SUM(od.qty), 0) AS sum_order
            FROM order_detail od
            INNER JOIN orders o ON od.order_id = o.order_id
            WHERE od.variant_id = {$vid}
              AND o.status_id IN (2, 3)
        ");
        $sumOrder = isset($ordered[0]["sum_order"]) ? (int)$ordered[0]["sum_order"] : 0;
        return (int)$variantStock - $sumOrder;
    }

    function formatVariantLabel($color, $size) {
        $parts = [];
        if (trim((string)$color) !== "") {
            $parts[] = "สี" . trim((string)$color);
        }
        if (trim((string)$size) !== "") {
            $parts[] = "ขนาด " . trim((string)$size);
        }
        return implode(" ", $parts);
    }

    function check_login() {
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data" => "f",
                "title" => "กรุณาเข้าสู่ระบบ",
                "message" => "เข้าสู่ระบบก่อนหยิบสินค้าลงตะกร้าหรือสั่งซื้อสินค้า",
                "icon" => "warning"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "data" => "t",
                "title" => "เข้าสู่ระบบ",
                "message" => "หยิบสินค้าลงตะกร้าหรือสั่งซื้อสินค้าได้ทันที",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function add_cart() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data" => "f",
                "title" => "กรุณาเข้าสู่ระบบ",
                "message" => "เข้าสู่ระบบก่อนหยิบสินค้าลงตะกร้าหรือสั่งซื้อสินค้า",
                "icon" => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $currentUser = $_SESSION["user_info"];
        $userId = (int)($currentUser["id"] ?? 0);
        $productHash = $_POST["product_id"] ?? "";
        $productName = trim((string)($_POST["product_name"] ?? "สินค้า"));
        $rawColor = $_POST["variant_color"] ?? "";
        $rawSize = $_POST["variant_size"] ?? "";
        $cartQty = max(1, (int)($_POST["cart_qty"] ?? 1));

        $variant = findMatchingVariant($DB, $productHash, $rawColor, $rawSize);
        if (!$variant) {
            echo json_encode([
                "data" => "n",
                "title" => $productName,
                "message" => "ไม่พบตัวเลือกสินค้าที่ระบุ กรุณาลองใหม่อีกครั้ง",
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $variantId = (int)$variant["variant_id"];
        $stock = getRemainingStock($DB, $variantId, $variant["variant_stock"]);
        $label = formatVariantLabel($variant["variant_color"], $variant["variant_size"]);

        if ($stock < $cartQty) {
            $stockText = $stock < 1 ? "หมดแล้ว" : "เหลือเพียง " . $stock . " ชิ้น";
            $prefix = $label !== "" ? $label . " " : "สินค้านี้";
            if ((int)$variant["reserve_id"] === 2) {
                echo json_encode([
                    "data" => "n",
                    "title" => $productName,
                    "message" => trim($prefix . $stockText),
                    "icon" => "error"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                echo json_encode([
                    "data" => "r",
                    "title" => $productName,
                    "message" => trim($prefix . $stockText . " ต้องการจองใช่หรือไม่"),
                    "icon" => "warning"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
        }

        $objCheck = $DB->QueryObj("
            SELECT cart_id
            FROM cart
            WHERE variant_id = {$variantId}
              AND user_id = {$userId}
              AND cart_type = 1
        ");
        if (count($objCheck) > 0) {
            $cartId = (int)$objCheck[0]["cart_id"];
            $insert = $DB->Query("UPDATE cart SET cart_qty = cart_qty + {$cartQty} WHERE cart_id = {$cartId}");
        } else {
            $insert = $DB->QueryInsert("cart", [
                "cart_id" => $DB->QueryMaxId("cart", "cart_id"),
                "variant_id" => $variantId,
                "cart_qty" => $cartQty,
                "user_id" => $userId,
                "cart_type" => 1,
                "cart_status" => 1
            ]);
        }

        if ($insert) {
            echo json_encode([
                "data" => "t",
                "title" => $productName,
                "message" => "ถูกเพิ่มลงตะกร้าแล้ว",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "data" => "n",
                "title" => "ไม่สำเร็จ",
                "message" => "เพิ่มลงตะกร้าไม่สำเร็จเนื่องจาก " . $DB->Error(),
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function reserve_product() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data" => "f",
                "title" => "กรุณาเข้าสู่ระบบ",
                "message" => "กรุณาเข้าสู่ระบบก่อนจองสินค้า",
                "icon" => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $currentUser = $_SESSION["user_info"];
        $userId = (int)($currentUser["id"] ?? 0);
        $productHash = $_POST["product_id"] ?? "";
        $productName = trim((string)($_POST["product_name"] ?? "สินค้า"));
        $rawColor = $_POST["variant_color"] ?? "";
        $rawSize = $_POST["variant_size"] ?? "";
        $cartQty = max(1, (int)($_POST["cart_qty"] ?? 1));

        $variant = findMatchingVariant($DB, $productHash, $rawColor, $rawSize);
        if (!$variant) {
            echo json_encode([
                "data" => "n",
                "title" => "ไม่สำเร็จ",
                "message" => "ไม่พบตัวเลือกสินค้าที่ต้องการจอง",
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $variantId = (int)$variant["variant_id"];
        $objCheck = $DB->QueryObj("
            SELECT cart_id
            FROM cart
            WHERE variant_id = {$variantId}
              AND user_id = {$userId}
              AND cart_type = 2
        ");
        if (count($objCheck) > 0) {
            $cartId = (int)$objCheck[0]["cart_id"];
            $insert = $DB->Query("UPDATE cart SET cart_qty = cart_qty + {$cartQty} WHERE cart_id = {$cartId}");
        } else {
            $insert = $DB->QueryInsert("cart", [
                "cart_id" => $DB->QueryMaxId("cart", "cart_id"),
                "variant_id" => $variantId,
                "cart_qty" => $cartQty,
                "user_id" => $userId,
                "cart_type" => 2,
                "cart_status" => 1
            ]);
        }

        if ($insert) {
            echo json_encode([
                "data" => "t",
                "title" => $productName,
                "message" => "ได้ถูกสั่งจองเรียบร้อยแล้ว",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "data" => "n",
                "title" => "ไม่สำเร็จ",
                "message" => "สั่งจองไม่สำเร็จเนื่องจาก " . $DB->Error(),
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function buy_product() {
        global $DB;
        $productHash = $_POST["product_id"] ?? "";
        $productName = trim((string)($_POST["product_name"] ?? "สินค้า"));
        $rawColor = $_POST["variant_color"] ?? "";
        $rawSize = $_POST["variant_size"] ?? "";
        $cartQty = max(1, (int)($_POST["cart_qty"] ?? 1));

        $variant = findMatchingVariant($DB, $productHash, $rawColor, $rawSize);
        if (!$variant) {
            echo json_encode([
                "data" => "n",
                "title" => $productName,
                "message" => "ไม่พบตัวเลือกสินค้าที่ระบุ กรุณาลองใหม่อีกครั้ง",
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $variantId = (int)$variant["variant_id"];
        $stock = getRemainingStock($DB, $variantId, $variant["variant_stock"]);
        $label = formatVariantLabel($variant["variant_color"], $variant["variant_size"]);

        if ($stock < $cartQty) {
            $stockText = $stock < 1 ? "หมดแล้ว" : "เหลือเพียง " . $stock . " ชิ้น";
            $prefix = $label !== "" ? $label . " " : "สินค้านี้";
            echo json_encode([
                "data" => "n",
                "title" => $productName,
                "message" => trim($prefix . $stockText),
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        echo json_encode([
            "data" => "t",
            "variant_id" => $variant["variant_hash"]
        ], JSON_UNESCAPED_UNICODE);
    }
?>
