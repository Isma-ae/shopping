<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_category'	: select_category(); 	    break;
        case 'add_category'	    : add_category(); 	        break;
        case 'edit_category'	: edit_category(); 	        break;
        case 'delete_category'	: delete_category(); 	    break;

        case 'select_shipping'	: select_shipping(); 	    break;
        case 'add_shipping'	    : add_shipping(); 	        break;
        case 'edit_shipping'	: edit_shipping(); 	        break;
        case 'delete_shipping'	: delete_shipping(); 	    break;
		default: break;
	}

    function select_category() {
        global $DB;
        $sql = "SELECT * FROM category";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function add_category() {
        global $DB;
        $category_id = $DB->QueryMaxId("category","category_id");
        $insert = $DB->QueryInsert("category",[
            'category_id'      => $category_id,
            'category_name'    => $_POST["category_name"]
        ]);
        if($insert){
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"เพิ่มประเภทสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"เพิ่มประเภทสินค้าไม่สำเร็จเนื่องจาก ".$insert->error,
                "icon"=>"error"
            ]);
        }
    }

    function edit_category() {
        global $DB;
        $category_id = $_POST["category_id"];
        $update = $DB->QueryUpdate("category",[
            'category_name'      => $_POST["category_name"]
        ], "category_id = '".$category_id."'");
        if($update){
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขประเภทสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขประเภทสินค้าไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    };

    function delete_category() {
        global $DB;
        $delete = $DB->QueryDelete("category","category_id = '".$_POST['category_id']."'");
        if ($delete) {
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"ลบประเภทสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"ลบประเภทสินค้าไม่สำเร็จเนื่องจาก ".$delete->error,
                "icon"=>"error"
            ]);
        }
    }

    function select_shipping() {
        global $DB;
        $sql = "SELECT * FROM transported";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function add_shipping() {
        global $DB;
        $transported_id = $DB->QueryMaxId("transported","transported_id");
        $insert = $DB->QueryInsert("transported",[
            'transported_id'      => $transported_id,
            'transported_name'    => $_POST["transported_name"]
        ]);
        if($insert){
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"เพิ่มบริษัทขนส่งสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"เพิ่มบริษัทขนส่งไม่สำเร็จเนื่องจาก ".$insert->error,
                "icon"=>"error"
            ]);
        }
    }

    function edit_shipping() {
        global $DB;
        $transported_id = $_POST["transported_id"];
        $update = $DB->QueryUpdate("transported",[
            'transported_name'      => $_POST["transported_name"]
        ], "transported_id = '".$transported_id."'");
        if($update){
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"แก้ไขบริษัทขนส่งสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขบริษัทขนส่งไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    };

    function delete_shipping() {
        global $DB;
        $delete = $DB->QueryDelete("transported","transported_id = '".$_POST['transported_id']."'");
        if ($delete) {
            echo json_encode([
                "status"=>"success",
                "title"=>"สำเร็จ",
                "message"=>"ลบบริษัทขนส่งสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"ลบบริษัทขนส่งไม่สำเร็จเนื่องจาก ".$delete->error,
                "icon"=>"error"
            ]);
        }
    }
?>