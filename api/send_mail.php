<?php
    include("../config/all.php");
    require '../vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);

    if (!isset($_SESSION["user_info"])) {
        exit();
    }
    $currentUser = $_SESSION['user_info'];
    $order_no = $DB->Escape($_POST["order_no"]);
    $sql = "SELECT * FROM orders 
            LEFT JOIN tb_address ON orders.address_id = tb_address.id
            LEFT JOIN provinces
                ON tb_address.province_id = provinces.province_id
            LEFT JOIN districts
                ON tb_address.district_id = districts.district_id
            LEFT JOIN subdistricts
                ON tb_address.subdistrict_id = subdistricts.subdistrict_id
            LEFT JOIN transported
                ON orders.transported_id = transported.transported_id
            WHERE orders.order_no = '".$order_no."'";
    $obj = $DB->QueryObj($sql);
    if ($obj[0]["shipping_type"] == "1") {
        $ship_type = 'รับหน้าร้าน';
        $address = '<p class="stext-116 cl8 trans-04 p-t-10">
            <span class="cl6">ผู้รับ:</span> '.$obj[0]["shipping_name"].'
        </p>';
    } elseif ($obj[0]["shipping_type"] == "2") {
        $ship_type = 'ส่งภายในวิทยาเขต';
        $address = '<p class="stext-116 cl8 trans-04 p-t-10">
            <span class="cl6">ผู้รับ:</span> '.$obj[0]["shipping_name"].'
            <span class="cl12 ml-4 mr-6">|</span>
            โทร: '.$obj[0]["shipping_phone"].'
            <span class="cl6">คณะ / หน่วยงาน:</span> '.$obj[0]["shipping_department"].'
        </p>';
    } else {
        $ship_type = 'ส่งภายนอกวิทยาเขต';
        $address = '<p class="stext-116 cl8 trans-04 p-t-10">
            <span class="cl6">ผู้รับ:</span> '.$obj[0]["name"].' 
            <span class="cl12 ml-4 mr-6">|</span>
            <span class="cl6">โทร:</span> '.$obj[0]["phone"].'<br>
            <span class="cl6">ที่อยู่:</span> '.$obj[0]["address_at"].' ตำบล'.$obj[0]["subdistrict_name_in_thai"].' อำเภอ'.$obj[0]["district_name_in_thai"].' จังหวัด'.$obj[0]["province_name_in_thai"].' '.$obj[0]["zip_code"].'
        </p>';
    }

    $sql2 = "SELECT * FROM order_detail
            INNER JOIN variants
                ON order_detail.variant_id = variants.variant_id
            LEFT JOIN img
                ON variants.img_id = img.img_id
            WHERE order_id = '".$obj[0]["order_id"]."'";
    $obj2 = $DB->QueryObj($sql2);
    $product_list = ''; 
    foreach ($obj2 as $i => $value2) {

        $product_list .= '
            <tr>
                <td style="display:flex; align-items:center; gap:10px;">
                    <img src="https://shopping.oas.psu.ac.th/file/product/'.$value2['img_name'].'" width="60" height="60" style="border-radius:6px; border:1px solid #eee;">
                    <div>'.$value2["product_name"].'</div>
                </td>
                <td>'.$value2["qty"].'</td>
                <td>'.number_format($value2["total_price"], 2).' บาท</td>
            </tr>
        ';
    }

    $email = htmlspecialchars($currentUser['email']);
    $message = '
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
        <p>เรียนคุณ <b>'.htmlspecialchars($currentUser['name']).'</b>,</p>
        <p>เราขอขอบคุณที่สั่งซื้อสินค้ากับ <b>OAR Shopping</b> คำสั่งซื้อของคุณได้รับเรียบร้อยแล้ว!</p>

        <div class="order-info">
            <p><b>หมายเลขคำสั่งซื้อ:</b> '.$order_no.'</p>
            <p><b>วันที่สั่งซื้อ:</b> '.$obj[0]["create_date"].'</p>
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
            <b>ยอดรวมทั้งหมด: '.number_format($obj[0]["total_price"], 2).' บาท</b>
        </p>

        <div class="order-info">
            <p><b>จัดส่งไปที่:</b><br>'.$ship_type.' <br> '.$address.'</p>
        </div>

        <p style="text-align:center; margin-top:20px;">
            <a href="https://shopping.oas.psu.ac.th/?page=shipping_list" style="background-color:#FF5722; color:#fff; padding:10px 20px; border-radius:6px; text-decoration:none;">ดูรายละเอียดคำสั่งซื้อ</a>
        </p>
        </div>
    </div>
    </body>
    </html>';
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'air.app204@gmail.com';
    $mail->Password   = 'hkbk oljg rtop qrxl'; // ใช้ App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom('air.app204@gmail.com', 'OAR Shopping');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "ยืนยันคำสั่งซื้อของคุณ #$order_no สำเร็จแล้ว!";
    $mail->Body = $message;
    $mail->send();
    