<?php
    include("../config/all.php");
    
    $message_id = $DB->QueryMaxId('message','message_id');
    $email = $_POST["email"];
    $msg = $_POST["msg"];
    $send_dete = date('Y-m-d H:i:s');
    $insert = $DB->QueryInsert('message',[
        'message_id'    => $message_id,
        'email'         => $email,
        'msg'           => $msg,
        'send_date'     => $send_dete,
        'read'          => 0
    ]);

    if ($insert) {
        echo json_encode([
                "status"=>'success',
                "title"=>"สำเร็จ",
                "message"=>"ข้อความของคุณถูกส่งไปยังผู้ดูแลระบบแล้ว",
                "icon"=>"success"
        ]);
    } else {
        echo json_encode([
                "status"=>'error',
                "title"=>"ไม่สำเร็จ",
                "message"=>"ไม่สามารถส่งข้อความได้",
                "icon"=>"error"
        ]);
    }