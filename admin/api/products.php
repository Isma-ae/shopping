<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_product'	: select_product(); 	    break;
        case 'edit_product'	    : edit_product(); 	        break;
        case 'delete_product'	: delete_product(); 	    break;
        case 'change_status'	: change_status(); 	        break;
		default: break;
	}

    function select_product() {
        global $DB;
        $sql = "SELECT
                    p.product_id,
                    p.product_name,
                    c.category_name,
                    i.img_name,

                    COALESCE(vs.total_stock, 0) AS total_stock,
                    COALESCE(os.total_ordered, 0) AS total_ordered,
                    COALESCE(vs.total_stock, 0) - COALESCE(os.total_ordered, 0) AS remaining_stock

                FROM product p

                INNER JOIN category c
                    ON p.category_id = c.category_id

                LEFT JOIN img i
                    ON p.product_id = i.product_id
                    AND i.img_main = '1'

                LEFT JOIN (
                    SELECT
                        product_id,
                        SUM(variant_stock) AS total_stock
                    FROM variants
                    GROUP BY product_id
                ) vs ON p.product_id = vs.product_id

                LEFT JOIN (
                    SELECT
                        v.product_id,
                        SUM(o.qty) AS total_ordered
                    FROM order_detail o
                    INNER JOIN variants v
                        ON o.variant_id = v.variant_id
                    INNER JOIN orders ord
                        ON o.order_id = ord.order_id
                        AND ord.status_id NOT IN (1,4)
                    GROUP BY v.product_id
                ) os ON p.product_id = os.product_id

                ORDER BY p.product_id
        ";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function edit_product() {
        global $DB;
        $product_id = $_POST["product_id"];
        $update = $DB->QueryUpdate("product",[
            'product_name'      => $_POST["product_name"],
            'product_detail'    => $_POST["product_detail"],
            'category_id'       => $_POST["category_id"],
            'reserve_id'       => $_POST["reserve_id"]
        ], "product_id = '".$product_id."'");
        if($update){
            echo json_encode([
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขสินค้าไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    };

    function delete_product() {
        global $DB;
        $dir = "../../file/product/";
        $obj = $DB->QueryObj("SELECT * FROM variants WHERE product_id = '".$_POST['product_id']."'");
        $obj2 = $DB->QueryObj("SELECT * FROM img WHERE product_id = '".$_POST['product_id']."'");
        $delete = $DB->QueryDelete("product","product_id = '".$_POST['product_id']."'");
        $delete2 = $DB->QueryDelete("variants","product_id = '".$_POST['product_id']."'");
        $delete3 = $DB->QueryDelete("img","product_id = '".$_POST['product_id']."'");
        if ($delete) {
            foreach ($obj2 as $value) {
                deleteFile($dir,$value["img_name"]);
            }
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

    function change_status() {
        global $DB;
        $product_id = $_POST["product_id"];
        $status_id = $_POST["status_id"];
        $update = $DB->QueryUpdate("product",[
            'product_status'      => $status_id
        ], "product_id = '".$product_id."'");
        if($update){
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขสินค้าไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }
?>