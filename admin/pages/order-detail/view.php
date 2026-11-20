<?php
    if (!function_exists("odEsc")) {
        function odEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("odFormatBaht")) {
        function odFormatBaht($amount) {
            $num = (float)($amount ?? 0);
            $hasFraction = abs($num - round($num)) > 0.001;
            return "฿" . number_format($num, $hasFraction ? 2 : 0);
        }
    }

    if (!function_exists("odFormatDate")) {
        function odFormatDate($dateStr) {
            if (empty($dateStr)) return "-";
            $ts = strtotime((string)$dateStr);
            if (!$ts) return odEsc($dateStr);
            return date("d/m/", $ts) . ((int)date("Y", $ts) + 543) . date(" H:i", $ts) . " น.";
        }
    }

    $orderId = (int)($_GET["order_id"] ?? 0);
    $obj = [];
    if ($orderId > 0) {
        $sql = "SELECT
                    orders.order_id,
                    orders.order_no,
                    COALESCE(orders.ref1, '') AS ref1,
                    orders.user_id,
                    COALESCE(users.name, '') AS user_name,
                    COALESCE(users.email, '') AS user_email,
                    COALESCE(users.user_phone, '') AS user_phone,
                    COALESCE(tb_address.name, '') AS address_recipient_name,
                    COALESCE(tb_address.phone, '') AS address_recipient_phone,
                    COALESCE(orders.receipt_id, 0) AS receipt_id,
                    COALESCE(orders.receipt_name, '') AS receipt_name,
                    COALESCE(orders.receipt_number, '') AS receipt_number,
                    COALESCE(orders.receipt_address, '') AS receipt_address,
                    COALESCE(orders.receipt_link, '') AS receipt_link,
                    COALESCE(orders.total_price, 0) AS total_price,
                    COALESCE(orders.shipping_price, 0) AS shipping_price,
                    orders.status_id,
                    orders.shipping_type,
                    COALESCE(orders.shipping_name, '') AS shipping_name,
                    COALESCE(orders.shipping_phone, '') AS shipping_phone,
                    COALESCE(orders.shipping_department, '') AS shipping_department,
                    COALESCE(orders.create_date, '') AS create_date,
                    COALESCE(orders.update_date, '') AS update_date,
                    COALESCE(tb_address.address_at, '') AS address_at,
                    COALESCE(subdistricts.subdistrict_name_in_thai, '') AS subdistrict_name_in_thai,
                    COALESCE(districts.district_name_in_thai, '') AS district_name_in_thai,
                    COALESCE(provinces.province_name_in_thai, '') AS province_name_in_thai,
                    COALESCE(subdistricts.zip_code, '') AS zip_code,
                    COALESCE(orders.transported_id, 0) AS transported_id,
                    COALESCE(transported.transported_name, '') AS transported_name,
                    COALESCE(orders.parcel_number, '') AS parcel_number
                FROM orders
                LEFT JOIN users ON orders.user_id = users.id
                LEFT JOIN tb_address ON orders.address_id = tb_address.id
                LEFT JOIN provinces ON tb_address.province_id = provinces.province_id
                LEFT JOIN districts ON tb_address.district_id = districts.district_id
                LEFT JOIN subdistricts ON tb_address.subdistrict_id = subdistricts.subdistrict_id
                LEFT JOIN transported ON orders.transported_id = transported.transported_id
                WHERE orders.order_id = {$orderId}
                LIMIT 1";
        $obj = $DB->QueryObj($sql);
    }

    $order = count($obj) > 0 ? $obj[0] : null;
?>
<div class="page-inner">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <h3 class="fw-bold mb-0">รายละเอียดคำสั่งซื้อ</h3>
            <ul class="breadcrumbs mb-0">
                <li class="nav-home">
                    <a href="./">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="?page=orders">รายการสั่งซื้อ</a>
                </li>
                <?php if ($order) { ?>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#"><?= odEsc($order["order_no"]); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <a href="?page=orders" class="btn btn-sm btn-outline-secondary btn-round">
            <i class="fas fa-arrow-left me-1"></i>กลับหน้ารายการสั่งซื้อ
        </a>
    </div>

    <?php if (!$order) { ?>
        <div class="card mt-3">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="fw-bold mb-2">ไม่พบข้อมูลคำสั่งซื้อ</h4>
                <p class="text-muted mb-4">ไม่พบคำสั่งซื้อตามรหัสที่ระบุ หรือรายการอาจถูกลบออกไปแล้ว</p>
                <a href="?page=orders" class="btn btn-primary btn-round">กลับไปหน้ารายการสั่งซื้อ</a>
            </div>
        </div>
    <?php
        } else {
            $statusId = (int)$order["status_id"];
            $shippingType = (int)$order["shipping_type"];
            $statusEligible = in_array($statusId, [2, 3], true);
            $orderNoEsc = $DB->Escape((string)$order["order_no"]);

            // Fetch items with LEFT JOIN so deleted variants still show, plus fallback image and single-variant check
            $items = $DB->QueryObj("
                SELECT
                    od.detail_id,
                    od.product_name,
                    COALESCE(od.variant_color, '') AS variant_color,
                    COALESCE(od.variant_size, '') AS variant_size,
                    COALESCE(v.size_detail, '') AS size_detail,
                    COALESCE(NULLIF(od.variant_price, 0), v.variant_sale, 0) AS unit_price,
                    COALESCE(od.qty, 1) AS qty,
                    COALESCE(od.order_type, 1) AS order_type,
                    COALESCE(od.total_price, 0) AS total_price,
                    COALESCE(img.img_name, fallback_img.img_name, '') AS img_name,
                    (
                        SELECT COUNT(DISTINCT COALESCE(v2.variant_color, ''))
                        FROM variants v2
                        WHERE v2.product_id = v.product_id
                    ) AS color_count,
                    (
                        SELECT COUNT(DISTINCT CONCAT(COALESCE(v3.variant_size, ''), '|', COALESCE(v3.size_detail, '')))
                        FROM variants v3
                        WHERE v3.product_id = v.product_id
                          AND COALESCE(v3.variant_color, '') = COALESCE(v.variant_color, '')
                    ) AS size_count
                FROM order_detail od
                LEFT JOIN variants v ON od.variant_id = v.variant_id
                LEFT JOIN img ON v.img_id = img.img_id
                LEFT JOIN (
                    SELECT product_id, MIN(img_name) AS img_name
                    FROM img
                    WHERE img_main = '1'
                    GROUP BY product_id
                ) fallback_img ON v.product_id = fallback_img.product_id
                WHERE od.order_id = {$orderId}
                ORDER BY od.detail_id ASC
            ");

            // Fetch payment slips attached to this order
            $evidences = $DB->QueryObj("
                SELECT evidence_id, evidence_slip
                FROM evidence
                WHERE order_no = '{$orderNoEsc}'
                ORDER BY evidence_id DESC
            ");

            $itemsSubtotal = 0;
            $totalQty = 0;
            foreach ($items as $it) {
                $itemsSubtotal += (float)$it["total_price"];
                $totalQty += (int)$it["qty"];
            }
            $shippingPrice = (float)$order["shipping_price"];
            $grandTotal = (float)$order["total_price"];

            $statusMap = [
                1 => ["label" => "รอชำระเงิน",          "badge" => "badge-warning", "icon" => "fa-clock"],
                2 => ["label" => "ชำระแล้ว · รอจัดส่ง", "badge" => "badge-info",    "icon" => "fa-box-open"],
                3 => ["label" => "จัดส่งแล้ว",          "badge" => "badge-success", "icon" => "fa-check-circle"],
                4 => ["label" => "ยกเลิก",              "badge" => "badge-danger",  "icon" => "fa-times-circle"],
            ];
            $stInfo = $statusMap[$statusId] ?? ["label" => "ไม่ทราบสถานะ", "badge" => "badge-secondary", "icon" => "fa-question-circle"];

            // Shipping display info
            $receiverName = "";
            $receiverPhone = "";
            $receiverDept = "";
            $receiverAddress = "";
            if ($shippingType === 1) {
                $shipLabel = "รับหน้าร้าน";
                $shipBadgeClass = "od-ship-pill--pickup";
                $shipIcon = "fa-store";
                $receiverName = trim($order["shipping_name"]) !== "" ? $order["shipping_name"] : $order["user_name"];
                $receiverPhone = trim($order["shipping_phone"]) !== "" ? $order["shipping_phone"] : $order["user_phone"];
                $receiverDept = trim($order["shipping_department"]);
                $receiverAddress = "เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี";
            } elseif ($shippingType === 2) {
                $shipLabel = "ส่งภายในวิทยาเขต";
                $shipBadgeClass = "od-ship-pill--campus";
                $shipIcon = "fa-paper-plane";
                $receiverName = trim($order["shipping_name"]) !== "" ? $order["shipping_name"] : $order["user_name"];
                $receiverPhone = trim($order["shipping_phone"]);
                $receiverDept = trim($order["shipping_department"]);
            } else {
                $shipLabel = "ส่งภายนอกวิทยาเขต";
                $shipBadgeClass = "od-ship-pill--outside";
                $shipIcon = "fa-truck";
                if ((int)$order["user_id"] !== 0) {
                    $receiverName = trim($order["address_recipient_name"]) !== "" ? $order["address_recipient_name"] : $order["user_name"];
                    $receiverPhone = trim($order["address_recipient_phone"]);
                    $addrParts = [];
                    if (trim($order["address_at"]) !== "") $addrParts[] = trim($order["address_at"]);
                    if (trim($order["subdistrict_name_in_thai"]) !== "") $addrParts[] = "ตำบล" . trim($order["subdistrict_name_in_thai"]);
                    if (trim($order["district_name_in_thai"]) !== "") $addrParts[] = "อำเภอ" . trim($order["district_name_in_thai"]);
                    if (trim($order["province_name_in_thai"]) !== "") $addrParts[] = "จังหวัด" . trim($order["province_name_in_thai"]);
                    if (trim($order["zip_code"]) !== "") $addrParts[] = trim($order["zip_code"]);
                    $receiverAddress = implode(" ", $addrParts);
                } else {
                    $receiverName = trim($order["shipping_name"]);
                    $receiverPhone = trim($order["shipping_phone"]);
                    $receiverAddress = trim($order["shipping_department"]);
                }
            }
    ?>
    <input type="hidden" id="order_id" value="<?= $orderId; ?>">
    <input type="hidden" id="transported_now" value="<?= (int)$order["transported_id"]; ?>">
    <input type="hidden" id="parcel_now" value="<?= odEsc($order["parcel_number"]); ?>">
    <input type="hidden" id="receipt_now" value="<?= odEsc($order["receipt_link"]); ?>">
    <input type="hidden" id="status_now" value="<?= $statusId; ?>">

    <div class="row g-4 mt-1 align-items-start">
        <!-- Left Column: Items & Payment Evidence -->
        <div class="col-lg-8">
            <!-- Order Items Card -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-shopping-bag text-primary me-2"></i><?= odEsc($order["order_no"]); ?>
                        </h4>
                        <button type="button" class="btn btn-xs btn-light border js-copy-text"
                            data-copy="<?= odEsc($order["order_no"]); ?>" title="คัดลอกเลขที่ใบสั่งซื้อ">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <span class="badge badge-light border text-dark">
                        <?= count($items); ?> รายการ · <?= $totalQty; ?> ชิ้น
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 od-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 44px;" class="ps-4">#</th>
                                    <th>สินค้า</th>
                                    <th style="width: 120px;" class="text-end">ราคา/หน่วย</th>
                                    <th style="width: 85px;" class="text-center">จำนวน</th>
                                    <th style="width: 130px;" class="text-end pe-4">ราคารวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $i => $item) {
                                    $imgName = trim((string)$item["img_name"]);
                                    $imgSrc = $imgName !== "" ? "../file/product/" . rawurlencode($imgName) : "../images/icons/OARShopping.png";
                                    $colorCnt = (int)($item["color_count"] ?? 0);
                                    $sizeCnt = (int)($item["size_count"] ?? 0);
                                    $colorVal = trim((string)$item["variant_color"]);
                                    $sizeVal = trim((string)$item["variant_size"]);
                                    $sizeDet = trim((string)$item["size_detail"]);
                                    $isReserve = ((int)$item["order_type"] === 2);
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $i + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= odEsc($imgSrc); ?>" alt="" class="od-item-thumb"
                                                onerror="this.onerror=null;this.src='../images/icons/OARShopping.png';">
                                            <div>
                                                <div class="fw-semibold text-dark"><?= odEsc($item["product_name"]); ?></div>
                                                <?php if ($colorCnt > 1 || $sizeCnt > 1 || $isReserve) { ?>
                                                <div class="od-variant-row">
                                                    <?php if ($colorCnt > 1 && $colorVal !== "") { ?>
                                                        <span class="od-var-pill">สี: <?= odEsc($colorVal); ?></span>
                                                    <?php } ?>
                                                    <?php if ($sizeCnt > 1 && $sizeVal !== "") {
                                                        $szText = $sizeVal . ($sizeDet !== "" ? " ({$sizeDet})" : "");
                                                    ?>
                                                        <span class="od-var-pill">ขนาด: <?= odEsc($szText); ?></span>
                                                    <?php } ?>
                                                    <?php if ($isReserve) { ?>
                                                        <span class="badge badge-warning">สินค้าสั่งจอง</span>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= odFormatBaht($item["unit_price"]); ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-light border text-dark px-2"><?= (int)$item["qty"]; ?></span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-primary"><?= odFormatBaht($item["total_price"]); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Totals Summary -->
                    <div class="od-totals-box">
                        <div class="od-total-row">
                            <span>รวมค่าสินค้า (<?= $totalQty; ?> ชิ้น)</span>
                            <strong><?= odFormatBaht($itemsSubtotal); ?></strong>
                        </div>
                        <div class="od-total-row">
                            <span>ค่าจัดส่ง</span>
                            <strong class="<?= $shippingPrice <= 0 ? 'text-success' : ''; ?>">
                                <?= $shippingPrice <= 0 ? "ฟรี" : odFormatBaht($shippingPrice); ?>
                            </strong>
                        </div>
                        <div class="od-total-row od-total-row--grand">
                            <span>ยอดชำระเงินทั้งหมด</span>
                            <strong><?= odFormatBaht($grandTotal); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info & Attached Slips Card -->
            <?php if ($statusEligible || trim($order["ref1"]) !== "" || count($evidences) > 0) { ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar text-success me-2"></i>ข้อมูลการชำระเงินและสลิป
                    </h4>
                    <?php if ($statusEligible || trim($order["ref1"]) !== "") { ?>
                    <a href="https://scb.oas.psu.ac.th/" target="_blank" rel="noopener noreferrer"
                        class="btn btn-sm btn-outline-success btn-round">
                        <i class="fas fa-external-link-alt me-1"></i>ตรวจสอบยอดผ่าน SCB
                    </a>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <?php if (trim($order["ref1"]) !== "") { ?>
                    <div class="od-ref-box mb-3">
                        <span class="text-muted">หมายเลขอ้างอิง (Ref1):</span>
                        <strong class="text-success ms-2"><?= odEsc($order["ref1"]); ?></strong>
                        <button type="button" class="btn btn-xs btn-light border ms-2 js-copy-text"
                            data-copy="<?= odEsc($order["ref1"]); ?>" title="คัดลอก Ref1">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <?php } ?>

                    <?php if (count($evidences) > 0) { ?>
                    <div class="mb-2 fw-semibold">หลักฐานการโอนเงินที่ลูกค้าแนบ (<?= count($evidences); ?> รายการ)</div>
                    <div class="od-slip-grid">
                        <?php foreach ($evidences as $ev) {
                            $slipUrl = "../file/evidence/" . rawurlencode((string)$ev["evidence_slip"]);
                        ?>
                        <button type="button" class="od-slip-card js-zoom-slip" data-slip-url="<?= odEsc($slipUrl); ?>"
                            title="คลิกเพื่อดูสลิปขนาดใหญ่">
                            <img src="<?= odEsc($slipUrl); ?>" alt="หลักฐานการชำระเงิน"
                                onerror="this.onerror=null;this.parentNode.innerHTML='<div class=\'p-3 text-muted small\'><i class=\'fas fa-image d-block mb-1\'></i>ไม่พบไฟล์สลิป</div>';">
                            <span class="od-slip-zoom-hint"><i class="fas fa-search-plus me-1"></i>ขยายดู</span>
                        </button>
                        <?php } ?>
                    </div>
                    <?php } elseif ($statusId === 1) { ?>
                    <p class="text-muted mb-0">ยังไม่มีหลักฐานการโอนเงินที่ลูกค้าแนบเข้ามา</p>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Right Column: Status Actions, Shipping Info, Receipt Info -->
        <div class="col-lg-4">
            <!-- Status & Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">สถานะและการจัดการ</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted">สถานะปัจจุบัน</span>
                        <span class="badge <?= $stInfo["badge"]; ?> fs-6 px-3 py-2">
                            <i class="fas <?= $stInfo["icon"]; ?> me-1"></i><?= $stInfo["label"]; ?>
                        </span>
                    </div>

                    <div class="od-meta-rows mb-3">
                        <div class="od-meta-row">
                            <span>วันที่สั่งซื้อ</span>
                            <strong><?= odFormatDate($order["create_date"]); ?></strong>
                        </div>
                        <?php if (!empty($order["update_date"]) && $order["update_date"] !== "0000-00-00 00:00:00") { ?>
                        <div class="od-meta-row">
                            <span>อัปเดตล่าสุด</span>
                            <strong><?= odFormatDate($order["update_date"]); ?></strong>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="update-status">
                            <i class="fas fa-exchange-alt me-2"></i>เปลี่ยนสถานะคำสั่งซื้อ
                        </button>

                        <?php if ($statusEligible && $shippingType === 3) { ?>
                        <button type="button" class="btn btn-warning" id="update-shipping">
                            <i class="fas fa-truck me-2"></i>อัปเดตข้อมูลจัดส่ง / เลขพัสดุ
                        </button>
                        <?php } ?>

                        <?php if ($statusEligible) { ?>
                        <button type="button" class="btn btn-success" id="update-receipt">
                            <i class="fas fa-file-upload me-2"></i>อัปเดตลิงก์ใบเสร็จรับเงิน
                        </button>
                        <?php } ?>

                        <?php if ($statusId === 2 && $shippingType === 3) { ?>
                        <a class="btn btn-outline-success" href="print.php?order_id=<?= $orderId; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-print me-2"></i>พิมพ์ที่อยู่จัดส่ง
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Shipping & Receiver Card -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">ข้อมูลการจัดส่ง</h4>
                    <span class="od-ship-pill <?= $shipBadgeClass; ?>">
                        <i class="fas <?= $shipIcon; ?> me-1"></i><?= $shipLabel; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="od-info-group">
                        <div class="od-info-item">
                            <span class="od-info-label">ชื่อผู้รับ</span>
                            <div class="od-info-val fw-semibold"><?= $receiverName !== "" ? odEsc($receiverName) : "-"; ?></div>
                        </div>

                        <?php if ($receiverPhone !== "") { ?>
                        <div class="od-info-item">
                            <span class="od-info-label">เบอร์โทรศัพท์</span>
                            <div class="od-info-val">
                                <a href="tel:<?= odEsc($receiverPhone); ?>"><?= odEsc($receiverPhone); ?></a>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if (trim($order["user_email"]) !== "") { ?>
                        <div class="od-info-item">
                            <span class="od-info-label">อีเมลบัญชีผู้สั่งซื้อ</span>
                            <div class="od-info-val"><?= odEsc($order["user_email"]); ?></div>
                        </div>
                        <?php } ?>

                        <?php if ($receiverDept !== "") { ?>
                        <div class="od-info-item">
                            <span class="od-info-label">คณะ / หน่วยงาน</span>
                            <div class="od-info-val"><?= odEsc($receiverDept); ?></div>
                        </div>
                        <?php } ?>

                        <?php if ($receiverAddress !== "") { ?>
                        <div class="od-info-item">
                            <span class="od-info-label"><?= $shippingType === 1 ? "จุดรับสินค้า" : "ที่อยู่จัดส่ง"; ?></span>
                            <div class="od-info-val"><?= odEsc($receiverAddress); ?></div>
                        </div>
                        <?php } ?>

                        <?php if ($shippingType === 3) { ?>
                        <div class="od-info-item pt-2 border-top">
                            <span class="od-info-label">ข้อมูลพัสดุ</span>
                            <?php if (trim($order["transported_name"]) !== "" || trim($order["parcel_number"]) !== "") { ?>
                            <div class="od-tracking-box mt-1">
                                <div><i class="fas fa-truck me-1 text-primary"></i><?= trim($order["transported_name"]) !== "" ? odEsc($order["transported_name"]) : "-"; ?></div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <strong class="text-primary"><?= trim($order["parcel_number"]) !== "" ? odEsc($order["parcel_number"]) : "-"; ?></strong>
                                    <?php if (trim($order["parcel_number"]) !== "") { ?>
                                    <button type="button" class="btn btn-xs btn-light border js-copy-text"
                                        data-copy="<?= odEsc($order["parcel_number"]); ?>" title="คัดลอกหมายเลขพัสดุ">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="text-muted small">ยังไม่ได้ระบุบริษัทขนส่งและเลขพัสดุ</div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Receipt Info Card -->
            <?php if ((int)$order["receipt_id"] === 1 || trim($order["receipt_link"]) !== "") { ?>
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">ใบเสร็จรับเงิน</h4>
                    <?php if (trim($order["receipt_link"]) !== "") { ?>
                        <span class="badge badge-success">แนบลิงก์แล้ว</span>
                    <?php } else { ?>
                        <span class="badge badge-info">ลูกค้าขอใบเสร็จ</span>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <?php if ((int)$order["receipt_id"] === 1) { ?>
                    <div class="od-info-group mb-3">
                        <div class="od-info-item">
                            <span class="od-info-label">นามออกใบเสร็จ</span>
                            <div class="od-info-val fw-semibold"><?= odEsc($order["receipt_name"]); ?></div>
                        </div>
                        <div class="od-info-item">
                            <span class="od-info-label">เลขประจำตัวผู้เสียภาษี</span>
                            <div class="od-info-val"><?= odEsc($order["receipt_number"]); ?></div>
                        </div>
                        <div class="od-info-item">
                            <span class="od-info-label">ที่อยู่ออกใบเสร็จ</span>
                            <div class="od-info-val"><?= odEsc($order["receipt_address"]); ?></div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if (trim($order["receipt_link"]) !== "") { ?>
                    <a href="<?= odEsc($order["receipt_link"]); ?>" target="_blank" rel="noopener noreferrer"
                        class="btn btn-outline-primary w-100">
                        <i class="fas fa-external-link-alt me-2"></i>เปิดดูใบเสร็จรับเงิน
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal: Update Shipping -->
    <div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="shippingModalLabel">อัปเดตข้อมูลจัดส่ง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group px-0">
                        <label for="transported_id" class="fw-semibold mb-1">บริษัทขนส่ง <span class="text-danger">*</span></label>
                        <select class="form-select form-control" id="transported_id">
                            <option value="0" disabled <?= (int)$order["transported_id"] === 0 ? "selected" : ""; ?>>-- กรุณาเลือกบริษัทขนส่ง --</option>
                            <?php
                                $transporteds = $DB->QueryObj("SELECT * FROM transported");
                                foreach ($transporteds as $transported) {
                                    $sel = ((int)$transported["transported_id"] === (int)$order["transported_id"]) ? "selected" : "";
                                    echo '<option value="' . (int)$transported["transported_id"] . '" ' . $sel . '>' . odEsc($transported["transported_name"]) . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group px-0 mt-2">
                        <label for="parcel_number" class="fw-semibold mb-1">หมายเลขพัสดุ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parcel_number"
                            value="<?= odEsc($order["parcel_number"]); ?>" placeholder="เช่น TH1234567890">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="save-shipping">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Update Receipt Link -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="receiptModalLabel">อัปเดตใบเสร็จรับเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group px-0">
                        <label for="receipt_link" class="fw-semibold mb-1">ลิงก์ใบเสร็จรับเงิน</label>
                        <input type="url" class="form-control" id="receipt_link"
                            value="<?= odEsc($order["receipt_link"]); ?>" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="save-receipt">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Update Order Status -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="statusModalLabel">อัปเดตสถานะคำสั่งซื้อ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group px-0">
                        <label class="form-label fw-semibold">เลือกสถานะใหม่</label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status_id" value="1" class="selectgroup-input" <?= $statusId === 1 ? "checked" : ""; ?>>
                                <span class="selectgroup-button">รอชำระเงิน</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status_id" value="2" class="selectgroup-input" <?= $statusId === 2 ? "checked" : ""; ?>>
                                <span class="selectgroup-button">รอจัดส่ง</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status_id" value="3" class="selectgroup-input" <?= $statusId === 3 ? "checked" : ""; ?>>
                                <span class="selectgroup-button">จัดส่งแล้ว</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status_id" value="4" class="selectgroup-input" <?= $statusId === 4 ? "checked" : ""; ?>>
                                <span class="selectgroup-button">ยกเลิก</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="save-status">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Slip Zoom -->
    <div class="modal fade" id="slipZoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">หลักฐานการชำระเงิน (<?= odEsc($order["order_no"]); ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="slipZoomImg" src="" alt="สลิปการโอนเงิน" class="img-fluid rounded" style="max-height:70vh;">
                </div>
                <div class="modal-footer">
                    <a id="slipZoomLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>เปิดในแท็บใหม่
                    </a>
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
