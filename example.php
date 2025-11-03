<?php
// เรียกใช้งาน autoload ของ Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // ตั้งค่า SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'air.app204@gmail.com';
    $mail->Password   = 'hkbk oljg rtop qrxl'; // ใช้ App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // ผู้ส่ง/ผู้รับ
    $mail->setFrom('air.app204@gmail.com', 'อิสมาแอ');
    $mail->addAddress('kuan.pra.bat@gmail.com', 'คุณแอ');

    // เนื้อหา
    $mail->isHTML(true);
    $mail->Subject = "ยืนยันคำสั่งซื้อของคุณ #$order_id สำเร็จแล้ว!";

    $mail->Body = '
    <html>
    <head>
    <style>
        body { font-family: "Prompt", Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden; }
        .header { background-color: #FF5722; color: white; text-align: center; padding: 20px 10px; font-size: 22px; font-weight: bold; }
        .content { padding: 20px; background-color: #fff; }
        .order-info { background-color: #f9f9f9; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .order-info p { margin: 6px 0; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .product-table th, .product-table td { border-bottom: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle; }
        .footer { background-color: #fafafa; text-align: center; padding: 15px; color: #777; font-size: 13px; border-top: 1px solid #eee; }
        img.product-thumb { border-radius: 6px; border: 1px solid #eee; width: 60px; height: 60px; object-fit: cover; }
    </style>
    </head>
    <body>
    <div class="container">
        <div class="header">ขอบคุณที่สั่งซื้อสินค้ากับเรา ❤️</div>
        <div class="content">
        <p>เรียนคุณ <b>'.$customer_name.'</b>,</p>
        <p>เราขอขอบคุณที่สั่งซื้อสินค้ากับ <b>'.$shop_name.'</b> คำสั่งซื้อของคุณได้รับเรียบร้อยแล้ว!</p>

        <div class="order-info">
            <p><b>หมายเลขคำสั่งซื้อ:</b> '.$order_id.'</p>
            <p><b>วันที่สั่งซื้อ:</b> '.$order_date.'</p>
            <p><b>สถานะ:</b> รอการชำระเงิน</p>
        </div>

        <table class="product-table">
            <thead>
            <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคา</th>
            </tr>
            </thead>
            <tbody>
            '.$product_list.'
            </tbody>
        </table>

        <p style="text-align:right; font-size:16px; margin-top:10px;">
            <b>ยอดรวมทั้งหมด: '.number_format($order_total, 2).' บาท</b>
        </p>

        <div class="order-info">
            <p><b>จัดส่งไปที่:</b><br>'.$shipping_address.'</p>
        </div>

        <p style="text-align:center; margin-top:20px;">
            <a href="'.$order_url.'" style="background-color:#FF5722; color:#fff; padding:10px 20px; border-radius:6px; text-decoration:none;">ดูรายละเอียดคำสั่งซื้อ</a>
        </p>
        </div>
        <div class="footer">
        © '.date("Y").' '.$shop_name.'. All rights reserved.
        </div>
    </div>
    </body>
    </html>
    ';

    //$mail->Subject = 'ทดสอบส่งอีเมลด้วย PHPMailer';
    //$mail->Body    = '<b>สวัสดีครับ</b><br>นี่คือการทดสอบส่งอีเมลด้วย PHPMailer ผ่าน Gmail SMTP.';

    $mail->send();
    echo '✅ ส่งอีเมลสำเร็จแล้ว!';
} catch (Exception $e) {
    echo "❌ ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}";
}

