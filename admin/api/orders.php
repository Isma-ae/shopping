<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_count'	    : select_count(); 	    break;
        case 'select_order'	    : select_order(); 	    break;
        case 'update_shipping'	: update_shipping(); 	break;
        case 'update_receipt'	: update_receipt(); 	break;
        case 'update_status'	: update_status(); 	    break;
		default: break;
	}

    function select_count() {
        global $DB;
        $sql = "SELECT COUNT(order_id) AS count_status FROM orders";
        $return["count1"] = $DB->QueryObj($sql." WHERE status_id = 1");
        $return["count2"] = $DB->QueryObj($sql." WHERE status_id = 2");
        $return["count3"] = $DB->QueryObj($sql." WHERE status_id = 3");
        $return["count4"] = $DB->QueryObj($sql." WHERE status_id = 4");
        echo json_encode( $return );
    }

    function select_order() {
        global $DB;
        $sql="SELECT 
            order_id,
            order_no,
            orders.user_id,
            users.name,
            tb_address.name AS recipient_name,
            tb_address.phone AS recipient_phone,
            orders.shipping_name,
            total_price,
            orders.status_id,
            orders.shipping_type,
            orders.shipping_name,
            orders.shipping_phone,
            orders.shipping_department,
            address_at,
            subdistrict_name_in_thai,
            district_name_in_thai,
            province_name_in_thai,
            zip_code,
            transported_name,
            parcel_number
        FROM orders
        LEFT JOIN users 
                ON orders.status_id = users.id
        LEFT JOIN tb_address 
                ON orders.address_id = tb_address.id
        LEFT JOIN provinces
                ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts
                ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts
                ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported
                ON orders.transported_id = transported.transported_id
        WHERE orders.status_id = ".$_POST["status_id"]."
        ORDER BY order_id DESC";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function update_shipping() {
        global $DB;
        $update = $DB->QueryUpdate("orders",[
            "transported_id" => $_POST["transported_id"],
            "parcel_number" => $_POST["parcel_number"]
        ],"order_id = ".$_POST["order_id"]);
        if ($update) {
           echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"บันทึกข้อมูลจัดส่งเรียบร้อย",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"บันทึกข้อมูลจัดส่งไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
        
    }

    function update_receipt() {
        global $DB;
        $update = $DB->QueryUpdate("orders",[
            "receipt_link" => $_POST["receipt_link"]
        ],"order_id = ".$_POST["order_id"]);
        if ($update) {
           echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"บันทึกใบเสร็จรับเงินเรียบร้อย",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"บันทึกใบเสร็จรับเงินไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }
    
    function update_status() {
        global $DB;
        $update = $DB->QueryUpdate("orders",[
            "status_id" => $_POST["status_id"]
        ],"order_id = ".$_POST["order_id"]);
        if ($update) {
           echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"บันทึกสถานะเรียบร้อย",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"บันทึกสถานะไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }

?>