<?php
    include("../config/all.php");
    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'check_ref'	    : check_ref(); 	        break;
        case 'change_status'	: change_status(); 	    break;
		default: break;
	}

    function check_ref() {
        global $con;
        $order_ref1 = $con->Escape($_POST["order_ref1"]);
        $order_ref2 = $con->Escape($_POST["order_ref2"]);
        $check = "SELECT * FROM payment_confirm	WHERE billPaymentRef1 = '$order_ref1' AND billPaymentRef2 = '$order_ref2' AND billPaymentRef3 = 'OAR00026'";
        $have = $con->QueryObj($check);
        if (sizeof($have) > 0) {
            echo json_encode([
                    "data"=>'t'
                ]);
        } else {
            echo json_encode([
                    "data"=>'f'
                ]);
        }
    }

    function change_status() {
        global $DB;
        $order_id = $DB->Escape($_POST["order_id"]);
        $ref1 = $DB->Escape($_POST["ref1"]);
        $change = $DB->QueryUpdate("orders",[
            "order_status" => 'ชำระเงิน',
            "status_id" => 2,
            "ref1" => $ref1,
            "update_date" => date("Y-m-d H:i:s")
        ],"order_no = '".$order_id."'");
        if ($change) {
            echo json_encode([
                    "data"=>'t'
                ]);
        } else {
            echo json_encode([
                    "data"=>'f'
                ]);
        }
    }
?>