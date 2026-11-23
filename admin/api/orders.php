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
        case 'select_count'    : select_count();    break;
        case 'select_order'    : select_order();    break;
        case 'update_shipping' : update_shipping(); break;
        case 'update_receipt'  : update_receipt();  break;
        case 'update_status'   : update_status();   break;
        case 'delete_order'    : delete_order();    break;
        default: break;
    }

    function select_count() {
        global $DB;
        $sql = "SELECT COUNT(order_id) AS count_status FROM orders";
        $return["count1"] = $DB->QueryObj($sql . " WHERE status_id = 1");
        $return["count2"] = $DB->QueryObj($sql . " WHERE status_id = 2");
        $return["count3"] = $DB->QueryObj($sql . " WHERE status_id = 3");
        $return["count4"] = $DB->QueryObj($sql . " WHERE status_id = 4");
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function select_order() {
        global $DB;
        $statusId = (int)($_POST["status_id"] ?? 1);
        $shippingType = (int)($_POST["shipping_type"] ?? 0);

        $where = "WHERE orders.status_id = {$statusId}";
        if (in_array($shippingType, [1, 2, 3], true)) {
            $where .= " AND orders.shipping_type = {$shippingType}";
        }

        $sql = "SELECT
            orders.order_id,
            orders.order_no,
            orders.user_id,
            COALESCE(users.name, '') AS name,
            COALESCE(users.email, '') AS user_email,
            COALESCE(tb_address.name, '') AS recipient_name,
            COALESCE(tb_address.phone, '') AS recipient_phone,
            COALESCE(orders.total_price, 0) AS total_price,
            COALESCE(orders.shipping_price, 0) AS shipping_price,
            orders.status_id,
            orders.shipping_type,
            COALESCE(orders.shipping_name, '') AS shipping_name,
            COALESCE(orders.shipping_phone, '') AS shipping_phone,
            COALESCE(orders.shipping_department, '') AS shipping_department,
            COALESCE(orders.receipt_id, 0) AS receipt_id,
            COALESCE(orders.receipt_link, '') AS receipt_link,
            COALESCE(orders.create_date, '') AS create_date,
            COALESCE(tb_address.address_at, '') AS address_at,
            COALESCE(subdistricts.subdistrict_name_in_thai, '') AS subdistrict_name_in_thai,
            COALESCE(districts.district_name_in_thai, '') AS district_name_in_thai,
            COALESCE(provinces.province_name_in_thai, '') AS province_name_in_thai,
            COALESCE(subdistricts.zip_code, '') AS zip_code,
            COALESCE(transported.transported_name, '') AS transported_name,
            COALESCE(orders.parcel_number, '') AS parcel_number,
            COALESCE(od_agg.total_qty, 0) AS total_qty,
            COALESCE(od_agg.item_count, 0) AS item_count,
            COALESCE(ev_agg.evidence_count, 0) AS evidence_count
        FROM orders
        LEFT JOIN users
            ON orders.user_id = users.id
        LEFT JOIN tb_address
            ON orders.address_id = tb_address.id
        LEFT JOIN provinces
            ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts
            ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts
            ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported
            ON orders.transported_id = transported.transported_id
        LEFT JOIN (
            SELECT
                order_id,
                COUNT(detail_id) AS item_count,
                COALESCE(SUM(qty), 0) AS total_qty
            FROM order_detail
            GROUP BY order_id
        ) od_agg
            ON orders.order_id = od_agg.order_id
        LEFT JOIN (
            SELECT
                order_no,
                COUNT(evidence_id) AS evidence_count
            FROM evidence
            GROUP BY order_no
        ) ev_agg
            ON orders.order_no = ev_agg.order_no
        {$where}
        ORDER BY orders.order_id DESC";

        $return = [];
        $return["data"] = $DB->QueryObj($sql);
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    function update_shipping() {
        global $DB;
        $orderId = (int)($_POST["order_id"] ?? 0);
        $transportedId = (int)($_POST["transported_id"] ?? 0);
        $parcelNumber = trim((string)($_POST["parcel_number"] ?? ""));

        $update = $DB->QueryUpdate("orders", [
            "transported_id" => $transportedId,
            "parcel_number"  => $parcelNumber
        ], "order_id = {$orderId}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "บันทึกข้อมูลจัดส่งเรียบร้อย",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "บันทึกข้อมูลจัดส่งไม่สำเร็จ",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function update_receipt() {
        global $DB;
        $orderId = (int)($_POST["order_id"] ?? 0);
        $receiptLink = trim((string)($_POST["receipt_link"] ?? ""));

        $update = $DB->QueryUpdate("orders", [
            "receipt_link" => $receiptLink
        ], "order_id = {$orderId}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "บันทึกใบเสร็จรับเงินเรียบร้อย",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "บันทึกใบเสร็จรับเงินไม่สำเร็จ",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function update_status() {
        global $DB;
        $orderId = (int)($_POST["order_id"] ?? 0);
        $statusId = (int)($_POST["status_id"] ?? 1);
        $statusLabels = [
            1 => "รอการชำระเงิน",
            2 => "รอจัดส่ง",
            3 => "จัดส่งแล้ว",
            4 => "ยกเลิก"
        ];
        $statusText = $statusLabels[$statusId] ?? "รอการชำระเงิน";

        $update = $DB->QueryUpdate("orders", [
            "status_id"    => $statusId,
            "order_status" => $statusText,
            "update_date"  => date("Y-m-d H:i:s")
        ], "order_id = {$orderId}");

        if ($update) {
            echo json_encode([
                "status"  => "success",
                "title"   => "สำเร็จ",
                "message" => "บันทึกสถานะเรียบร้อย",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สำเร็จ",
                "message" => "บันทึกสถานะไม่สำเร็จ",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_order() {
        global $DB, $host, $user, $pass, $isLocal;
        $orderId = (int)($_POST["order_id"] ?? 0);
        if ($orderId <= 0) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ข้อมูลไม่ถูกต้อง",
                "message" => "ไม่พบรหัสคำสั่งซื้อที่ต้องการลบ",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $rows = $DB->QueryObj("SELECT order_id, order_no, status_id FROM orders WHERE order_id = {$orderId} LIMIT 1");
        if (empty($rows)) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่พบคำสั่งซื้อ",
                "message" => "ไม่พบคำสั่งซื้อนี้ในระบบ หรืออาจถูกลบไปแล้ว",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $order = $rows[0];
        if ((int)($order["status_id"] ?? 0) !== 4) {
            echo json_encode([
                "status"  => "error",
                "title"   => "ไม่สามารถลบได้",
                "message" => "สามารถลบได้เฉพาะรายการที่มีสถานะ \"ยกเลิก\" เท่านั้น",
                "icon"    => "warning"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $orderNo = (string)($order["order_no"] ?? "");
        $orderNoEsc = $DB->Escape($orderNo);

        // ลบหลักฐานการโอนเงิน (ถ้ามีแนบไว้กับคำสั่งซื้อที่ยกเลิกนี้)
        if ($orderNo !== "") {
            $evRows = $DB->QueryObj("SELECT evidence_slip FROM evidence WHERE order_no = '{$orderNoEsc}'");
            $DB->QueryDelete("evidence", "order_no = '{$orderNoEsc}'");
            foreach ($evRows as $ev) {
                $slipName = basename((string)($ev["evidence_slip"] ?? ""));
                if ($slipName === "") continue;
                $localFile = "../../file/evidence/" . $slipName;
                if (file_exists($localFile) && is_file($localFile)) {
                    @unlink($localFile);
                }
                if (empty($isLocal)) {
                    @deleteFileFTP($host, $user, $pass, "/home/httpd/vhost/shopping.oas.psu.ac.th/file/evidence/", $slipName);
                }
            }
        }

        // ลบรายละเอียดสินค้าในคำสั่งซื้อและลบคำสั่งซื้อ
        $DB->QueryDelete("order_detail", "order_id = {$orderId}");
        $deleted = $DB->QueryDelete("orders", "order_id = {$orderId} AND status_id = 4");

        if ($deleted) {
            echo json_encode([
                "status"  => "success",
                "title"   => "ลบคำสั่งซื้อสำเร็จ",
                "message" => "ลบคำสั่งซื้อหมายเลข {$orderNo} ออกจากระบบเรียบร้อยแล้ว",
                "icon"    => "success"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "status"  => "error",
                "title"   => "ลบไม่สำเร็จ",
                "message" => "ไม่สามารถลบคำสั่งซื้อนี้ได้ กรุณาลองใหม่อีกครั้ง",
                "icon"    => "error"
            ], JSON_UNESCAPED_UNICODE);
        }
    }
