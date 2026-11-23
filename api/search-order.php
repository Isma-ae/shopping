<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $rawOrderNo = trim((string)($_POST["order_no"] ?? $_GET["order_no"] ?? ""));
    if ($rawOrderNo === "") {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $orderNoEsc = $DB->Escape($rawOrderNo);

    $sql = "
        SELECT
            orders.*,
            tb_address.name AS addr_name,
            tb_address.phone AS addr_phone,
            tb_address.address_at AS addr_detail,
            subdistricts.zip_code AS addr_zip,
            provinces.province_name_in_thai,
            districts.district_name_in_thai,
            subdistricts.subdistrict_name_in_thai,
            transported.transported_name
        FROM orders
        LEFT JOIN tb_address ON orders.address_id = tb_address.id
        LEFT JOIN provinces ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported ON orders.transported_id = transported.transported_id
        WHERE orders.order_no = '{$orderNoEsc}'
        ORDER BY orders.order_id DESC
    ";

    $orders = $DB->QueryObj($sql);

    foreach ($orders as &$order) {
        $oid = (int)($order["order_id"] ?? 0);
        $createTs = !empty($order["create_date"]) ? strtotime((string)$order["create_date"]) : false;
        $order["create_date_fmt"] = $createTs ? date("d/m/Y H:i", $createTs) : "-";
        $order["payment_deadline_fmt"] = $createTs ? date("d/m/Y H:i", $createTs + 86400) : "-";

        $sqlItems = "
            SELECT
                od.detail_id,
                od.order_id,
                od.variant_id,
                od.product_name,
                COALESCE(od.variant_color, '') AS variant_color,
                COALESCE(od.variant_size, '') AS variant_size,
                COALESCE(v.size_detail, '') AS size_detail,
                COALESCE(od.variant_price, 0) AS variant_price,
                COALESCE(od.qty, 1) AS qty,
                COALESCE(od.order_type, 1) AS order_type,
                COALESCE(od.total_price, 0) AS total_price,
                MD5(v.product_id) AS product_hash,
                COALESCE(img.img_name, fallback_img.img_name, '') AS img_name,
                (
                    SELECT COUNT(DISTINCT COALESCE(v2.variant_color, ''))
                    FROM variants v2
                    WHERE v2.product_id = v.product_id
                ) AS color_count,
                (
                    SELECT COUNT(DISTINCT CONCAT(COALESCE(v3.variant_size, ''), '|', COALESCE(v3.size_detail, '')))
                    FROM variants v3
                    WHERE v3.product_id = v.product_id
                      AND COALESCE(v3.variant_color, '') = COALESCE(v.variant_color, '')
                ) AS size_count
            FROM order_detail od
            LEFT JOIN variants v ON od.variant_id = v.variant_id
            LEFT JOIN img ON v.img_id = img.img_id
            LEFT JOIN (
                SELECT product_id, MIN(img_name) AS img_name
                FROM img
                WHERE img_main = '1'
                GROUP BY product_id
            ) fallback_img ON v.product_id = fallback_img.product_id
            WHERE od.order_id = {$oid}
            ORDER BY od.detail_id ASC
        ";
        $order["details"] = $DB->QueryObj($sqlItems);
    }
    unset($order);

    echo json_encode($orders, JSON_UNESCAPED_UNICODE);
