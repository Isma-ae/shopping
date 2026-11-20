<?php

    include("../../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        echo json_encode([
            "status"  => "error",
            "data"    => [],
            "title"   => "ไม่มีสิทธิ์เข้าถึง",
            "message" => "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
            "icon"    => "error"
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'select_image' : select_image(); break;
        case 'upload_image' : upload_image(); break;
        case 'delete_image' : delete_image(); break;
        case 'change_main'  : change_main();  break;
        case 'select_stock' : select_stock(); break;
        case 'edit_variant' : edit_variant(); break;
        default: break;
    }

    function isProductImgLocalEnv() {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($httpHost, ['localhost', '127.0.0.1'], true)
            || strpos($httpHost, 'localhost:') === 0
            || strpos($httpHost, '127.0.0.1:') === 0;
    }

    function productImgSaveUpload($file, $rename) {
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

        if (!isProductImgLocalEnv()) {
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

    function productImgDeleteFile($filename) {
        global $host, $user, $pass;
        if (empty($filename)) return;
        $safeName = basename((string)$filename);
        $localDir = "../../file/product/";
        $ftpDir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/product/";

        if (!isProductImgLocalEnv()) {
            deleteFileFTP($host, $user, $pass, $ftpDir, $safeName);
        }
        deleteFile($localDir, $safeName);
    }

    function select_image() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        if ($product_id === "") {
            echo json_encode(["data" => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pidEsc = $DB->Escape($product_id);
        $sql = "SELECT
                    i.img_id,
                    i.img_name,
                    i.img_main,
                    i.product_id,
                    COUNT(DISTINCT v.variant_color) AS used_color_count
                FROM img i
                LEFT JOIN variants v
                    ON v.img_id = i.img_id
                    AND v.product_id = i.product_id
                WHERE i.product_id = '{$pidEsc}'
                GROUP BY i.img_id, i.img_name, i.img_main, i.product_id
                ORDER BY (i.img_main = 1) DESC, i.img_id ASC";

        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function upload_image() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        if ($product_id === "" || empty($_FILES["img_name"]) || !isset($_FILES["img_name"]["name"])) {
            echo json_encode([
                "data"    => "n",
                "status"  => "error",
                "title"   => "ข้อมูลไม่ครบถ้วน",
                "message" => "กรุณาเลือกไฟล์รูปภาพที่ต้องการอัปโหลด",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc = $DB->Escape($product_id);
        $hasMainRows = $DB->QueryObj("SELECT COUNT(*) AS cnt FROM img WHERE product_id = '{$pidEsc}' AND img_main = 1");
        $hasMain = ((int)($hasMainRows[0]["cnt"] ?? 0)) > 0;

        $files = $_FILES["img_name"];
        $isMulti = is_array($files["name"]);
        $count = $isMulti ? count($files["name"]) : 1;

        $success = 0;
        $invalidCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $err = $isMulti ? ($files["error"][$i] ?? UPLOAD_ERR_NO_FILE) : ($files["error"] ?? UPLOAD_ERR_NO_FILE);
            if ($err !== UPLOAD_ERR_OK) {
                continue;
            }

            $file = [
                "name"     => $isMulti ? $files["name"][$i] : $files["name"],
                "tmp_name" => $isMulti ? $files["tmp_name"][$i] : $files["tmp_name"],
                "error"    => $err
            ];

            $img_id = $DB->QueryMaxId("img", "img_id");
            $rename = md5($img_id . "_" . microtime(true) . "_" . $i);
            $img_name = productImgSaveUpload($file, $rename);

            if ($img_name === "__INVALID_TYPE__") {
                $invalidCount++;
                continue;
            }

            if ($img_name !== "") {
                $mainFlag = $hasMain ? "2" : "1";
                $insert = $DB->QueryInsert("img", [
                    "img_id"     => $img_id,
                    "img_name"   => $img_name,
                    "img_main"   => $mainFlag,
                    "product_id" => $product_id
                ]);
                if ($insert) {
                    $success++;
                    $hasMain = true;
                }
            }
        }

        if ($success > 0) {
            $msg = "อัปโหลดรูปภาพสำเร็จ {$success} รูป";
            if ($invalidCount > 0) {
                $msg .= " (ข้ามไฟล์ที่ไม่ใช่รูปภาพ {$invalidCount} ไฟล์)";
            }
            echo json_encode([
                "data"     => "y",
                "status"   => "success",
                "uploaded" => $success,
                "title"    => "อัปโหลดสำเร็จ",
                "message"  => $msg,
                "icon"     => "success"
            ], JSON_UNESCAPED_UNICODE);
        } elseif ($invalidCount > 0) {
            echo json_encode([
                "data"    => "n",
                "status"  => "error",
                "title"   => "ชนิดไฟล์ไม่รองรับ",
                "message" => "รองรับเฉพาะไฟล์รูปภาพ JPG, PNG, GIF และ WEBP เท่านั้น",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "data"    => "n",
                "status"  => "error",
                "title"   => "อัปโหลดไม่สำเร็จ",
                "message" => "ไม่สามารถอัปโหลดรูปภาพได้ กรุณาตรวจสอบไฟล์และลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_image() {
        global $DB;
        $img_id = (int)($_POST["img_id"] ?? 0);
        if ($img_id <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสรูปภาพที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $rows = $DB->QueryObj("SELECT img_id, img_name, img_main, product_id FROM img WHERE img_id = {$img_id} LIMIT 1");
        if (empty($rows)) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่พบรูปภาพ",
                "message" => "รูปภาพนี้อาจถูกลบไปแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $target = $rows[0];
        $delete = $DB->QueryDelete("img", "img_id = {$img_id}");
        if ($delete) {
            // Unlink any variants referencing this deleted image
            $DB->QueryUpdate("variants", ["img_id" => 0], "img_id = {$img_id}");

            // Promote next remaining image as main if the deleted image was main
            if ((int)$target["img_main"] === 1 && !empty($target["product_id"])) {
                $pidEsc = $DB->Escape($target["product_id"]);
                $nextRows = $DB->QueryObj("SELECT img_id FROM img WHERE product_id = '{$pidEsc}' ORDER BY img_id ASC LIMIT 1");
                if (!empty($nextRows)) {
                    $nextId = (int)$nextRows[0]["img_id"];
                    $DB->QueryUpdate("img", ["img_main" => 1], "img_id = {$nextId}");
                }
            }

            productImgDeleteFile($target["img_name"] ?? "");

            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบรูปสินค้าเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบรูปสินค้าได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function change_main() {
        global $DB;
        $img_id = (int)($_POST["img_id"] ?? 0);
        $product_id = trim((string)($_POST["product_id"] ?? ""));

        if ($img_id <= 0 || $product_id === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสรูปภาพหรือรหัสสินค้า",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc = $DB->Escape($product_id);
        $update1 = $DB->QueryUpdate("img", ["img_main" => 2], "product_id = '{$pidEsc}'");
        $update2 = $DB->QueryUpdate("img", ["img_main" => 1], "img_id = {$img_id} AND product_id = '{$pidEsc}'");

        if ($update1 && $update2) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "ตั้งเป็นรูปสินค้าหลักเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถเปลี่ยนรูปสินค้าหลักได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function select_stock() {
        global $DB;
        $product_id = trim((string)($_POST["product_id"] ?? ""));
        if ($product_id === "") {
            echo json_encode(["data" => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pidEsc = $DB->Escape($product_id);
        $sql = "SELECT
                    COALESCE(v.variant_color, '') AS variant_color,
                    COUNT(v.variant_id) AS variant_count,
                    MAX(v.img_id) AS img_id,
                    MAX(i.img_name) AS img_name,
                    MAX(v.variant_img) AS variant_img,
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(v.variant_size), '')
                        ORDER BY CAST(COALESCE(NULLIF(v.size_order, ''), '9') AS UNSIGNED) ASC
                        SEPARATOR ', '
                    ) AS sizes_list
                FROM variants v
                LEFT JOIN img i
                    ON v.img_id = i.img_id
                WHERE v.product_id = '{$pidEsc}'
                GROUP BY v.variant_color
                ORDER BY v.variant_color ASC";

        echo json_encode([
            "data" => $DB->QueryObj($sql)
        ], JSON_UNESCAPED_UNICODE);
    }

    function edit_variant() {
        global $DB;
        $product_id    = trim((string)($_POST["product_id"] ?? ""));
        $variant_color = (string)($_POST["variant_color"] ?? "");
        $img_id        = (int)($_POST["img_id"] ?? 0);

        if ($product_id === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสสินค้า",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $pidEsc   = $DB->Escape($product_id);
        $colorEsc = $DB->Escape($variant_color);

        $update = $DB->QueryUpdate("variants", [
            "img_id" => $img_id
        ], "variant_color = '{$colorEsc}' AND product_id = '{$pidEsc}'");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => $img_id > 0 ? "บันทึกรูปประจำสีเรียบร้อยแล้ว" : "ล้างรูปประจำสีเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถอัปเดตรูปประจำสีได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
?>
