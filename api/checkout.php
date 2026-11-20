<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'select_cart'  : select_order(); break;
        case 'create_order' : create_order(); break;
        case 'cancel_order' : cancel_order(); break;
        default: break;
    }

    function select_order() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data" => 'f',
            ]);
            exit();
        }
        $currentUser = $_SESSION['user_info'];
        $userId = $DB->Escape((string)$currentUser['id']);

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
                WHERE
                    cart.cart_status = 2
                    AND cart.user_id = '{$userId}'
                ORDER BY cart.cart_id DESC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function create_order() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data" => 'f',
            ]);
            exit();
        }
        $currentUser = $_SESSION['user_info'];
        $userId = $DB->Escape((string)$currentUser['id']);
        $receipt_id = $_POST["receipt_id"] ?? 0;
        $receipt_name = $_POST['receipt_name'] ?? '';
        $receipt_number = $_POST['receipt_number'] ?? '';
        $receipt_address = $_POST['receipt_address'] ?? '';
        $shipping_type = $_POST['shipping_type'] ?? 1;
        $shipping_name = $_POST['shipping_name'] ?? '';
        $shipping_phone = $_POST['shipping_phone'] ?? '';
        $shipping_department = $_POST['shipping_department'] ?? '';
        $address_id = $_POST['address_id'] ?? 0;
        $shipping_price = $_POST['shipping_price'] ?? 0;
        $total_price = $_POST['total_price'] ?? 0;

        // 1. สร้างหมายเลขออเดอร์
        $order_no = 'ORD' . date('YmdHis');

        // 2. เพิ่มข้อมูลในตาราง orders
        $order_id = $DB->QueryMaxId("orders", "order_id");
        $insert_order = $DB->QueryInsert("orders", [
            "order_id"            => $order_id,
            "order_no"            => $order_no,
            "user_id"             => htmlspecialchars((string)$currentUser['id']),
            "total_price"         => $total_price,
            "shipping_price"      => $shipping_price,
            "receipt_id"          => $receipt_id,
            "receipt_name"        => $receipt_name,
            "receipt_number"      => $receipt_number,
            "receipt_address"     => $receipt_address,
            "shipping_type"       => $shipping_type,
            "shipping_name"       => $shipping_name,
            "shipping_phone"      => $shipping_phone,
            "shipping_department" => $shipping_department,
            "address_id"          => $address_id,
            "order_status"        => "รอการชำระเงิน",
            "status_id"           => 1,
            "create_date"         => date("Y-m-d H:i:s")
        ]);

        // 3. ดึงข้อมูลจากตะกร้าของ user
        $sql_cart = "SELECT
                        cart.variant_id,
                        cart.cart_qty,
                        cart.cart_type,
                        variants.variant_sale,
                        variants.variant_color,
                        variants.variant_size,
                        product.product_name
                    FROM cart
                    INNER JOIN variants ON cart.variant_id = variants.variant_id
                    INNER JOIN product ON variants.product_id = product.product_id
                    WHERE cart.cart_status = 2 AND cart.user_id = '{$userId}'";
        $cart_items = $DB->QueryObj($sql_cart);

        // 4. วนลูปเพิ่มข้อมูลเข้า order_detail
        foreach ($cart_items as $item) {
            $detail_id = $DB->QueryMaxId("order_detail", "detail_id");
            $DB->QueryInsert("order_detail", [
                "detail_id"     => $detail_id,
                "order_id"      => $order_id,
                "variant_id"    => $item['variant_id'],
                "product_name"  => $item['product_name'],
                "variant_color" => $item['variant_color'],
                "variant_size"  => $item['variant_size'],
                "variant_price" => $item['variant_sale'],
                "qty"           => $item['cart_qty'],
                "order_type"    => $item['cart_type'],
                "total_price"   => $item['variant_sale'] * $item['cart_qty']
            ]);
        }

        $delete = $DB->QueryDelete("cart", "cart_status = 2 AND user_id = '{$userId}'");
        if ($delete) {
            echo json_encode([
                "status" => "success",
                "msg" => "สร้างคำสั่งซื้อสำเร็จ",
                "order_no" => $order_no
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function cancel_order() {
        global $DB;
        $orderId = (int)($_POST["order_id"] ?? 0);
        $update = $DB->QueryUpdate("orders", [
            "status_id" => 4,
            "order_status" => "ยกเลิก"
        ], "order_id = {$orderId}");
        if ($update) {
            echo json_encode([
                "status" => "success",
                "title" => "ยกเลิกสำเร็จ",
                "msg" => "รายการสั่งซื้อของคุณถูกยกเลิก",
                "icon" => "success"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
