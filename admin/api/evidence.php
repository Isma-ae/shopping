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
        case "select_evidence": select_evidence(); break;
        case "approve_payment": approve_payment(); break;
        case "delete_evidence": delete_evidence(); break;
        default: break;
    }

    function select_evidence() {
        global $DB;
        $filter = $_POST["filter"] ?? "all";
        $where = "";
        if ($filter === "pending") {
            $where = "WHERE o.status_id = 1";
        } elseif ($filter === "verified") {
            $where = "WHERE o.status_id IN (2, 3)";
        } elseif ($filter === "other") {
            $where = "WHERE o.order_id IS NULL OR o.status_id = 4";
        }

        $sql = "SELECT
                    e.evidence_id,
                    e.evidence_slip,
                    e.order_no,
                    o.order_id,
                    COALESCE(o.total_price, 0) AS total_price,
                    COALESCE(o.shipping_price, 0) AS shipping_price,
                    o.status_id,
                    COALESCE(o.order_status, '') AS order_status,
                    o.shipping_type,
                    COALESCE(o.create_date, '') AS create_date,
                    COALESCE(NULLIF(TRIM(u.name), ''), NULLIF(TRIM(o.shipping_name), ''), NULLIF(TRIM(a.name), ''), '-') AS buyer_name,
                    COALESCE(u.email, '') AS buyer_email,
                    COALESCE(NULLIF(TRIM(o.shipping_phone), ''), NULLIF(TRIM(a.phone), ''), NULLIF(TRIM(u.user_phone), ''), '') AS buyer_phone
                FROM evidence e
                LEFT JOIN orders o
                    ON e.order_no = o.order_no
                LEFT JOIN users u
                    ON o.user_id = u.id
                LEFT JOIN tb_address a
                    ON o.address_id = a.id
                {$where}
                ORDER BY e.evidence_id DESC";
        $rows = $DB->QueryObj($sql);

        $countsRow = $DB->QueryObj("
            SELECT
                COUNT(e.evidence_id) AS total,
                SUM(CASE WHEN o.status_id = 1 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN o.status_id IN (2, 3) THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN o.order_id IS NULL OR o.status_id = 4 THEN 1 ELSE 0 END) AS other
            FROM evidence e
            LEFT JOIN orders o ON e.order_no = o.order_no
        ");
        $c = $countsRow[0] ?? [];

        echo json_encode([
            "data"   => $rows,
            "counts" => [
                "total"    => (int)($c["total"] ?? 0),
                "pending"  => (int)($c["pending"] ?? 0),
                "verified" => (int)($c["verified"] ?? 0),
                "other"    => (int)($c["other"] ?? 0)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    function approve_payment() {
        global $DB;
        $orderId = (int)($_POST["order_id"] ?? 0);
        if ($orderId <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่พบคำสั่งซื้อ",
                "message" => "ไม่พบเลขที่คำสั่งซื้อที่ตรงกับหลักฐานนี้ในระบบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $updated = $DB->QueryUpdate("orders", [
            "status_id"    => 2,
            "order_status" => "รอจัดส่ง",
            "update_date"  => date("Y-m-d H:i:s")
        ], "order_id = {$orderId}");

        if ($updated) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ยืนยันการชำระเงินสำเร็จ",
                "message" => "เปลี่ยนสถานะคำสั่งซื้อเป็น \"รอจัดส่ง\" เรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถอัปเดตสถานะคำสั่งซื้อได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_evidence() {
        global $DB;
        $evidenceId = (int)($_POST["evidence_id"] ?? 0);
        if ($evidenceId <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสหลักฐานที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $rows = $DB->QueryObj("SELECT evidence_slip FROM evidence WHERE evidence_id = {$evidenceId} LIMIT 1");
        $deleted = $DB->QueryDelete("evidence", "evidence_id = {$evidenceId}");

        if ($deleted) {
            if (!empty($rows[0]["evidence_slip"])) {
                $localFile = "../../file/evidence/" . basename((string)$rows[0]["evidence_slip"]);
                if (file_exists($localFile) && is_file($localFile)) {
                    @unlink($localFile);
                }
            }
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบสำเร็จ",
                "message" => "ลบหลักฐานการชำระเงินเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "ไม่สามารถลบหลักฐานได้",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
