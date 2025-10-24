<?php
    include("../../config/all.php");

    $dir = "../../file/product/";
    $variants = json_decode($_POST['variants'], true);
    $img_id = $DB->QueryMaxId("img","img_id");
    $product_id = $DB->QueryMaxId("product","product_id", "", 7);
    $img = $_FILES["img_name"];
    $img_name = uploadFile($dir,$img,md5($img_id));
    $insert = $DB->QueryInsert('product',[
        'product_id'        => $product_id,
        'product_name'      => $_POST["product_name"],
        'product_detail'    => $_POST["product_detail"],
        'category_id'       => $_POST["category_id"],
        'reserve_id'        => $_POST["reserve_id"],
        'product_status'    => 1
    ]);

    foreach ($variants as $v) {
        $parsed = parseSize($v['size_shirt']);
        $variant_id = $DB->QueryMaxId("variants","variant_id");
        $variants = $DB->QueryInsert("variants", [
            "variant_id"        => $variant_id,
            "product_api_id"     => $v['id'],
            "variant_style"     => $v['style_shirt'],
            "variant_color"     => $v['color_shirt'],
            "variant_size"      => $parsed['size_shirt'],
            "size_detail"       => $parsed['size_detail'],
            "size_order"        => $parsed['size_order'],
            "variant_price"     => $v['price'],
            "variant_sale"      => $v['price_sale'],
            "variant_stock"     => $v['number_stock'],
            "variant_img"       => $v['picture'],
            "product_id"        => $product_id
        ]);
    }

    if ($insert) {
        $DB->QueryInsert('img',[
            'img_id' => $img_id,
            'img_name' => $img_name,
            'img_main' => '1',
            'product_id' => $product_id
        ]);
        echo json_encode([
            "data"=>"y",
            "title"=>"สำเร็จ",
            "message"=>"เพิ่มสินค้าเรียบร้อย",
            "icon"=>"success"
        ]);
    } else {
        echo json_encode([
            "data"=>"n",
            "title"=>"ไม่สำเร็จ",
            "message"=>"ไม่สามารถเพิ่มสินค้าได้",
            "icon"=>"error"
        ]);
    }
    