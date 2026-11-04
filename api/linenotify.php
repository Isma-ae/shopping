<?php
        $apiUrl = "https://jfk.oas.psu.ac.th/oarnotify/notify-bot/msg_rcv";
        // กำหนดข้อมูลที่จะส่ง
        include("../config/all.php");
        $order_no  = $DB->Escape($_POST["order_no"]);
        $sql = "SELECT * FROM orders
                WHERE order_no = '".$order_no."'";
        $obj = $DB->QueryObj($sql);
        if ($obj[0]["shipping_type"] == "1") {
            $ship_type = 'รับหน้าร้าน';
        } elseif ($obj[0]["shipping_type"] == "2") {
            $ship_type = 'ส่งภายในวิทยาเขต';
        } else {
            $ship_type = 'ส่งภายนอกวิทยาเขต';
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
            $product_list .= $value2["product_name"].' จำนวน '.$value2["qty"].' ราคา '.number_format($value2["total_price"], 2).' บาท<br>';
        }
        $data = [
            "msg_detail" => "รหัสสั่งซื้อ ".$order_no."<br>".$product_list."<br> ยอดรวมทั้งหมด ".number_format($obj[0]["total_price"], 2)." บาท"
        ];

        // กำหนด API Key
        $apiKey = "d7347b0d8b866c371cd2c4bd64e4a83a";

        // สร้าง cURL request
        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);
        if ($response == false) {
            exit();
        }