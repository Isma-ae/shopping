<?php

    include("../../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "ไม่มีสิทธิ์เข้าถึง",
            "message" => "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    function isProductAddLocalEnv() {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($httpHost, ['localhost', '127.0.0.1'], true)
            || strpos($httpHost, 'localhost:') === 0
            || strpos($httpHost, '127.0.0.1:') === 0;
    }

    function productSaveUpload($file, $rename) {
        global $host, $user, $pass;
        if (empty($file) || !isset($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return "";
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = getFileType($file['name'] ?? '');
        if (!in_array($ext, $allowed, true)) {
            return "__INVALID_TYPE__";
        }

        $localDir = "../../file/product/";
        $ftpDir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/product/";

        if (!isProductAddLocalEnv()) {
            $uploaded = uploadFileFTP($host, $user, $pass, $ftpDir, $file, $rename);
            if ($uploaded !== "") {
                return $uploaded;
            }
        }
        if (!is_dir($localDir)) {
            @mkdir($localDir, 0775, true);
        }
        return uploadFile($localDir, $file, $rename);
    }

    function clampSizeOrder($orderVal) {
        $n = (int)$orderVal;
        if ($n < 0) return "0";
        if ($n > 9) return "9";
        return (string)$n;
    }

    $product_name   = trim((string)($_POST["product_name"] ?? ""));
    $product_detail = (string)($_POST["product_detail"] ?? "");
    $category_id    = (int)($_POST["category_id"] ?? 0);
    $reserve_id     = (int)($_POST["reserve_id"] ?? 2);
    if (!in_array($reserve_id, [1, 2], true)) {
        $reserve_id = 2;
    }

    $variantsRaw = $_POST["variants"] ?? "[]";
    $variantsList = json_decode($variantsRaw, true);

    if (!is_array($variantsList) || count($variantsList) === 0) {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "ยังไม่ได้เลือกสินค้า",
            "message" => "กรุณาเลือกรายการตัวเลือกสินค้าอย่างน้อย 1 รายการ",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($product_name === "") {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "กรุณากรอกชื่อสินค้า",
            "message" => "กรุณากรอกชื่อสินค้าก่อนบันทึก",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($category_id <= 0) {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "กรุณาเลือกประเภทสินค้า",
            "message" => "กรุณาเลือกประเภทสินค้าก่อนบันทึก",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Validate image upload if provided
    $img_id = $DB->QueryMaxId("img", "img_id");
    $img_name = "";
    if (isset($_FILES["img_name"]) && ($_FILES["img_name"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $img_name = productSaveUpload($_FILES["img_name"], md5($img_id . "_" . time()));
        if ($img_name === "__INVALID_TYPE__") {
            echo json_encode([
                "data"    => "n",
                "status"  => "error",
                "title"   => "ชนิดไฟล์ไม่รองรับ",
                "message" => "รองรับเฉพาะไฟล์รูปภาพ JPG, PNG, GIF และ WEBP เท่านั้น",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        if ($img_name === "") {
            echo json_encode([
                "data"    => "n",
                "status"  => "error",
                "title"   => "อัปโหลดรูปไม่สำเร็จ",
                "message" => "ไม่สามารถอัปโหลดรูปสินค้าหลักได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    // Check already-imported API IDs to prevent duplicate variants
    $existingRows = $DB->QueryObj("SELECT product_api_id FROM variants WHERE product_api_id IS NOT NULL AND product_api_id != ''");
    $existingMap = [];
    foreach ($existingRows as $er) {
        $existingMap[(string)$er["product_api_id"]] = true;
    }

    $validVariants = [];
    foreach ($variantsList as $v) {
        if (!is_array($v)) continue;
        $apiId = trim((string)($v["id"] ?? ""));
        if ($apiId !== "" && isset($existingMap[$apiId])) {
            continue;
        }
        $validVariants[] = $v;
    }

    if (count($validVariants) === 0) {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "รายการถูกนำเข้าแล้ว",
            "message" => "ตัวเลือกสินค้าที่เลือกทั้งหมดถูกนำเข้าในระบบแล้ว กรุณารีเฟรชรายการ",
            "icon"    => "warning"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $product_id = $DB->QueryMaxId("product", "product_id", "", 7);
    $insert = $DB->QueryInsert("product", [
        "product_id"     => $product_id,
        "product_name"   => $product_name,
        "product_detail" => $product_detail,
        "category_id"    => $category_id,
        "reserve_id"     => $reserve_id,
        "product_status" => 1
    ]);

    if (!$insert) {
        echo json_encode([
            "data"    => "n",
            "status"  => "error",
            "title"   => "ไม่สำเร็จ",
            "message" => "ไม่สามารถบันทึกข้อมูลสินค้าได้ กรุณาลองใหม่อีกครั้ง",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($img_name !== "") {
        $DB->QueryInsert("img", [
            "img_id"     => $img_id,
            "img_name"   => $img_name,
            "img_main"   => "1",
            "product_id" => $product_id
        ]);
    }

    $insertedVariants = 0;
    foreach ($validVariants as $v) {
        $parsed = parseSize((string)($v["size_shirt"] ?? ""));
        $variant_id = $DB->QueryMaxId("variants", "variant_id");
        $vOk = $DB->QueryInsert("variants", [
            "variant_id"     => $variant_id,
            "product_api_id" => (string)($v["id"] ?? ""),
            "variant_style"  => (string)($v["style_shirt"] ?? ""),
            "variant_color"  => (string)($v["color_shirt"] ?? ""),
            "variant_size"   => $parsed["size_shirt"],
            "size_detail"    => $parsed["size_detail"],
            "size_order"     => clampSizeOrder($parsed["size_order"]),
            "variant_price"  => (float)($v["price"] ?? 0),
            "variant_sale"   => (float)($v["price_sale"] ?? 0),
            "variant_stock"  => (int)($v["number_stock"] ?? 0),
            "variant_img"    => (string)($v["picture"] ?? ""),
            "product_id"     => $product_id,
            "img_id"         => 0
        ]);
        if ($vOk) {
            $insertedVariants++;
        }
    }

    echo json_encode([
        "data"           => "y",
        "status"         => "success",
        "product_id"     => $product_id,
        "variant_count"  => $insertedVariants,
        "title"          => "เพิ่มสินค้าสำเร็จ",
        "message"        => "สร้างสินค้า \"{$product_name}\" พร้อมตัวเลือก {$insertedVariants} รายการเรียบร้อยแล้ว",
        "icon"           => "success"
    ], JSON_UNESCAPED_UNICODE);
