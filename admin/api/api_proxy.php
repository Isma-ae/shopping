<?php
header("Content-Type: application/json; charset=UTF-8");
include("../../config/all.php");

if (!isset($_SESSION["user_info"]) || ($_SESSION["user_info"]["role"] ?? "") !== "admin") {
    echo json_encode([
        "status"  => "error",
        "message" => "ไม่มีสิทธิ์เข้าถึง กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ",
        "count"   => 0,
        "data"    => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiUrl = "https://oarsmart.oas.psu.ac.th/preview/ws_shirt";
$apiKey = "OAR123456";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 25);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode([
        "status"  => "error",
        "message" => "ไม่สามารถเชื่อมต่อระบบคลังสินค้าได้" . ($curlErr ? " ({$curlErr})" : ""),
        "count"   => 0,
        "data"    => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = json_decode($response, true);
if (!isset($result["data"]) || !is_array($result["data"])) {
    echo json_encode([
        "status"  => "error",
        "message" => "รูปแบบข้อมูลจาก API ไม่ถูกต้อง",
        "count"   => 0,
        "data"    => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$dbProducts = $DB->QueryObj("SELECT product_api_id FROM variants WHERE product_api_id IS NOT NULL AND product_api_id != ''");
$existingMap = [];
foreach ($dbProducts as $row) {
    $existingMap[(string)$row["product_api_id"]] = true;
}

$newItems = array_filter($result["data"], function ($item) use ($existingMap) {
    $id = isset($item["id"]) ? (string)$item["id"] : "";
    return $id !== "" && !isset($existingMap[$id]);
});

usort($newItems, function ($a, $b) {
    return (int)($b["id"] ?? 0) <=> (int)($a["id"] ?? 0);
});

echo json_encode([
    "status" => "success",
    "count"  => count($newItems),
    "data"   => array_values($newItems)
], JSON_UNESCAPED_UNICODE);
