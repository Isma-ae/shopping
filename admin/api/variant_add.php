<?php
    include("../../config/all.php");
    $variants = json_decode($_POST['variants'], true);
    $product_id = $_POST["product_id"];

    if (!is_array($variants)) {
        echo json_encode([
            "data" => "n",
            "title" => "ไม่สำเร็จ",
            "message" => "รูปแบบข้อมูลไม่ถูกต้อง",
            "icon" => "error"
        ]);
        exit;
    }

    foreach ($variants as $v) {
        $parsed = parseSize($v['size_shirt']);
        $variant_id = $DB->QueryMaxId("variants","variant_id");
        $variants = $DB->QueryInsert("variants", [
            "variant_id"        => $variant_id,
            "product_api_id"    => $v['id'],
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

    echo json_encode([
        "data"=>"y",
        "title"=>"สำเร็จ",
        "message"=>"เพิ่มเรียบร้อย",
        "icon"=>"success"
    ]);
    