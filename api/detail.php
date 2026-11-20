<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case "select_size":  select_size();  break;
        case "select_price": select_price(); break;
        default: break;
    }

    function select_size() {
        global $DB;
        $product_id = $DB->Escape($_POST["product_id"] ?? "");
        $variant_color = $DB->Escape($_POST["variant_color"] ?? "");

        $sql = "SELECT
                    COALESCE(variant_size, '') AS variant_size,
                    COALESCE(size_detail, '') AS size_detail,
                    size_order
                FROM variants
                WHERE MD5(product_id) = '{$product_id}'
                    AND COALESCE(variant_color, '') = '{$variant_color}'
                GROUP BY COALESCE(variant_size, ''), COALESCE(size_detail, ''), size_order
                ORDER BY size_order ASC, variant_id ASC";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_price() {
        global $DB;
        $product_id = $DB->Escape($_POST["product_id"] ?? "");
        $has_color = isset($_POST["variant_color"]);
        $variant_color = $DB->Escape($_POST["variant_color"] ?? "");
        $has_size = isset($_POST["variant_size"]) && $_POST["variant_size"] !== "";
        $variant_size = $DB->Escape($_POST["variant_size"] ?? "");

        $conds = ["MD5(v.product_id) = '{$product_id}'"];
        if ($has_color) {
            $conds[] = "COALESCE(v.variant_color, '') = '{$variant_color}'";
        }
        if ($has_size) {
            $conds[] = "COALESCE(v.variant_size, '') = '{$variant_size}'";
        }

        $whereSql = implode(" AND ", $conds);
        $sql = "SELECT
                    v.variant_id,
                    COALESCE(v.variant_sale, 0) AS variant_sale,
                    COALESCE(v.variant_stock, 0) AS variant_stock,
                    COALESCE(p.reserve_id, 2) AS reserve_id,
                    (COALESCE(v.variant_stock, 0) - COALESCE(SUM(CASE WHEN o.status_id IN (2, 3) THEN od.qty ELSE 0 END), 0)) AS remaining_stock
                FROM variants v
                INNER JOIN product p
                    ON v.product_id = p.product_id
                LEFT JOIN order_detail od
                    ON v.variant_id = od.variant_id
                LEFT JOIN orders o
                    ON od.order_id = o.order_id
                WHERE {$whereSql}
                GROUP BY v.variant_id, v.variant_sale, v.variant_stock, p.reserve_id
                ORDER BY v.variant_id ASC
                LIMIT 1";
        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }
?>
