<?php
    include("../../config/all.php");
    require '../../vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);

    $message_id = $_POST["message_id"];
    $email = $_POST["email"];
    $message_reply = $_POST["message_reply"];

    $update = $DB->QueryUpdate("message",[
        "message_reply" => $message_reply
    ],"message_id = ".$message_id);

    if ($update) {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'oarshopping@gmail.com';
        $mail->Password   = 'ytdu mhio yiym wvaq'; // ใช้ App Password hkbk oljg rtop qrxl
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // ผู้ส่ง/ผู้รับ
        $mail->setFrom('oarshopping@gmail.com', 'OAR Shopping');
        $mail->addAddress($email);

        // เนื้อหา
        $mail->isHTML(true);
        $mail->Subject = "ข้อความตอบกลับจากระบบ OAR Shopping";
        $mail->Body = $message_reply;
        $mail->send();
        echo json_encode([
            "status"=>"success",
            "title"=>"ส่งข้อความ",
            "message"=>"ตอบกลับข้อความไปยังอีเมลแล้ว",
            "icon"=>"success"
        ]);
    } else {
        echo json_encode([
            "status"=>"error",
            "title"=>"ไม่สำเร็จ",
            "message"=>"ตอบกลับข้อความไม่สำเร็จ",
            "icon"=>"error"
        ]);
    }
    