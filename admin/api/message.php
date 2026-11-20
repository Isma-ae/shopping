<?php
    include("../../config/all.php");
    require '../../vendor/autoload.php';
    header("Content-Type: application/json; charset=utf-8");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        echo json_encode([
            "status"  => "error",
            "title"   => "ไม่มีสิทธิ์เข้าถึง",
            "message" => "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $message_id = (int)($_POST["message_id"] ?? 0);
    $email = trim((string)($_POST["email"] ?? ""));
    $message_reply = trim((string)($_POST["message_reply"] ?? ""));

    if ($message_id <= 0 || $email === "" || $message_reply === "") {
        echo json_encode([
            "status"  => "error",
            "title"   => "ข้อมูลไม่ครบถ้วน",
            "message" => "กรุณากรอกข้อความตอบกลับก่อนส่ง",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $update = $DB->QueryUpdate("message", [
        "message_reply" => $message_reply,
        "read"          => 1
    ], "message_id = {$message_id}");

    if ($update) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'oarshopping@gmail.com';
            $mail->Password   = 'ytdu mhio yiym wvaq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom('oarshopping@gmail.com', 'OAR Shopping');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "ข้อความตอบกลับจากระบบ OAR Shopping";
            $mail->Body    = $message_reply;
            $mail->send();

            echo json_encode([
                "status"  => "success",
                "title"   => "ส่งข้อความสำเร็จ",
                "message" => "บันทึกและส่งข้อความตอบกลับไปยังอีเมล {$email} เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode([
                "status"  => "warning",
                "title"   => "บันทึกแล้ว แต่ส่งอีเมลไม่สำเร็จ",
                "message" => "บันทึกคำตอบลงระบบแล้ว แต่ไม่สามารถส่งอีเมลได้ (" . $e->getMessage() . ")",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode([
            "status"  => "error",
            "title"   => "ไม่สำเร็จ",
            "message" => "บันทึกข้อความตอบกลับไม่สำเร็จ",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
    }
