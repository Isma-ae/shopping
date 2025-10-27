<?php

    include("../config/all.php");
    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'select_review'    : select_review();  break;
        case 'add_review'       : add_review();     break;
		default: break;
	}

    function select_review() {
        global $DB;
        $product_id = $_POST["product_id"];
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;      // โหลดครั้งละ 5
        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;   // เริ่มต้นจาก 0

        $sql = "SELECT r.*, u.name
                FROM review r
                INNER JOIN users u ON r.user_id = u.id
                WHERE MD5(r.product_id) = '$product_id'
                ORDER BY r.review_id DESC
                LIMIT $limit OFFSET $offset";

        $return = array();
        $return["data"] = $DB->QueryObj($sql);

        // ตรวจสอบว่ามีรีวิวเพิ่มหรือไม่
        $sql_count = "SELECT COUNT(*) AS total
                    FROM review
                    WHERE MD5(product_id) = '$product_id'";
        $count = $DB->QueryObj($sql_count);
        $return["total"] = $count[0]['total'];

        echo json_encode($return);
    }

    function add_review() {
        global $DB;
        if (!isset($_SESSION["user_info"])) {
            echo json_encode([
                "data"=>'f',
                "title"=>"กรุณาเข้าสู่ระบบ",
                "message"=>"เข้าสู่ระบบก่อนหยิบสินค้าลงตะกร้าหรือสั่งซื้อสินค้า",
                "icon"=>"warning"
            ]);
            exit();
        }
        
        $obj = $DB->QueryObj("SELECT product_id FROM product WHERE MD5(product_id) = '".$_POST["product_id"]."'");
        $currentUser = $_SESSION['user_info'];
        $insert = $DB->QueryInsert('review',[
            'review_id'     => $DB->QueryMaxId('review','review_id'),
            'review_rating' => $_POST["review_rating"],
            'review_detail' => $_POST["review_detail"],
            'product_id'    => $obj[0]["product_id"],
            'user_id'       => htmlspecialchars($currentUser['id'])
        ]);

        if ($insert) {
            echo json_encode([
                "data"=>'t',
                "title"=> "รีวิวสินค้า",
                "message"=>"รีวิวของคุณถูกบันทึกแล้ว",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "data"=>'n',
                "title"=>"ไม่สำเร็จ",
                "message"=>"รีวิวของคุณไม่ถูกบันทึกแล้วเนื่องจาก ".$insert->error,
                "icon"=>"error"
            ]);
        }
    }