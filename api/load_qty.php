<?php

    include("../config/all.php");

    if (!isset($_SESSION["user_info"])) {
        echo json_encode([
            "data"=>'f',
        ]);
        exit();
    }

    $sql_order = "SELECT order_detail.variant_id, order_detail.qty
                FROM order_detail
                INNER JOIN orders
                    ON order_detail.order_id = orders.order_id
                WHERE order_type = 1
                    AND status_id = 1
                    AND create_date < (NOW() - INTERVAL 24 HOUR)";//order_type = 1 คือสั่งซื้อ 2 คือสั่งจอง
    $order_items = $DB->QueryObj($sql_order);
    if(sizeof($order_items) > 0) {
        foreach ($order_items as $item) {
            $DB->Query("UPDATE variants SET variant_stock = variant_stock+".$item['qty']." WHERE variant_id = ".$item['variant_id']."");
        }
    }
    $update = $DB->QueryUpdate("orders",[
        "status_id" => 4,
        "order_status"  => "ยกเลิก"
    ],"status_id = 1 AND create_date < (NOW() - INTERVAL 24 HOUR)");

    $currentUser = $_SESSION['user_info'];
    $sql = "SELECT SUM(cart_qty) AS cart_qty FROM cart WHERE user_id = '".htmlspecialchars($currentUser['id'])."'";
    $return["data"] = $DB->QueryObj($sql);
	echo json_encode( $return );