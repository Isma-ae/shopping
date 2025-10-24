<?php

    include("../config/all.php");

    $fn = isset( $_POST["fn"] ) ? $_POST["fn"] : "";
	switch ($fn) {
        case 'select_size'	: select_size(); 	    break;
        case 'select_price'	: select_price(); 	    break;
		default: break;
	}

    function select_size() {
        global $DB;
        $sql = "SELECT variant_size, size_detail
                FROM variants
                WHERE MD5(product_id) = '".$_POST["product_id"]."'
                    AND variant_color = '".$_POST["variant_color"]."'
                GROUP BY variant_size
                ORDER BY size_order";
        $return = array();
		$return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }

    function select_price() {
        global $DB;
        $sql = "SELECT variant_sale
                FROM variants
                WHERE 
                    MD5(product_id) = '".$_POST["product_id"]."'
                    AND variant_color = '".$_POST["variant_color"]."'
                    AND variant_size = '".$_POST["variant_size"]."'";
        $return["data"] = $DB->QueryObj($sql);
		echo json_encode( $return );
    }