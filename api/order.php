<?php
    include("../config/all.php");
    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_order'	    : select_order(); 	    break;
        case 'create_order'	    : create_order(); 	    break;
        //case 'cancel_order'	    : cancel_order(); 	    break;
		default: break;
	}

    function select_order() {
        global $DB;
        $sql = "SELECT * 
            FROM variants
            INNER JOIN product
                ON variants.product_id = product.product_id
            LEFT JOIN img
                ON variants.img_id = img.img_id
            WHERE MD5(variant_id) = '".$_POST["variant_id"]."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }


    function create_order() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            $user_id = 0;
        } else {
            $currentUser = $_SESSION['user_info'];
            $user_id = htmlspecialchars($currentUser['id']);
        }
        $variant_id = $_POST["variant_id"];
        $cart_qty = $_POST["cart_qty"];
        $receipt_id = $_POST["receipt_id"];
        $receipt_name = $_POST['receipt_name'];
        $receipt_number = $_POST['receipt_number'];
        $receipt_address = $_POST['receipt_address'];
        $shipping_type = $_POST['shipping_type'];
        $shipping_name = $_POST['shipping_name'];
        $shipping_phone = $_POST['shipping_phone'];
        $shipping_department = $_POST['shipping_department'];
        $address_id = $_POST['address_id'];
        $shipping_price = $_POST['shipping_price'];
        $total_price = $_POST['total_price'];

        // 1️⃣ สร้างหมายเลขออเดอร์
        $order_no = 'ORD' . date('YmdHis');

        // 2️⃣ เพิ่มข้อมูลในตาราง order
        $order_id = $DB->QueryMaxId("orders", "order_id");
        $insert_order = $DB->QueryInsert("orders", [
            "order_id"              => $order_id,
            "order_no"              => $order_no,
            "user_id"               => $user_id,
            "total_price"           => $total_price,
            "shipping_price"        => $shipping_price,
            "receipt_id"            => $receipt_id,
            "receipt_name"          => $receipt_name,
            "receipt_number"        => $receipt_number,
            "receipt_address"       => $receipt_address,
            "shipping_type"         => $shipping_type,
            "shipping_name"         => $shipping_name,
            "shipping_phone"        => $shipping_phone,
            "shipping_department"   => $shipping_department,
            "address_id"            => $address_id,
            "order_status"          => "รอการชำระเงิน",
            "status_id"             => 1,
            "create_date"           => date("Y-m-d H:i:s")
        ]);

        // 3️⃣ ดึงข้อมูลจากตะกร้าของ user
        $sql_cart = "SELECT 
                        variant_id,
                        variant_sale,
                        variant_color,
                        variant_size,
                        product_name
                    FROM variants
                    INNER JOIN product ON variants.product_id = product.product_id
                    WHERE MD5(variant_id) = '".$variant_id."'";
        $item = $DB->QueryObj($sql_cart);

        // 4️⃣ วนลูปเพิ่มข้อมูลเข้า order_detail
        $detail_id = $DB->QueryMaxId("order_detail", "detail_id");
        $DB->QueryInsert("order_detail", [
            "detail_id"     => $detail_id,
            "order_id"      => $order_id,
            "variant_id"    => $item[0]['variant_id'],
            "product_name"  => $item[0]['product_name'],
            "variant_color" => $item[0]['variant_color'],
            "variant_size"  => $item[0]['variant_size'],
            "variant_price" => $item[0]['variant_sale'],
            "qty"           => $cart_qty,
            "order_type"    => 1,
            "total_price"   => $item[0]['variant_sale'] * $cart_qty
        ]);
        $update_stock = $DB->Query("UPDATE variants SET variant_stock = variant_stock-".$cart_qty." WHERE variant_id = ".$item[0]['variant_id']."");

        if ($update_stock) {
            echo json_encode([
                "status" => "success",
                "msg" => "สร้างคำสั่งซื้อสำเร็จ",
                "order_no" => $order_no
            ]);
        }
    }

    // function cancel_order() {
    //     global $DB;
    //     $sql_order = "SELECT variant_id, qty
    //                 FROM order_detail
    //                 WHERE order_type = 1
    //                     AND status_id = 1
    //                     AND order_id = ".$_POST["order_id"]."";
    //     $order_items = $DB->QueryObj($sql_order);
    //     if(sizeof($order_items) > 0) {
    //         foreach ($order_items as $item) {
    //             $DB->Query("UPDATE variants SET variant_stock = variant_stock+".$item['qty']." WHERE variant_id = ".$item['variant_id']."");
    //         }
    //     }
    //     $update = $DB->QueryUpdate("orders",[
    //         "status_id" => 4,
    //         "order_status"  => "ยกเลิก"
    //     ],"order_id=".$_POST["order_id"]);
    //     if ($update) {
    //         echo json_encode([
    //             "status" => "success",
    //             "title" => "ยกเลิกสำเร็จ",
    //             "msg" => "รายการสั่งซื้อของคุณถูกยกเลิก",
    //             "icon" => "success"
    //         ]);
    //     }
    // }