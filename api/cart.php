<?php

    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"])) {
        echo json_encode([
            "data" => 'f',
        ]);
        exit();
    }

    $currentUser = $_SESSION['user_info'];
    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'select_cart'  : select_cart();  break;
        case 'change_qty'   : change_qty();   break;
        case 'delete_cart'  : delete_cart();  break;
        case 'empty_basket' : empty_basket(); break;
        case 'checkout'     : checkout();     break;
        default: break;
    }

    function select_cart() {
        global $DB;
        global $currentUser;
        $userId = $DB->Escape((string)$currentUser['id']);

        $update = $DB->QueryUpdate("cart", [
            "cart_status" => 1
        ], "user_id = '{$userId}'");

        if ($update) {
            $sql = "SELECT
                        cart.cart_id,
                        cart.user_id,
                        cart.variant_id,
                        cart.cart_qty,
                        cart.cart_type,
                        cart.cart_status,
                        variants.product_id,
                        MD5(product.product_id) AS product_hash,
                        product.product_name,
                        COALESCE(variants.variant_color, '') AS variant_color,
                        COALESCE(variants.variant_size, '') AS variant_size,
                        COALESCE(variants.size_detail, '') AS size_detail,
                        COALESCE(variants.variant_sale, 0) AS variant_sale,
                        COALESCE(variants.variant_stock, 0) AS variant_stock,
                        COALESCE(img.img_name, fallback_img.img_name, '') AS img_name,
                        GREATEST(0, COALESCE(variants.variant_stock, 0) - COALESCE(os.sum_order, 0)) AS remaining_stock,
                        (
                            SELECT COUNT(DISTINCT COALESCE(v2.variant_color, ''))
                            FROM variants v2
                            WHERE v2.product_id = variants.product_id
                        ) AS color_count,
                        (
                            SELECT COUNT(DISTINCT CONCAT(COALESCE(v3.variant_size, ''), '|', COALESCE(v3.size_detail, '')))
                            FROM variants v3
                            WHERE v3.product_id = variants.product_id
                              AND COALESCE(v3.variant_color, '') = COALESCE(variants.variant_color, '')
                        ) AS size_count
                    FROM cart
                    INNER JOIN variants
                        ON cart.variant_id = variants.variant_id
                    INNER JOIN product
                        ON variants.product_id = product.product_id
                    LEFT JOIN img
                        ON variants.img_id = img.img_id
                    LEFT JOIN (
                        SELECT product_id, MIN(img_name) AS img_name
                        FROM img
                        WHERE img_main = '1'
                        GROUP BY product_id
                    ) fallback_img
                        ON product.product_id = fallback_img.product_id
                    LEFT JOIN (
                        SELECT
                            od.variant_id,
                            COALESCE(SUM(od.qty), 0) AS sum_order
                        FROM order_detail od
                        INNER JOIN orders o
                            ON od.order_id = o.order_id
                        WHERE o.status_id IN (2, 3)
                        GROUP BY od.variant_id
                    ) os
                        ON variants.variant_id = os.variant_id
                    WHERE cart.user_id = '{$userId}'
                    ORDER BY cart.cart_id DESC";
            $return = [];
            $return["data"] = $DB->QueryObj($sql);
            echo json_encode($return, JSON_UNESCAPED_UNICODE);
        }
    }

    function change_qty() {
        global $DB;
        global $currentUser;
        $cartId = (int)($_POST["cart_id"] ?? 0);
        $cartQty = (int)($_POST["cart_qty"] ?? 1);
        $userId = $DB->Escape((string)$currentUser['id']);

        if ($cartId <= 0 || $cartQty < 1) {
            echo json_encode([
                "data" => 'n',
                "title" => "ข้อมูลไม่ถูกต้อง",
                "message" => "จำนวนสินค้าต้องมากกว่า 0",
                "icon" => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $sql = "SELECT cart.cart_type, variants.variant_id, variants.variant_stock
                FROM cart
                INNER JOIN variants
                    ON cart.variant_id = variants.variant_id
                WHERE cart.cart_id = {$cartId}
                  AND cart.user_id = '{$userId}'";
        $obj = $DB->QueryObj($sql);
        if (count($obj) === 0) {
            echo json_encode([
                "data" => 'n',
                "title" => "ไม่พบรายการสินค้า",
                "message" => "รายการสินค้านี้อาจถูกลบไปแล้ว",
                "icon" => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ((int)$obj[0]['cart_type'] !== 2) {
            $variantId = (int)$obj[0]['variant_id'];
            $sql2 = "SELECT IFNULL(SUM(qty), 0) AS sum_order
                FROM order_detail od
                JOIN orders o ON od.order_id = o.order_id
                WHERE od.variant_id = {$variantId}
                AND o.status_id IN (2, 3)";
            $obj2 = $DB->QueryObj($sql2);
            $stock = (int)$obj[0]["variant_stock"] - (int)($obj2[0]["sum_order"] ?? 0);
            if ($cartQty > $stock) {
                echo json_encode([
                    "data" => 'n',
                    "title" => "ไม่สามารถเพิ่มได้",
                    "message" => "สินค้าคงเหลือมีเพียง " . max(0, $stock) . " ชิ้น",
                    "icon" => "error"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
        }

        $update = $DB->QueryUpdate('cart', [
            'cart_qty' => $cartQty
        ], "cart_id = {$cartId} AND user_id = '{$userId}'");
        if ($update) {
            echo json_encode([
                "data" => 't',
                "title" => "สำเร็จ",
                "message" => "แก้ไขรายการสินค้าสำเร็จ",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_cart() {
        global $DB;
        global $currentUser;
        $cartId = (int)($_POST["cart_id"] ?? 0);
        $userId = $DB->Escape((string)$currentUser['id']);
        $delete = $DB->QueryDelete('cart', "cart_id = {$cartId} AND user_id = '{$userId}'");
        if ($delete) {
            echo json_encode([
                "data" => 't',
                "title" => "สำเร็จ",
                "message" => "ลบรายการสินค้าสำเร็จ",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function empty_basket() {
        global $DB;
        global $currentUser;
        $userId = $DB->Escape((string)$currentUser['id']);
        $delete = $DB->QueryDelete('cart', "user_id = '{$userId}'");
        if ($delete) {
            echo json_encode([
                "data" => 't',
                "title" => "ลบสินค้าแล้ว",
                "message" => "ลบสินค้าทั้งหมดออกจากตะกร้าของคุณแล้ว",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function checkout() {
        global $DB;
        global $currentUser;
        $userId = $DB->Escape((string)$currentUser['id']);
        $cart_ids = $_POST['cart_ids'] ?? [];
        $idsArr = array_filter(array_map('intval', is_array($cart_ids) ? $cart_ids : []), function ($v) {
            return $v > 0;
        });

        if (count($idsArr) === 0) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเลือกสินค้าที่ต้องการสั่งซื้อ'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $ids = implode(',', $idsArr);
        $sql = "UPDATE cart SET cart_status = 2 WHERE cart_id IN ({$ids}) AND user_id = '{$userId}'";
        if ($DB->Query($sql)) {
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB error'], JSON_UNESCAPED_UNICODE);
        }
    }
