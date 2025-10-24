<?php
header("Content-Type: application/json; charset=UTF-8");

$apiUrl = "https://oarsmart.oas.psu.ac.th/preview/ws_shirt";
$apiKey = "OAR123456";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // ต้องเป็น POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
$response = curl_exec($ch);
curl_close($ch);
if ($response !== false) {
    $result = json_decode($response, true);
    if (isset($result["data"]) && is_array($result["data"])) {
        usort($result["data"], function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "data" => "ไม่สามารถเชื่อมต่อ API ได้"
    ], JSON_UNESCAPED_UNICODE);
}