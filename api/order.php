<?php
    include("../config/all.php");
    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_order'	    : select_order(); 	    break;
        //case 'create_order'	    : create_order(); 	    break;
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


    // function create_order() {
    //     global $DB;
    //     global $currentUser;
    //     $receipt_id = $_POST["receipt_id"];
    //     $receipt_name = $_POST['receipt_name'];
    //     $receipt_number = $_POST['receipt_number'];
    //     $receipt_address = $_POST['receipt_address'];
    //     $shipping_type = $_POST['shipping_type'];
    //     $shipping_name = $_POST['shipping_name'];
    //     $shipping_phone = $_POST['shipping_phone'];
    //     $shipping_department = $_POST['shipping_department'];
    //     $address_id = $_POST['address_id'];
    //     $shipping_price = $_POST['shipping_price'];
    //     $total_price = $_POST['total_price'];

    //     // 1️⃣ สร้างหมายเลขออเดอร์
    //     $order_no = 'ORD' . date('YmdHis');

    //     // 2️⃣ เพิ่มข้อมูลในตาราง order
    //     $order_id = $DB->QueryMaxId("orders", "order_id");
    //     $insert_order = $DB->QueryInsert("orders", [
    //         "order_id"              => $order_id,
    //         "order_no"              => $order_no,
    //         "user_id"               => htmlspecialchars($currentUser['id']),
    //         "total_price"           => $total_price,
    //         "shipping_price"        => $shipping_price,
    //         "receipt_id"            => $receipt_id,
    //         "receipt_name"          => $receipt_name,
    //         "receipt_number"        => $receipt_number,
    //         "receipt_address"       => $receipt_address,
    //         "shipping_type"         => $shipping_type,
    //         "shipping_name"         => $shipping_name,
    //         "shipping_phone"        => $shipping_phone,
    //         "shipping_department"   => $shipping_department,
    //         "address_id"            => $address_id,
    //         "order_status"          => "รอการชำระเงิน",
    //         "status_id"             => 1,
    //         "create_date"           => date("Y-m-d H:i:s")
    //     ]);

    //     // 3️⃣ ดึงข้อมูลจากตะกร้าของ user
    //     $sql_cart = "SELECT 
    //                     cart.variant_id,
    //                     cart.cart_qty,
    //                     cart.cart_type,
    //                     variants.variant_sale,
    //                     variants.variant_color,
    //                     variants.variant_size,
    //                     product.product_name
    //                 FROM cart
    //                 INNER JOIN variants ON cart.variant_id = variants.variant_id
    //                 INNER JOIN product ON variants.product_id = product.product_id
    //                 WHERE cart.cart_status = 2 AND cart.user_id = '".htmlspecialchars($currentUser['id'])."'";
    //     $cart_items = $DB->QueryObj($sql_cart);

    //     // 4️⃣ วนลูปเพิ่มข้อมูลเข้า order_detail
    //     foreach ($cart_items as $item) {
    //         $detail_id = $DB->QueryMaxId("order_detail", "detail_id");
    //         $DB->QueryInsert("order_detail", [
    //             "detail_id"     => $detail_id,
    //             "order_id"      => $order_id,
    //             "variant_id"    => $item['variant_id'],
    //             "product_name"  => $item['product_name'],
    //             "variant_color" => $item['variant_color'],
    //             "variant_size"  => $item['variant_size'],
    //             "variant_price" => $item['variant_sale'],
    //             "qty"           => $item['cart_qty'],
    //             "order_type"    => $item['cart_type'],
    //             "total_price"   => $item['variant_sale'] * $item['cart_qty']
    //         ]);
    //         if($item['cart_type'] == 1){
    //             $DB->Query("UPDATE variants SET variant_stock = variant_stock-".$item['cart_qty']." WHERE variant_id = ".$item['variant_id']."");
    //         }
    //     }

    //     $delete = $DB->QueryDelete("cart","cart_status = 2 AND user_id = '".htmlspecialchars($currentUser['id'])."'");
    //     if ($delete) {
    //         echo json_encode([
    //             "status" => "success",
    //             "msg" => "สร้างคำสั่งซื้อสำเร็จ",
    //             "order_no" => $order_no
    //         ]);
    //     }
    // }

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