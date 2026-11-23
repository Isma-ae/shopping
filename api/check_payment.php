<?php
    include("../config/all.php");
    header("Content-Type: application/json; charset=utf-8");

    $fn = isset($_POST["fn"]) ? $_POST["fn"] : "";
    switch ($fn) {
        case 'check_ref'     : check_ref();     break;
        case 'change_status' : change_status(); break;
        default:
            echo json_encode(["data" => "f", "message" => "Invalid action"], JSON_UNESCAPED_UNICODE);
            break;
    }

    function hasConfirmedPayment($ref1, $ref2) {
        global $con;
        $ref1 = trim((string)$ref1);
        $ref2 = trim((string)$ref2);
        if ($ref1 === "" || $ref2 === "") {
            return false;
        }
        $r1 = $con->Escape($ref1);
        $r2 = $con->Escape($ref2);
        $check = "SELECT 1 FROM payment_confirm
                  WHERE billPaymentRef1 = '{$r1}'
                    AND billPaymentRef2 = '{$r2}'
                    AND billPaymentRef3 = 'OAR00026'
                  LIMIT 1";
        $have = $con->QueryObj($check);
        return is_array($have) && count($have) > 0;
    }

    function check_ref() {
        $order_ref1 = $_POST["order_ref1"] ?? "";
        $order_ref2 = $_POST["order_ref2"] ?? "";
        if (hasConfirmedPayment($order_ref1, $order_ref2)) {
            echo json_encode(["data" => "t"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["data" => "f"], JSON_UNESCAPED_UNICODE);
        }
    }

    function change_status() {
        global $DB;
        $orderNo = trim((string)($_POST["order_id"] ?? ""));
        $ref1 = trim((string)($_POST["ref1"] ?? ""));
        $ref2 = trim((string)($_POST["ref2"] ?? ""));

        if ($orderNo === "" || $ref1 === "" || $ref2 === "") {
            echo json_encode(["data" => "f", "message" => "ข้อมูลอ้างอิงไม่ครบถ้วน"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // ยืนยันกับฐานข้อมูล payment_confirm จริงก่อนเปลี่ยนสถานะ (ป้องกันการยิง API เปลี่ยนสถานะโดยไม่จ่ายเงิน)
        if (!hasConfirmedPayment($ref1, $ref2)) {
            echo json_encode(["data" => "f", "message" => "ไม่พบข้อมูลยืนยันการชำระเงินจากธนาคาร"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $orderNoEsc = $DB->Escape($orderNo);
        $existing = $DB->QueryObj("SELECT order_id, status_id, ref1 FROM orders WHERE order_no = '{$orderNoEsc}' LIMIT 1");
        if (empty($existing)) {
            echo json_encode(["data" => "f", "message" => "ไม่พบคำสั่งซื้อ"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $curStatus = (int)($existing[0]["status_id"] ?? 0);
        // หากอัปเดตไปแล้วด้วย ref1 เดียวกัน ถือว่าสำเร็จแล้ว (idempotent)
        if ($curStatus === 2 && (string)($existing[0]["ref1"] ?? "") === $ref1) {
            echo json_encode(["data" => "t"], JSON_UNESCAPED_UNICODE);
            exit();
        }
        if ($curStatus !== 1) {
            echo json_encode(["data" => "f", "message" => "สถานะคำสั่งซื้อไม่สามารถอัปเดตได้"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // QueryUpdate ทำการ Escape ค่าใน array ให้เอง ไม่ต้อง Escape ซ้ำ
        $change = $DB->QueryUpdate("orders", [
            "order_status" => "ชำระเงิน",
            "status_id"    => 2,
            "ref1"         => $ref1,
            "update_date"  => date("Y-m-d H:i:s")
        ], "order_no = '{$orderNoEsc}' AND status_id = 1");

        if ($change) {
            echo json_encode(["data" => "t"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["data" => "f", "message" => "ไม่สามารถอัปเดตสถานะคำสั่งซื้อได้"], JSON_UNESCAPED_UNICODE);
        }
    }
