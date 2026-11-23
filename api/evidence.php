<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    function evidenceJson($status, $title, $message, $icon, $extra = []) {
        echo json_encode(array_merge([
            "status"  => $status,
            "title"   => $title,
            "message" => $message,
            "icon"    => $icon
        ], $extra), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $orderNo = trim((string)($_POST["order_no"] ?? ""));
    if ($orderNo === "") {
        evidenceJson("error", "กรุณากรอกเลขที่ใบสั่งซื้อ", "กรุณาระบุเลขที่ใบสั่งซื้อที่ต้องการแจ้งหลักฐานการโอนเงิน", "warning");
    }

    // ตรวจสอบว่าเลขที่ใบสั่งซื้อมีอยู่จริงในระบบ
    $orderNoEsc = $DB->Escape($orderNo);
    $orderRows = $DB->QueryObj("SELECT order_id, order_no, status_id FROM orders WHERE order_no = '{$orderNoEsc}' LIMIT 1");
    if (empty($orderRows)) {
        evidenceJson("error", "ไม่พบเลขที่ใบสั่งซื้อ", "ไม่พบคำสั่งซื้อหมายเลข {$orderNo} ในระบบ กรุณาตรวจสอบอีกครั้ง", "warning");
    }

    $orderRow = $orderRows[0];
    $statusId = (int)($orderRow["status_id"] ?? 0);
    if ($statusId === 4) {
        evidenceJson("error", "คำสั่งซื้อถูกยกเลิกแล้ว", "คำสั่งซื้อหมายเลข {$orderNo} ถูกยกเลิกไปแล้ว ไม่สามารถส่งหลักฐานได้", "warning");
    }
    if ($statusId === 2 || $statusId === 3) {
        evidenceJson("error", "ชำระเงินเรียบร้อยแล้ว", "คำสั่งซื้อหมายเลข {$orderNo} ได้รับการยืนยันการชำระเงินแล้ว", "info");
    }

    // ตรวจสอบไฟล์ที่อัปโหลด
    if (!isset($_FILES["evidence_slip"]) || !is_array($_FILES["evidence_slip"])) {
        evidenceJson("error", "กรุณาแนบหลักฐานการโอนเงิน", "กรุณาเลือกไฟล์ภาพสลิปการโอนเงินก่อนส่ง", "warning");
    }

    $file = $_FILES["evidence_slip"];
    $uploadErr = (int)($file["error"] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadErr === UPLOAD_ERR_NO_FILE) {
        evidenceJson("error", "กรุณาแนบหลักฐานการโอนเงิน", "กรุณาเลือกไฟล์ภาพสลิปการโอนเงินก่อนส่ง", "warning");
    }
    if ($uploadErr !== UPLOAD_ERR_OK) {
        $errMsg = ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE)
            ? "ขนาดไฟล์ใหญ่เกินกำหนด กรุณาใช้ไฟล์ภาพขนาดไม่เกิน 5 MB"
            : "เกิดข้อผิดพลาดระหว่างอัปโหลดไฟล์ กรุณาลองใหม่อีกครั้ง";
        evidenceJson("error", "อัปโหลดไม่สำเร็จ", $errMsg, "error");
    }

    $maxBytes = 5 * 1024 * 1024; // 5 MB
    if ((int)($file["size"] ?? 0) > $maxBytes) {
        evidenceJson("error", "ไฟล์มีขนาดใหญ่เกินไป", "กรุณาเลือกไฟล์ภาพที่มีขนาดไม่เกิน 5 MB", "warning");
    }

    $allowedExts = ["jpg", "jpeg", "png", "gif", "webp"];
    $ext = getFileType((string)($file["name"] ?? ""));
    if (!in_array($ext, $allowedExts, true)) {
        evidenceJson("error", "รูปแบบไฟล์ไม่รองรับ", "กรุณาอัปโหลดไฟล์ภาพนามสกุล JPG, PNG, WEBP หรือ GIF เท่านั้น", "warning");
    }

    $tmpName = (string)($file["tmp_name"] ?? "");
    if ($tmpName === "" || !is_uploaded_file($tmpName) || @getimagesize($tmpName) === false) {
        evidenceJson("error", "ไฟล์ภาพไม่ถูกต้อง", "ไฟล์ที่แนบไม่ใช่รูปภาพที่ถูกต้อง กรุณาเลือกไฟล์ใหม่อีกครั้ง", "warning");
    }

    $evidence_id = $DB->QueryMaxId("evidence", "evidence_id");
    $rename = md5((string)$evidence_id);
    $ftp_dir = "/home/httpd/vhost/shopping.oas.psu.ac.th/file/evidence/";
    $local_dir = "../file/evidence/";

    $evidence_slip = "";
    if (empty($isLocal)) {
        $evidence_slip = uploadFileFTP($host, $user, $pass, $ftp_dir, $file, $rename);
    }
    if ($evidence_slip === "") {
        if (!is_dir($local_dir)) {
            @mkdir($local_dir, 0775, true);
        }
        $evidence_slip = uploadFile($local_dir, $file, $rename);
    }

    if ($evidence_slip === "") {
        evidenceJson("error", "อัปโหลดไฟล์ไม่สำเร็จ", "ไม่สามารถบันทึกไฟล์หลักฐานลงเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
    }

    // QueryInsert ทำการ Escape ภายในให้อยู่แล้ว ไม่ต้อง Escape ซ้ำ
    $insert = $DB->QueryInsert("evidence", [
        "evidence_id"   => $evidence_id,
        "evidence_slip" => $evidence_slip,
        "order_no"      => $orderRow["order_no"]
    ]);

    if ($insert) {
        $redirectUrl = isset($_SESSION["user_info"])
            ? "?page=shipping_list"
            : "?page=search-order&order_no=" . rawurlencode((string)$orderRow["order_no"]);

        evidenceJson(
            "success",
            "ส่งหลักฐานสำเร็จ",
            "หลักฐานการชำระเงินสำหรับใบสั่งซื้อ " . $orderRow["order_no"] . " ถูกส่งไปยังผู้ดูแลระบบแล้ว",
            "success",
            ["redirect_url" => $redirectUrl]
        );
    } else {
        evidenceJson("error", "บันทึกข้อมูลไม่สำเร็จ", "ไม่สามารถส่งหลักฐานการชำระเงินได้ กรุณาลองใหม่อีกครั้ง", "error");
    }
