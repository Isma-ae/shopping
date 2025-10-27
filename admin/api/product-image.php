<?php

    include("../../config/all.php");
	$fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_image'	: select_image(); 	    break;
        case 'upload_image'	: upload_image(); 	    break;
        case 'delete_image'	: delete_image(); 	    break;
        case 'change_main'	: change_main(); 	    break;
        case 'select_stock'	: select_stock(); 	    break;
        case 'edit_variant'	: edit_variant(); 	    break;
		default: break;
	}

    function select_image() {
        global $DB;
        $product_id = $DB->Escape($_POST["product_id"]);
        $sql = "SELECT *
                FROM img
                WHERE product_id = '".$product_id."'";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
        
    }

    function upload_image() {
        global $DB;
        $dir = "../../file/product/";
        $product_id = $_POST["product_id"];
        $files = $_FILES["img_name"];
        $count = count($files["name"]);
        $success = 0;

        for ($i = 0; $i < $count; $i++) {
            if ($files["error"][$i] == 0) {
                $img_id = $DB->QueryMaxId("img", "img_id");
                $file = [
                    "name" => $files["name"][$i],
                    "tmp_name" => $files["tmp_name"][$i]
                ];

                $img_name = uploadFile($dir, $file, md5($img_id));
                if ($img_name != "") {
                    $insert = $DB->QueryInsert('img', [
                        'img_id' => $img_id,
                        'img_name' => $img_name,
                        'img_main' => '2',
                        'product_id' => $product_id
                    ]);
                    if ($insert) $success++;
                }
            }
        }

        if ($success > 0) {
            echo json_encode([
                "data" => "y",
                "title" => "สำเร็จ",
                "message" => "อัปโหลดรูปสินค้าทั้งหมดเรียบร้อย",
                "icon" => "success"
            ]);
        } else {
            echo json_encode([
                "data" => "n",
                "title" => "ไม่สำเร็จ",
                "message" => "ไม่สามารถอัปโหลดรูปสินค้าได้",
                "icon" => "error"
            ]);
        }
    }

    function delete_image() {
        global $DB;
        $dir = "../../file/product/";
        $obj = $DB->QueryObj("SELECT * FROM img WHERE img_id = ".$_POST['img_id']."");
        $delete = $DB->QueryDelete('img','img_id = '.$_POST['img_id'].'');
        if ($delete) {
            deleteFile($dir,$obj[0]["img_name"]);
            echo json_encode([
                "title"=>"สำเร็จ",
                "message"=>"ลบรูปสินค้าสำเร็จ",
                "icon"=>"success"
            ]);
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"ลบรูปสินค้าเนื่องจาก ".$delete->error,
                "icon"=>"error"
            ]);
        }
    }

    function change_main() {
        global $DB;
        $update = $DB->QueryUpdate("img",["img_main"=>1],"img_id=".$_POST["img_id"]."");
        if ($update) {
            $update2 = $DB->QueryUpdate("img",[
                            "img_main"=>2
                        ],"img_id<>".$_POST["img_id"]." AND product_id = '".$_POST["product_id"]."'");
            if ($update2) {
                echo json_encode([
                    "title"=>"สำเร็จ",
                    "message"=>"เปลี่ยนรูปสินค้าหลักสำเร็จ",
                    "icon"=>"success"
                ]);
            } else {
                echo json_encode([
                    "title"=>"ไม่สำเร็จ",
                    "message"=>"ไม่สามารถเปลี่ยนรูปสินค้าหลักเนื่องจาก ".$update2->error,
                    "icon"=>"error"
                ]);
            }
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"ไม่สามารถเปลี่ยนรูปสินค้าหลักเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }

    function select_stock() {
        global $DB;
        $sql = "SELECT *
                FROM variants
                LEFT JOIN img
                    ON variants.img_id = img.img_id
                WHERE variants.product_id = '".$_POST["product_id"]."'
                GROUP BY variant_color
                ";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function edit_variant() {
        global $DB;
        $dir = "../../file/product-stock/";
        $product_id = $_POST["product_id"];
        $variant_color = $_POST["variant_color"];
        $img_id = $_POST["img_id"];
        $update = $DB->QueryUpdate("variants", [
            "img_id" => $img_id
        ], "variant_color = '".$variant_color."' AND product_id = '".$product_id."'");
        if($update){
            echo json_encode([
                "title"   => "สำเร็จ",
                "message" => "แก้ไขรายการสินค้าสำเร็จ",
                "icon"    => "success"
            ]);
        } else {
            echo json_encode([
                "title"=>"ไม่สำเร็จ",
                "message"=>"แก้ไขรายการสินค้าไม่สำเร็จเนื่องจาก ".$update->error,
                "icon"=>"error"
            ]);
        }
    }