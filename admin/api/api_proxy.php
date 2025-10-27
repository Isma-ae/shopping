<?php
header("Content-Type: application/json; charset=UTF-8");
include("../../config/all.php");

$apiUrl = "https://oarsmart.oas.psu.ac.th/preview/ws_shirt";
$apiKey = "OAR123456";

// ===== เรียก API =====
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode([
        "status" => "error",
        "message" => "ไม่สามารถเชื่อมต่อ API ได้"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = json_decode($response, true);
if (!isset($result["data"]) || !is_array($result["data"])) {
    echo json_encode([
        "status" => "error",
        "message" => "ข้อมูลจาก API ไม่ถูกต้อง"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== ดึง ID ทั้งหมดจากฐานข้อมูล =====
$dbProducts = $DB->QueryObj("SELECT product_api_id FROM variants");
$existingIds = array_column($dbProducts, "product_api_id");

// ===== กรองเฉพาะสินค้าที่ไม่มีในฐานข้อมูล =====
$newItems = array_filter($result["data"], function ($item) use ($existingIds) {
    return !in_array($item["id"], $existingIds);
});

// ===== เรียงลำดับ id มากไปน้อย =====
usort($newItems, function ($a, $b) {
    return $b['id'] <=> $a['id'];
});

// ===== แสดงเฉพาะที่ไม่มีในฐานข้อมูล =====
echo json_encode([
    "status" => "success",
    "count" => count($newItems),
    "data" => array_values($newItems)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);