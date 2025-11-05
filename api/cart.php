<?php

    include("../config/all.php");
    if (!isset($_SESSION["user_info"])) {
        echo json_encode([
            "data"=>'f',
        ]);
        exit();
    }

    $currentUser = $_SESSION['user_info'];
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_cart'	    : select_cart(); 	    break;
        case 'change_qty'	    : change_qty(); 	    break;
        case 'delete_cart'	    : delete_cart(); 	    break;
        case 'empty_basket'	    : empty_basket(); 	    break;
        case 'checkout'	        : checkout(); 	        break;
		default: break;
	}

    function select_cart() {
        global $DB;
        global $currentUser;
        $update = $DB->QueryUpdate("cart",[
            "cart_status" => 1
        ], "user_id = '".htmlspecialchars($currentUser['id'])."'");
        if ($update) {
            $sql = "SELECT * 
                FROM cart
                INNER JOIN variants
                    ON cart.variant_id = variants.variant_id
                INNER JOIN product
                    ON variants.product_id = product.product_id
                LEFT JOIN img
                    ON variants.img_id = img.img_id
                WHERE user_id = '".htmlspecialchars($currentUser['id'])."'
                ORDER BY cart_id DESC";
            $return = array();
            $return["data"] = $DB->QueryObj($sql);
            echo json_encode( $return );
        }
    }

    function change_qty() {
        global $DB;
        $sql = "SELECT variants.variant_id, variants.variant_stock
                FROM cart
                INNER JOIN variants
                    ON cart.variant_id = variants.variant_id
                WHERE cart_id = '".$_POST["cart_id"]."'";
        $obj = $DB->QueryObj($sql);
        $sql2 = "SELECT IFNULL(SUM(qty), 0) AS sum_order
            FROM order_detail od
            JOIN orders o ON od.order_id = o.order_id
            WHERE od.variant_id = ".$obj[0]['variant_id']." 
            AND o.status_id IN (2, 3)";
        $obj2 = $DB->QueryObj($sql2);
        $stock = $obj[0]["variant_stock"] - $obj2[0]["sum_order"];
        if ($_POST["cart_qty"] > $stock) {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สามารถเพิ่มได้",
                "message"=>"เนื่องจากสินค้ามีไม่เพียงพอ",
                "icon"=>"error"
            ]);
            exit();
        }
        $update = $DB->QueryUpdate('cart',[
            'cart_qty' => $_POST["cart_qty"]
        ],'cart_id='.$_POST["cart_id"]);
        if ($update) {
            echo json_encode([
                "data"=>'t',
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขรายการสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        }
    }

    function delete_cart() {
        global $DB;
        $delete = $DB->QueryDelete('cart','cart_id='.$_POST["cart_id"]);
        if ($delete) {
            echo json_encode([
                "data"=>'t',
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขรายการสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        }
    }

    function empty_basket() {
        global $DB;
        global $currentUser;
        $delete = $DB->QueryDelete('cart','user_id ='.htmlspecialchars($currentUser['id']));
        if ($delete) {
            echo json_encode([
                "data"=>'t',
                "title"=>"ลบสินค้า",
                "message"=>"ลบสินค้าทั้งหมดออกจากตะกร้าของคุณแล้ว",
                "icon"=>"success"
            ]);
        }
    }

    function checkout() {
        global $DB;
        $cart_ids = $_POST['cart_ids']; // array
        $ids = implode(',', array_map('intval', $cart_ids)); // แปลงเป็น string

        $sql = "UPDATE cart SET cart_status = 2 WHERE cart_id IN ($ids)";
        if($DB->Query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB error']);
        }
    }