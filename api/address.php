<?php

    include("../config/all.php");
    $currentUser = $_SESSION['user_info'];
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_address'	    : select_address(); 	    break;
        case 'select_provinces'	    : select_provinces(); 	    break;
        case 'select_districts'	    : select_districts(); 	    break;
        case 'select_subdistricts'	: select_subdistricts(); 	break;
        case 'select_zip_code'	    : select_zip_code(); 	    break;
        case 'insert_address'	    : insert_address(); 	    break;
        case 'update_address'	    : update_address(); 	    break;
        case 'delete_address'	    : delete_address(); 	    break;
		default: break;
	}

    function select_address() {
        global $DB;
        global $currentUser;
        $sql = "SELECT * 
                FROM tb_address
                INNER JOIN provinces
                    ON tb_address.province_id = provinces.province_id
                INNER JOIN districts
                    ON tb_address.district_id = districts.district_id
                INNER JOIN subdistricts
                    ON tb_address.subdistrict_id = subdistricts.subdistrict_id
                WHERE user_id = '".htmlspecialchars($currentUser['id'])."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function select_provinces() {
        global $DB;
        $sql = "SELECT * FROM provinces";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function select_districts() {
        global $DB;
        $sql = "SELECT * FROM districts WHERE province_id = '".$_POST["province_id"]."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function select_subdistricts() {
        global $DB;
        $sql = "SELECT * FROM subdistricts WHERE district_id = '".$_POST["district_id"]."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function select_zip_code() {
        global $DB;
        $sql = "SELECT * FROM subdistricts WHERE subdistrict_id = '".$_POST["subdistrict_id"]."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function insert_address() {
        global $DB;
        global $currentUser;
        $address_id = $DB->QueryMaxId('tb_address','id');
        $insert = $DB->QueryInsert('tb_address',[
            "id" => $address_id,
            "name" => $_POST["name"],
            "phone" => $_POST["phone"],
            "province_id" => $_POST["province_id"],
            "district_id" => $_POST["district_id"],
            "subdistrict_id" => $_POST["subdistrict_id"],
            "address_at" => $_POST["address_at"],
            "user_id" => htmlspecialchars($currentUser['id'])
        ]);
        if ($insert) {
            echo json_encode([
                "status"=>'success',
                "title"=>"บันทึกสำเร็จ",
                "message"=>"เพิ่มที่อยู่ใหม่เรียบร้อย",
                "icon"=>"success"
            ]);
        }
    }

    function update_address() {
        global $DB;
        $address_id = $_POST["address_id"];
        $update = $DB->QueryUpdate('tb_address',[
            "name" => $_POST["name"],
            "phone" => $_POST["phone"],
            "province_id" => $_POST["province_id"],
            "district_id" => $_POST["district_id"],
            "subdistrict_id" => $_POST["subdistrict_id"],
            "address_at" => $_POST["address_at"]
        ],'id='.$address_id);
        if ($update) {
            echo json_encode([
                "status"=>'success',
                "title"=>"บันทึกสำเร็จ",
                "message"=>"แก้ไขที่อยู่เรียบร้อย",
                "icon"=>"success"
            ]);
        }
    }

    function delete_address() {
        global $DB;
        $address_id = $_POST["address_id"];
        $delete = $DB->QueryDelete('tb_address', 'id='.$address_id);
        if ($delete) {
            echo json_encode([
                "status"=>'success',
                "title"=>"สำเร็จ",
                "message"=>"ลบที่อยู่เรียบร้อย",
                "icon"=>"success"
            ]);
        }
    }