<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

// โหลดค่า default ของ mPDF
$defaultConfig = (new ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

// ✅ สร้างอ็อบเจกต์ mPDF
$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/fonts']),
    'fontdata' => $fontData + [
        'thsarabunnew' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew-Bold.ttf',
        ]
    ],
    'default_font' => 'thsarabunnew',
    'format' => 'A4', // A6 (1/4 A4)
    'margin_left' => 8,
    'margin_right' => 8,
    'margin_top' => 8,
    'margin_bottom' => 8
]);

include("../config/all.php");

$order_id = $_GET["order_id"] ?? 0; // ✅ เพิ่มตัวแปร $order_id
$sql = "SELECT * FROM orders 
        LEFT JOIN tb_address ON orders.address_id = tb_address.id
        LEFT JOIN provinces ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported ON orders.transported_id = transported.transported_id
        WHERE order_id = '" . $order_id . "'";
$obj  = $DB->QueryObj($sql);

if (!$obj || count($obj) == 0) {
    die("ไม่พบข้อมูลการจัดส่ง");
}

$address = trim(
    $obj[0]["address_at"] . ' ' .
    'ตำบล' . $obj[0]["subdistrict_name_in_thai"] . ' ' .
    'อำเภอ' . $obj[0]["district_name_in_thai"] . ' ' .
    'จังหวัด' . $obj[0]["province_name_in_thai"]
);

// ✅ ลายเซ็น (หากต้องการ)
$signature = "หัวหน้างาน";

$stylesheet = "
    body { font-family: 'thsarabunnew'; font-size: 16px; }
    h3 { margin: 0; padding: 0; font-size: 18px; }
    .label-box {
        width: 300px;   /* A6 กว้าง */
        height: 160px;  /* A6 สูง */
        border: 2px dashed #000;
        padding: 20px;
        box-sizing: border-box;
        margin: 20mm auto; /* จัดกึ่งกลางหน้า */
    }
    .address { margin-top: 6px; line-height: 1.5; }
    .phone { margin-top: 6px; font-weight: bold; }
    .zip { margin-top: 10px; font-size: 20px; text-align: right; font-weight: bold; }
";

$html = '
<div class="label-box">
    <h3>ผู้รับ:</h3>
    <div class="address">
        ' . nl2br(htmlspecialchars($obj[0]['name'])) . '<br>
        ' . nl2br(htmlspecialchars($address)) . '
    </div>
    <div class="phone">โทร: ' . htmlspecialchars($obj[0]['phone']) . '</div>
    <div class="zip">รหัสไปรษณีย์: ' . htmlspecialchars($obj[0]["zip_code"]) . '</div>
</div>';

// ✅ เขียน HTML ลง PDF
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 2);

// ✅ แสดงผล PDF ในเบราว์เซอร์
$mpdf->Output('label_' . $order_id . '.pdf', 'I');