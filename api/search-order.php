<?php
include("../config/all.php");

$sql = "SELECT * FROM orders 
        LEFT JOIN tb_address ON orders.address_id = tb_address.id
        LEFT JOIN provinces ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported ON orders.transported_id = transported.transported_id
        WHERE orders.order_no = '".$DB->Escape($_POST["order_no"])."'";

$orders = $DB->QueryObj($sql);

foreach ($orders as &$order) {
    $sql2 = "SELECT * FROM order_detail
            INNER JOIN variants ON order_detail.variant_id = variants.variant_id
            LEFT JOIN img ON variants.img_id = img.img_id
            WHERE order_id = '".$order["order_id"]."'";
    $order["details"] = $DB->QueryObj($sql2);
}

echo json_encode($orders);
?>