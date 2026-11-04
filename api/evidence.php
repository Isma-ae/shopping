<?php
    include("../config/all.php");
    $ftp_dir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/evidence/";
    //$dir = "../file/evidence/";
    
    $evidence_id = $DB->QueryMaxId("evidence","evidence_id");
    $order_no = $DB->Escape($_POST["order_no"]);
    $file = $_FILES["evidence_slip"];
    $evidence_slip = uploadFileFTP($host, $user, $pass, $ftp_dir, $file, md5($evidence_id));//uploadFile($dir,$img,md5($evidence_id));
    if ($evidence_slip != "") {
        $insert = $DB->QueryInsert('evidence',[
            'evidence_id' => $evidence_id,
            'evidence_slip' => $evidence_slip,
            'order_no' => $order_no
        ]);

        if ($insert) {
            echo json_encode([
                "status"=>'success',
                "title"=>"สำเร็จ",
                "message"=>"หลักฐานการชำระเงินถูกส่งไปยังผู้ดูแลระบบแล้ว",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "status"=>'error',
                "title"=>"ไม่สำเร็จ",
                "message"=>"ไม่สามารถส่งหลักฐานการชำระเงินได้",
                "icon"=>"error"
            ]);
        }
        
    } else {
        echo json_encode([
            "status"=>'error',
            "title"=>"ไม่สำเร็จ",
            "message"=>"ไม่พบหลักฐานการชำระเงิน",
            "icon"=>"error"
        ]);
    }
?>