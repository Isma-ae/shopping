<?php
    if (!isset($_SESSION["user_info"])) {
        echo "<script>window.location.href = './';</script>";
        exit();
    }

    if (!function_exists("shipEsc")) {
        function shipEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("shipFormatBaht")) {
        function shipFormatBaht($amount) {
            $num = (float)($amount ?? 0);
            $hasFraction = abs($num - round($num)) > 0.001;
            return "฿" . number_format($num, $hasFraction ? 2 : 0);
        }
    }

    if (!function_exists("shipFormatDate")) {
        function shipFormatDate($dateStr, $addHours = 0) {
            if (empty($dateStr)) {
                return "-";
            }
            $ts = strtotime((string)$dateStr . ($addHours !== 0 ? " +{$addHours} hours" : ""));
            if (!$ts) {
                return shipEsc($dateStr);
            }
            return date("d/m/Y H:i", $ts);
        }
    }

    $shipUser = $currentUser ?? $_SESSION["user_info"];
    $userIdEsc = $DB->Escape((string)($shipUser["id"] ?? ""));

    // Count orders by status in a single query
    $statusCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    $countRows = $DB->QueryObj("
        SELECT status_id, COUNT(order_id) AS cnt
        FROM orders
        WHERE user_id = '{$userIdEsc}'
        GROUP BY status_id
    ");
    foreach ($countRows as $cr) {
        $sid = (int)($cr["status_id"] ?? 0);
        if (isset($statusCounts[$sid])) {
            $statusCounts[$sid] = (int)$cr["cnt"];
        }
    }

    $baseOrderSql = "
        SELECT
            orders.*,
            tb_address.name AS addr_name,
            tb_address.phone AS addr_phone,
            tb_address.address_at AS addr_detail,
            subdistricts.zip_code AS addr_zip,
            provinces.province_name_in_thai,
            districts.district_name_in_thai,
            subdistricts.subdistrict_name_in_thai,
            transported.transported_name
        FROM orders
        LEFT JOIN tb_address ON orders.address_id = tb_address.id
        LEFT JOIN provinces ON tb_address.province_id = provinces.province_id
        LEFT JOIN districts ON tb_address.district_id = districts.district_id
        LEFT JOIN subdistricts ON tb_address.subdistrict_id = subdistricts.subdistrict_id
        LEFT JOIN transported ON orders.transported_id = transported.transported_id
        WHERE orders.user_id = '{$userIdEsc}'
    ";

    $tabs = [
        [
            "id"          => "tab-pending",
            "status_id"   => 1,
            "label"       => "รอชำระเงิน",
            "badge_class" => "is-pending",
            "icon"        => "fa-clock-o",
            "empty_title" => "ไม่มีรายการที่รอชำระเงิน",
            "empty_desc"  => "คำสั่งซื้อที่ยังไม่ชำระเงินจะแสดงที่นี่"
        ],
        [
            "id"          => "tab-shipping",
            "status_id"   => 2,
            "label"       => "รอจัดส่ง",
            "badge_class" => "is-shipping",
            "icon"        => "fa-archive",
            "empty_title" => "ไม่มีรายการที่รอจัดส่ง",
            "empty_desc"  => "คำสั่งซื้อที่ชำระเงินเรียบร้อยแล้วและกำลังเตรียมจัดส่งจะแสดงที่นี่"
        ],
        [
            "id"          => "tab-completed",
            "status_id"   => 3,
            "label"       => "จัดส่งแล้ว",
            "badge_class" => "is-completed",
            "icon"        => "fa-check-circle-o",
            "empty_title" => "ยังไม่มีรายการที่จัดส่งแล้ว",
            "empty_desc"  => "ประวัติคำสั่งซื้อที่จัดส่งสำเร็จจะแสดงที่นี่"
        ],
        [
            "id"          => "tab-cancelled",
            "status_id"   => 4,
            "label"       => "ยกเลิกแล้ว",
            "badge_class" => "is-cancelled",
            "icon"        => "fa-times-circle-o",
            "empty_title" => "ไม่มีรายการที่ยกเลิก",
            "empty_desc"  => "รายการที่ถูกยกเลิกหรือเกินกำหนดชำระเงินจะแสดงที่นี่"
        ]
    ];

    if (!function_exists("shipFetchOrderItems")) {
        function shipFetchOrderItems($DB, $orderId) {
            $oid = (int)$orderId;
            return $DB->QueryObj("
                SELECT
                    od.detail_id,
                    od.order_id,
                    od.variant_id,
                    od.product_name,
                    COALESCE(od.variant_color, '') AS variant_color,
                    COALESCE(od.variant_size, '') AS variant_size,
                    COALESCE(v.size_detail, '') AS size_detail,
                    COALESCE(od.variant_price, 0) AS variant_price,
                    COALESCE(od.qty, 1) AS qty,
                    COALESCE(od.order_type, 1) AS order_type,
                    COALESCE(od.total_price, 0) AS total_price,
                    MD5(v.product_id) AS product_hash,
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
                WHERE od.order_id = {$oid}
                ORDER BY od.detail_id ASC
            ");
        }
    }
?>

<!-- Breadcrumb -->
<section class="orders-breadcrumb-bar">
    <div class="container">
        <nav class="orders-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">รายการสั่งซื้อของฉัน</span>
        </nav>
    </div>
</section>

<!-- Main Orders Section -->
<section class="orders-page-section">
    <div class="container">
        <div class="orders-page-head">
            <div>
                <span class="oar-eyebrow">MY ORDERS</span>
                <h1 class="oar-heading">รายการสั่งซื้อของฉัน</h1>
            </div>
            <a href="?page=product" class="orders-shop-btn">
                <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>เลือกซื้อสินค้าเพิ่ม
            </a>
        </div>

        <!-- Status Tabs -->
        <div class="orders-tabs-wrap">
            <ul class="nav nav-tabs orders-nav-tabs" role="tablist">
                <?php foreach ($tabs as $idx => $tab) {
                    $sid = $tab["status_id"];
                    $cnt = $statusCounts[$sid];
                    $isActive = ($idx === 0);
                ?>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive ? ' active' : ''; ?>"
                        data-toggle="tab"
                        href="#<?= $tab["id"]; ?>"
                        role="tab">
                        <i class="fa <?= $tab["icon"]; ?>" aria-hidden="true"></i>
                        <span><?= shipEsc($tab["label"]); ?></span>
                        <?php if ($cnt > 0) { ?>
                            <span class="orders-tab-count"><?= $cnt; ?></span>
                        <?php } ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>

        <!-- Tab Panes -->
        <div class="tab-content orders-tab-content">
            <?php foreach ($tabs as $idx => $tab) {
                $sid = $tab["status_id"];
                $isActive = ($idx === 0);
                $orders = $DB->QueryObj($baseOrderSql . " AND orders.status_id = {$sid} ORDER BY orders.order_id DESC");
            ?>
            <div class="tab-pane fade<?= $isActive ? ' show active' : ''; ?>" id="<?= $tab["id"]; ?>" role="tabpanel">
                <?php if (count($orders) === 0) { ?>
                    <div class="orders-empty-state">
                        <div class="orders-empty-icon">
                            <i class="fa <?= $tab["icon"]; ?>" aria-hidden="true"></i>
                        </div>
                        <h3 class="orders-empty-title"><?= shipEsc($tab["empty_title"]); ?></h3>
                        <p class="orders-empty-desc"><?= shipEsc($tab["empty_desc"]); ?></p>
                        <a href="?page=product" class="orders-empty-btn">
                            <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>เลือกชมสินค้า
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order) {
                            $orderNo = (string)($order["order_no"] ?? "");
                            $orderId = (int)($order["order_id"] ?? 0);
                            $shipTypeCode = (string)($order["shipping_type"] ?? "1");
                            $shipPrice = (float)($order["shipping_price"] ?? 0);
                            $totalPrice = (float)($order["total_price"] ?? 0);
                            $receiptLink = trim((string)($order["receipt_link"] ?? ""));
                            $transportName = trim((string)($order["transported_name"] ?? ""));
                            $parcelNum = trim((string)($order["parcel_number"] ?? ""));

                            if ($shipTypeCode === "1") {
                                $shipLabel = "รับหน้าร้าน";
                                $shipIcon = "fa-university";
                                $receiverName = trim((string)($order["shipping_name"] ?? ""));
                                $receiverPhone = trim((string)($order["shipping_phone"] ?? ""));
                                $receiverExtra = trim((string)($order["shipping_department"] ?? ""));
                                $addressLine = "เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี";
                            } elseif ($shipTypeCode === "2") {
                                $shipLabel = "ส่งภายในวิทยาเขต";
                                $shipIcon = "fa-paper-plane-o";
                                $receiverName = trim((string)($order["shipping_name"] ?? ""));
                                $receiverPhone = trim((string)($order["shipping_phone"] ?? ""));
                                $receiverExtra = trim((string)($order["shipping_department"] ?? ""));
                                $addressLine = $receiverExtra !== "" ? "คณะ / หน่วยงาน: " . $receiverExtra : "";
                            } else {
                                $shipLabel = "ส่งภายนอกวิทยาเขต";
                                $shipIcon = "fa-truck";
                                $receiverName = trim((string)($order["addr_name"] ?? ""));
                                $receiverPhone = trim((string)($order["addr_phone"] ?? ""));
                                $addrParts = [];
                                if (!empty($order["addr_detail"])) $addrParts[] = trim((string)$order["addr_detail"]);
                                if (!empty($order["subdistrict_name_in_thai"])) $addrParts[] = "ตำบล" . trim((string)$order["subdistrict_name_in_thai"]);
                                if (!empty($order["district_name_in_thai"])) $addrParts[] = "อำเภอ" . trim((string)$order["district_name_in_thai"]);
                                if (!empty($order["province_name_in_thai"])) $addrParts[] = "จังหวัด" . trim((string)$order["province_name_in_thai"]);
                                if (!empty($order["addr_zip"])) $addrParts[] = trim((string)$order["addr_zip"]);
                                $addressLine = implode(" ", $addrParts);
                            }

                            $items = shipFetchOrderItems($DB, $orderId);
                        ?>
                        <article class="order-card">
                            <!-- Card Header -->
                            <div class="order-card-head">
                                <div class="order-head-left">
                                    <div class="order-no-group">
                                        <span class="order-no-label">รหัสใบสั่งซื้อ</span>
                                        <strong class="order-no-code"><?= shipEsc($orderNo); ?></strong>
                                        <button type="button" class="order-copy-btn js-copy-text"
                                            data-copy="<?= shipEsc($orderNo); ?>" title="คัดลอกรหัสใบสั่งซื้อ">
                                            <i class="fa fa-clone" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span class="order-date-text">
                                        <i class="fa fa-calendar-o mr-1" aria-hidden="true"></i>
                                        สั่งซื้อเมื่อ <?= shipFormatDate($order["create_date"] ?? ""); ?> น.
                                    </span>
                                </div>

                                <span class="order-status-pill <?= $tab["badge_class"]; ?>">
                                    <i class="fa <?= $tab["icon"]; ?> mr-1" aria-hidden="true"></i>
                                    <?= shipEsc($tab["label"]); ?>
                                </span>
                            </div>

                            <!-- Shipping & Tracking Info Strip -->
                            <div class="order-ship-strip">
                                <div class="order-ship-row">
                                    <span class="order-ship-badge">
                                        <i class="fa <?= $shipIcon; ?> mr-1" aria-hidden="true"></i>
                                        <?= shipEsc($shipLabel); ?>
                                    </span>

                                    <?php if ($receiverName !== "" || $receiverPhone !== "") { ?>
                                    <span class="order-ship-receiver">
                                        <?php if ($receiverName !== "") { ?>
                                            <strong>ผู้รับ:</strong> <?= shipEsc($receiverName); ?>
                                        <?php } ?>
                                        <?php if ($receiverPhone !== "") { ?>
                                            <span class="order-ship-sep">&middot;</span>
                                            <strong>โทร:</strong> <?= shipEsc($receiverPhone); ?>
                                        <?php } ?>
                                    </span>
                                    <?php } ?>
                                </div>

                                <?php if ($addressLine !== "") { ?>
                                <div class="order-ship-address">
                                    <i class="fa fa-map-marker mr-1" aria-hidden="true"></i>
                                    <?= shipEsc($addressLine); ?>
                                </div>
                                <?php } ?>

                                <?php if ($transportName !== "" || $parcelNum !== "") { ?>
                                <div class="order-tracking-bar">
                                    <i class="fa fa-barcode" aria-hidden="true"></i>
                                    <?php if ($transportName !== "") { ?>
                                        <span>จัดส่งโดย: <strong><?= shipEsc($transportName); ?></strong></span>
                                    <?php } ?>
                                    <?php if ($parcelNum !== "") { ?>
                                        <span>หมายเลขพัสดุ: <strong><?= shipEsc($parcelNum); ?></strong></span>
                                        <button type="button" class="order-copy-btn js-copy-text"
                                            data-copy="<?= shipEsc($parcelNum); ?>" title="คัดลอกหมายเลขพัสดุ">
                                            <i class="fa fa-clone" aria-hidden="true"></i>
                                        </button>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>

                            <!-- Order Items List -->
                            <div class="order-items-list">
                                <?php foreach ($items as $item) {
                                    $colorCnt = (int)($item["color_count"] ?? 0);
                                    $sizeCnt = (int)($item["size_count"] ?? 0);
                                    $colorVal = trim((string)($item["variant_color"] ?? ""));
                                    $sizeVal = trim((string)($item["variant_size"] ?? ""));
                                    $sizeDet = trim((string)($item["size_detail"] ?? ""));
                                    $isReserve = ((int)($item["order_type"] ?? 1) === 2);

                                    $imgName = trim((string)($item["img_name"] ?? ""));
                                    $imgSrc = $imgName !== "" ? "file/product/" . rawurlencode($imgName) : "images/icons/OARShopping.png";
                                    $detailHref = !empty($item["product_hash"]) ? "?page=detail&product=" . urlencode($item["product_hash"]) : "?page=product";
                                ?>
                                <div class="order-item-row">
                                    <a href="<?= shipEsc($detailHref); ?>" class="order-item-thumb">
                                        <img src="<?= shipEsc($imgSrc); ?>"
                                            alt="<?= shipEsc($item["product_name"]); ?>"
                                            onerror="this.onerror=null;this.src='images/icons/OARShopping.png';this.classList.add('is-fallback-img');">
                                    </a>

                                    <div class="order-item-info">
                                        <a href="<?= shipEsc($detailHref); ?>" class="order-item-title">
                                            <?= shipEsc($item["product_name"]); ?>
                                        </a>

                                        <?php if ($colorCnt > 1 || $sizeCnt > 1 || $isReserve) { ?>
                                        <div class="order-item-variants">
                                            <?php if ($colorCnt > 1) { ?>
                                                <span class="order-variant-pill">
                                                    <strong>สี:</strong><?= shipEsc($colorVal !== "" ? $colorVal : "มาตรฐาน"); ?>
                                                </span>
                                            <?php } ?>
                                            <?php if ($sizeCnt > 1) {
                                                $sizeLabel = $sizeVal !== "" ? $sizeVal : "มาตรฐาน";
                                                if ($sizeDet !== "") $sizeLabel .= " (" . $sizeDet . ")";
                                            ?>
                                                <span class="order-variant-pill">
                                                    <strong>ขนาด:</strong><?= shipEsc($sizeLabel); ?>
                                                </span>
                                            <?php } ?>
                                            <?php if ($isReserve) { ?>
                                                <span class="order-reserve-pill">สินค้าสั่งจอง</span>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>

                                        <div class="order-item-qty">
                                            จำนวน <?= (int)$item["qty"]; ?> ชิ้น
                                            <?php if ((float)$item["variant_price"] > 0) { ?>
                                                <span>(<?= shipFormatBaht($item["variant_price"]); ?> / ชิ้น)</span>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="order-item-price">
                                        <?= shipFormatBaht($item["total_price"]); ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <!-- Card Footer -->
                            <div class="order-card-footer">
                                <div class="order-footer-left">
                                    <?php if ($sid === 1) { ?>
                                        <div class="order-deadline-note">
                                            <i class="fa fa-qrcode" aria-hidden="true"></i>
                                            <span>
                                                กรุณาชำระเงินภายใน
                                                <strong><?= shipFormatDate($order["create_date"] ?? "", 24); ?> น.</strong>
                                                ผ่าน Thai QR PromptPay
                                            </span>
                                        </div>
                                    <?php } elseif ($receiptLink !== "") { ?>
                                        <a href="<?= shipEsc($receiptLink); ?>" target="_blank" rel="noopener noreferrer" class="order-receipt-btn">
                                            <i class="fa fa-file-text-o mr-2" aria-hidden="true"></i>
                                            เปิดดูใบเสร็จรับเงิน
                                            <i class="fa fa-external-link ml-2" aria-hidden="true"></i>
                                        </a>
                                    <?php } ?>
                                </div>

                                <div class="order-footer-right">
                                    <div class="order-totals">
                                        <div class="order-total-row">
                                            <span>ค่าจัดส่ง:</span>
                                            <strong class="<?= $shipPrice <= 0 ? 'is-free' : ''; ?>">
                                                <?= $shipPrice <= 0 ? "ฟรี" : shipFormatBaht($shipPrice); ?>
                                            </strong>
                                        </div>
                                        <div class="order-total-row order-total-row--grand">
                                            <span>ยอดรวมทั้งหมด:</span>
                                            <strong><?= shipFormatBaht($totalPrice); ?></strong>
                                        </div>
                                    </div>

                                    <?php if ($sid === 1) { ?>
                                    <div class="order-actions">
                                        <button type="button" class="order-btn-cancel js-cancel-order"
                                            data-order-id="<?= $orderId; ?>">
                                            ยกเลิกคำสั่งซื้อ
                                        </button>
                                        <a href="payment/?order_no=<?= urlencode($orderNo); ?>" class="order-btn-pay">
                                            <i class="fa fa-qrcode mr-2" aria-hidden="true"></i>
                                            ชำระเงินตอนนี้
                                        </a>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
