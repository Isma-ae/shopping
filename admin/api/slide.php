<?php

    include("../../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
        echo json_encode([
            "status"  => "error",
            "title"   => "ไม่มีสิทธิ์เข้าถึง",
            "message" => "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
            "icon"    => "error",
            "data"    => []
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'load_slide'   : load_slide();   break;
        case 'add_slide'    : add_slide();    break;
        case 'edit_slide'   : edit_slide();   break;
        case 'delete_slide' : delete_slide(); break;
        default: break;
    }

    function isBannerLocalEnv() {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($httpHost, ['localhost', '127.0.0.1'], true)
            || strpos($httpHost, 'localhost:') === 0
            || strpos($httpHost, '127.0.0.1:') === 0;
    }

    function bannerSaveUpload($file, $rename) {
        global $host, $user, $pass;
        if (empty($file) || !isset($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return "";
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = getFileType($file['name'] ?? '');
        if (!in_array($ext, $allowed, true)) {
            return "__INVALID_TYPE__";
        }

        $localDir = "../../file/banner/";
        $ftpDir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/banner/";

        if (!isBannerLocalEnv()) {
            $uploaded = uploadFileFTP($host, $user, $pass, $ftpDir, $file, $rename);
            if ($uploaded !== "") {
                return $uploaded;
            }
        }
        return uploadFile($localDir, $file, $rename);
    }

    function bannerDeleteUpload($filename) {
        global $host, $user, $pass;
        if (empty($filename)) return;
        $safeName = basename((string)$filename);
        $localDir = "../../file/banner/";
        $ftpDir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/banner/";

        if (!isBannerLocalEnv()) {
            deleteFileFTP($host, $user, $pass, $ftpDir, $safeName);
        }
        deleteFile($localDir, $safeName);
    }

    function load_slide() {
        global $DB;
        $rows = $DB->QueryObj("SELECT slide_id, slide_name, slide_detail, slide_img, slide_link FROM slide ORDER BY slide_id ASC");
        echo json_encode([
            "status" => "success",
            "data"   => $rows
        ], JSON_UNESCAPED_UNICODE);
    }

    function add_slide() {
        global $DB;
        $slide_id = $DB->QueryMaxId("slide", "slide_id", "sl", 11);
        $img = $_FILES['slide_img'] ?? null;

        if (!$img || ($img['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            echo json_encode([
                "status"  => "error",
                "title"   => "กรุณาเลือกรูปภาพ",
                "message" => "กรุณาเลือกไฟล์ภาพแบนเนอร์ก่อนบันทึก",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $slide_img = bannerSaveUpload($img, "slide_" . $slide_id);
        if ($slide_img === "__INVALID_TYPE__") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ชนิดไฟล์ไม่รองรับ",
                "message" => "รองรับเฉพาะไฟล์รูปภาพ JPG, PNG, GIF หรือ WEBP เท่านั้น",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        if ($slide_img === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "อัปโหลดรูปไม่สำเร็จ",
                "message" => "ไม่สามารถบันทึกไฟล์รูปภาพได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์ file/banner/",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $add = $DB->QueryInsert('slide', [
            'slide_id'     => $slide_id,
            'slide_name'   => trim((string)($_POST["slide_name"] ?? "")),
            'slide_detail' => (string)($_POST["slide_detail"] ?? ""),
            'slide_link'   => trim((string)($_POST["slide_link"] ?? "")),
            'slide_img'    => $slide_img
        ]);

        if ($add) {
            echo json_encode([
                "status"  => "success",
                "title"   => "เพิ่มแบนเนอร์สำเร็จ",
                "message" => "บันทึกแบนเนอร์ใหม่เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            bannerDeleteUpload($slide_img);
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function edit_slide() {
        global $DB;
        $slide_id = $DB->Escape(trim((string)($_POST["slide_id"] ?? "")));
        if ($slide_id === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสแบนเนอร์ที่ต้องการแก้ไข",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $oldRows = $DB->QueryObj("SELECT slide_img FROM slide WHERE slide_id = '{$slide_id}' LIMIT 1");
        $oldImg = !empty($oldRows[0]["slide_img"]) ? (string)$oldRows[0]["slide_img"] : "";

        $updateData = [
            'slide_name'   => trim((string)($_POST["slide_name"] ?? "")),
            'slide_detail' => (string)($_POST["slide_detail"] ?? ""),
            'slide_link'   => trim((string)($_POST["slide_link"] ?? ""))
        ];

        $file = $_FILES["slide_img"] ?? null;
        $hasNewFile = $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($file['name']);
        $newImg = "";

        if ($hasNewFile) {
            $newImg = bannerSaveUpload($file, "slide_" . $slide_id);
            if ($newImg === "__INVALID_TYPE__") {
                echo json_encode([
                    "status"  => "error",
                    "title"   => "ชนิดไฟล์ไม่รองรับ",
                    "message" => "รองรับเฉพาะไฟล์รูปภาพ JPG, PNG, GIF หรือ WEBP เท่านั้น",
                    "icon"    => "warning"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            if ($newImg === "") {
                echo json_encode([
                    "status"  => "error",
                    "title"   => "อัปโหลดรูปไม่สำเร็จ",
                    "message" => "ไม่สามารถอัปโหลดรูปภาพใหม่ได้",
                    "icon"    => "error"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $updateData['slide_img'] = $newImg;
        }

        $updated = $DB->QueryUpdate("slide", $updateData, "slide_id = '{$slide_id}'");
        if ($updated) {
            if ($newImg !== "" && $oldImg !== "" && $oldImg !== $newImg) {
                bannerDeleteUpload($oldImg);
            }
            echo json_encode([
                "status"  => "success",
                "title"   => "บันทึกสำเร็จ",
                "message" => "แก้ไขข้อมูลแบนเนอร์เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถแก้ไขข้อมูลแบนเนอร์ได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_slide() {
        global $DB;
        $slide_id = $DB->Escape(trim((string)($_POST["slide_id"] ?? "")));
        if ($slide_id === "") {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสแบนเนอร์ที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $obj = $DB->QueryObj("SELECT slide_img FROM slide WHERE slide_id = '{$slide_id}' LIMIT 1");
        $delete = $DB->QueryDelete("slide", "slide_id = '{$slide_id}'");
        if ($delete) {
            if (!empty($obj[0]["slide_img"])) {
                bannerDeleteUpload($obj[0]["slide_img"]);
            }
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบแบนเนอร์เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบแบนเนอร์ได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
