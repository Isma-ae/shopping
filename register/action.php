<?php

    include("../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'register'	    : register(); 	    break;
		default: break;
	}

    function register() {
        global $DB;
        $user_name = $_POST["name"];
        $user_id = $_POST["user_id"];
        $user_password = $_POST["user_password"];
        $confirm_password = $_POST["confirm_password"];
        $user_phone = $_POST["user_phone"];
        if ($user_name == "") {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"กรุณากรอกชื่อ นามสกุล",
                "icon"=>"error"
            ]);
            exit();
        }

        if ($user_id == "") {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"กรุณากรอกอีเมลล์",
                "icon"=>"error"
            ]);
            exit();
        } elseif (!filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"รูปแบบอีเมลไม่ถูกต้อง",
                "icon"=>"error"
            ]);
            exit();
        }

        if ($user_password == "") {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"กรุณากรอกรหัสผ่าน",
                "icon"=>"error"
            ]);
            exit();
        }

        if (strlen($user_password) < 8) {
            echo json_encode([
                "data" => 'n',
                "title" => "ไม่สำเร็จ",
                "message" => "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร",
                "icon" => "error"
            ]);
            exit();
        }

        if ($confirm_password == "") {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"กรุณายืนยันรหัสผ่าน",
                "icon"=>"error"
            ]);
            exit();
        }

        if ($user_password !== $confirm_password) {
            echo json_encode([
                "data" => 'n',
                "title" => "ไม่สำเร็จ",
                "message" => "รหัสผ่านไม่ตรงกัน",
                "icon" => "error"
            ]);
            exit();
        }

        $check = "SELECT * FROM users WHERE user_id = '".$user_id."'";
        $objCheck = $DB->QueryObj($check);
        if(sizeof($objCheck) > 0) {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"อีเมลนี้ถูกใช้งานแล้ว",
                "icon"=>"error"
            ]);
            exit();
        }
        $id = $DB->QueryMaxId('users','id');
        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
        $insert = $DB->QueryInsert("users",[
            "id"            => $id,
            "user_name"     => $user_name,
            "user_id"       => $user_id,
            "user_password" => $hashed_password,
            "user_email"    => $user_id,
            "user_phone"    => $user_phone
        ]);
        if ($insert) {
            echo json_encode([
                "data"=>'y',
                "title"=>"สมัครสมาชิกสำเร็จ",
                "message"=>"กรุณาเข้าสู่ระบบก่อนใช้งาน",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"ไม่สามารถสมัครสมาชิกได้",
                "icon"=>"error"
            ]);
        }
        
    }