<?php

    include("../config/all.php");

    if (!isset($_SESSION["user_info"])) {
        echo json_encode([
            "data"=>'f',
            "title"=>"กรุณาเข้าสู่ระบบ",
            "message"=>"เข้าสู่ระบบก่อนหยิบสินค้าลงตะกร้าหรือสั่งซื้อสินค้า",
            "icon"=>"warning"
        ]);
        exit();
    }
    
    $currentUser = $_SESSION['user_info'];
    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'add_cart'	        : add_cart();           break;
        case 'reserve_product'  : reserve_product();    break;
		default: break;
	}

    function add_cart() {
        global $DB;
        global $currentUser;
        $sql = "SELECT
                    variant_id,
                    variant_stock,
                    product.reserve_id
                FROM variants
                INNER JOIN product
                    ON variants.product_id = product.product_id
                WHERE
                    variant_color = '".$_POST["variant_color"]."'
                    AND variant_size = '".$_POST["variant_size"]."'
                    AND MD5(variants.product_id) = '".$_POST["product_id"]."'";
        $obj = $DB->QueryObj($sql);
        if ($obj[0]["variant_stock"] < $_POST["cart_qty"]) {
            if ($obj[0]["variant_stock"] < 1) {
                $stock_text = "หมดแล้ว";
            } else {
                $stock_text = "เหลือเพียง ".$obj[0]["variant_stock"];
            }
            if ($obj[0]["reserve_id"] == 2) {
                echo json_encode([
                    "data"=>'n',
                    "title"=>$_POST["product_name"],
                    "message"=> "สี".$_POST["variant_color"]." ขนาด ".$_POST["variant_size"]." ".$stock_text,
                    "icon"=>"error"
                ]);
                exit();
            } else {
                echo json_encode([
                    "data"=>'r',
                    "title"=>$_POST["product_name"],
                    "message"=> "สี".$_POST["variant_color"]." ขนาด ".$_POST["variant_size"]." ".$stock_text." ต้องการจองใช่หรือไม่",
                    "icon"=>"warning"
                ]);
                exit();
            }
        }
        $check = "SELECT
                    cart_id
                FROM cart
                WHERE
                    variant_id = '".$obj[0]["variant_id"]."'
                    AND user_id = '".htmlspecialchars($currentUser['id'])."'
                    AND cart_type = 1";
        $obj_check = $DB->QueryObj($check);
        if (sizeof($obj_check) > 0) {
            $insert = $DB->Query("UPDATE cart SET cart_qty = cart_qty + 1 WHERE cart_id = ".$obj_check[0]["cart_id"]);
        } else {
            $insert = $DB->QueryInsert('cart',[
                'cart_id'       => $DB->QueryMaxId('cart','cart_id'),
                'variant_id'    => $obj[0]["variant_id"],
                'cart_qty'      => $_POST["cart_qty"],
                'user_id'       => htmlspecialchars($currentUser['id']),
                'cart_type'     => 1,
                'cart_status'   => 1,
            ]);
        }

        if ($insert) {
            echo json_encode([
                "data"=>'t',
                "title"=> $_POST["product_name"],
                "message"=>"ถูกเพิ่มลงตะกร้าแล้ว",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"เพิ่มลงตะกร้าไม่สำเร็จเนื่องจาก ".$insert->error,
                "icon"=>"error"
            ]);
        }
    }
    
    function reserve_product() {
        global $DB;
        global $currentUser;
        $sql = "SELECT
                    variant_id
                FROM variants
                WHERE
                    variant_color = '".$_POST["variant_color"]."'
                    AND variant_size = '".$_POST["variant_size"]."'
                    AND MD5(product_id) = '".$_POST["product_id"]."'";
        $obj = $DB->QueryObj($sql);
        $check = "SELECT
                    cart_id
                FROM cart
                WHERE
                    variant_id = '".$obj[0]["variant_id"]."'
                    AND user_id = '".htmlspecialchars($currentUser['id'])."'
                    AND cart_type = 2";
        $obj_check = $DB->QueryObj($check);
        if (sizeof($obj_check) > 0) {
            $insert = $DB->Query("UPDATE cart SET cart_qty = cart_qty + 1 WHERE cart_id = ".$obj_check[0]["cart_id"]);
        } else {
            $insert = $DB->QueryInsert('cart',[
                'cart_id'       => $DB->QueryMaxId('cart','cart_id'),
                'variant_id'    => $obj[0]["variant_id"],
                'cart_qty'      => $_POST["cart_qty"],
                'user_id'       => htmlspecialchars($currentUser['id']),
                'cart_type'     => 2,
                'cart_status'   => 1,
            ]);
        }

        if ($insert) {
            echo json_encode([
                "data"=>'t',
                "title"=> $_POST["product_name"],
                "message"=>"ได้ถูกสั่งจองเรียบร้อยแล้ว",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"สั่งจองเรียบร้อยไม่สำเร็จเนื่องจาก ".$insert->error,
                "icon"=>"error"
            ]);
        }
    }