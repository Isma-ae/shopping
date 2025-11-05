<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'change_read'	    : change_read(); 	    break;
        case 'select_message'	: select_message(); 	break;
    }

    function change_read() {
        global $DB;
        $message_id = $_POST["message_id"];
        $read = $DB->QueryUpdate("message",[
            "read" => 1
        ],"message_id=".$message_id);
        if ($read) {
            echo json_encode([
                "status"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>"error",
                "title"=>"ไม่สำเร็จ",
                "message"=>"อ่านข้อความไม่สำเร็จ",
                "icon"=>"error"
            ]);
        }
        
    }

    function select_message() {
        global $DB;
        $sql = "SELECT * FROM message ORDER BY message_id DESC";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    