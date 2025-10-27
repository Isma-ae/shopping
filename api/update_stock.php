<?php
header("Content-Type: application/json; charset=UTF-8");

include("../config/all.php"); // <<-- เรียกไฟล์ที่มีตัวแปร $DB

$apiUrl = "https://oarsmart.oas.psu.ac.th/preview/ws_shirt";
$apiKey = "OAR123456";

// ====== เรียก API ======
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // API นี้ต้อง POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    exit;
}

$result = json_decode($response, true);
if (!isset($result["data"]) || !is_array($result["data"])) {
    exit;
}

// ====== ลูปอัปเดตสินค้า ======
$updated = 0;
foreach ($result["data"] as $item) {
    if (!isset($item["id"])) continue;

    $id = $item["id"];
    $data = [
        "variant_price" => $item["price"] ?? null,
        "variant_sale" => $item["price_sale"] ?? null,
        "variant_stock" => $item["number_stock"] ?? null
    ];

    // อัปเดตเฉพาะถ้ามี id ในฐานข้อมูล
    $check = $DB->QueryObj("SELECT product_api_id FROM variants WHERE product_api_id = '".$DB->Escape($id)."' ");
    if (!empty($check)) {
        $DB->QueryUpdate("variants", $data, "product_api_id = '".$DB->Escape($id)."'");
        $updated++;
    }
}
