<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_stock'	    : select_stock(); 	    break;
        case 'edit_variant'	    : edit_variant(); 	    break;
        case 'delete_variant'	: delete_variant(); 	break;
		default: break;
	}

    function select_stock() {
        global $DB;
        $sql = "SELECT *
                FROM variants
                LEFT JOIN img
                    ON variants.img_id = img.img_id
                WHERE variants.product_id = '".$_POST["product_id"]."'
                ";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function edit_variant() {
        global $DB;
        $variant_id = $_POST["variant_id"];
        $product_id = $_POST["product_id"];
        $variant_color = $_POST["variant_color"];
        $img_id = $_POST["img_id"];
        $update = $DB->QueryUpdate("variants",[
            "variant_style" => $_POST["variant_style"],
            "variant_color" => $variant_color,
            "variant_size"  => $_POST["variant_size"],
            "size_detail"   => $_POST["size_detail"],
            "variant_price" => $_POST["variant_price"],
            "variant_sale"  => $_POST["variant_sale"],
            "variant_stock" => $_POST["variant_stock"]
        ], "variant_id = '".$variant_id."'");
        if($update){
            if (!empty($img_id) && $img_id != 0) {
                $DB->QueryUpdate("variants", [
                    "img_id" => $img_id
                ], "variant_color = '".$variant_color."' AND product_id = '".$product_id."'");
            }

            echo json_encode([
                "title"   => "สำเร็จ",
                "message" => "แก้ไขรายการสินค้าสำเร็จ",
                "icon"    => "success"
            ]);
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขรายการสินค้าไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }

    function delete_variant() {
        global $DB;
        $dir = "../../file/product-stock/";
        $obj = $DB->QueryObj("SELECT * FROM variants WHERE variant_id = ".$_POST['variant_id']."");
        $delete = $DB->QueryDelete('variants','variant_id = '.$_POST['variant_id'].'');
        if ($delete) {
            deleteFile($dir,$obj[0]["color_img"]);
            echo json_encode([
                "title"=>"สำเร็จ",
                "message"=>"ลบรายการสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"ลบรายการสินค้าไม่สำเร็จเนื่องจาก ".$delete->error,
                "icon"=>"error"
            ]);
        }
    }

?>