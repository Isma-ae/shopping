<?php
    if (!function_exists("evEsc")) {
        function evEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("evFormatBaht")) {
        function evFormatBaht($amount) {
            $num = (float)($amount ?? 0);
            $hasFraction = abs($num - round($num)) > 0.001;
            return "฿" . number_format($num, $hasFraction ? 2 : 0);
        }
    }

    $order_no = trim((string)($_GET["order_no"] ?? ""));
    $isLoggedIn = isset($_SESSION["user_info"]);
    $pendingOrders = [];
    $selectedOrder = null;

    if ($isLoggedIn) {
        $evUser = $currentUser ?? $_SESSION["user_info"];
        $evUserIdEsc = $DB->Escape((string)($evUser["id"] ?? ""));
        $pendingOrders = $DB->QueryObj("
            SELECT order_no, total_price, create_date
            FROM orders
            WHERE user_id = '{$evUserIdEsc}' AND status_id = 1
            ORDER BY order_id DESC
            LIMIT 8
        ");
    }

    if ($order_no !== "") {
        $ordEsc = $DB->Escape($order_no);
        $ordRows = $DB->QueryObj("
            SELECT order_no, total_price, status_id, create_date
            FROM orders
            WHERE order_no = '{$ordEsc}'
            LIMIT 1
        ");
        if (!empty($ordRows)) {
            $selectedOrder = $ordRows[0];
        }
    }
?>

<!-- Breadcrumb -->
<section class="ev-breadcrumb-bar">
    <div class="container">
        <nav class="ev-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">แจ้งหลักฐานการโอนเงิน</span>
        </nav>
    </div>
</section>

<!-- Main Evidence Section -->
<section class="ev-page-section">
    <div class="container">
        <div class="ev-page-head">
            <div>
                <span class="oar-eyebrow">PAYMENT EVIDENCE</span>
                <h1 class="oar-heading">แจ้งหลักฐานการโอนเงิน</h1>
                <p class="ev-head-sub mb-0">
                    กรณีชำระเงินผ่าน QR PromptPay แล้วสถานะยังไม่อัปเดตอัตโนมัติ สามารถแนบสลิปเพื่อให้เจ้าหน้าที่ตรวจสอบได้ทันที
                </p>
            </div>

            <?php if ($isLoggedIn) { ?>
                <a href="?page=shipping_list" class="ev-secondary-btn">
                    <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>รายการสั่งซื้อของฉัน
                </a>
            <?php } else { ?>
                <a href="?page=search-order" class="ev-secondary-btn">
                    <i class="fa fa-search mr-2" aria-hidden="true"></i>ค้นหาคำสั่งซื้อ
                </a>
            <?php } ?>
        </div>

        <div class="row align-items-stretch">
            <!-- Left: Evidence Upload Form Card -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="ev-form-card">
                    <div class="ev-card-head">
                        <div class="ev-card-icon">
                            <i class="fa fa-upload" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="ev-card-title">ส่งหลักฐานการชำระเงิน</h2>
                            <p class="ev-card-sub mb-0">ระบุเลขที่ใบสั่งซื้อและแนบภาพสลิปการโอนเงินที่เห็นรายละเอียดชัดเจน</p>
                        </div>
                    </div>

                    <form id="evidence-form" class="ev-form-body" enctype="multipart/form-data" novalidate>
                        <?php if ($selectedOrder !== null) {
                            $selStatus = (int)($selectedOrder["status_id"] ?? 0);
                            $selLabel = $selStatus === 1 ? "รอชำระเงิน" : ($selStatus === 2 ? "รอจัดส่ง" : ($selStatus === 3 ? "จัดส่งแล้ว" : "ยกเลิกแล้ว"));
                            $selClass = $selStatus === 1 ? "is-pending" : ($selStatus === 2 ? "is-shipping" : ($selStatus === 3 ? "is-completed" : "is-cancelled"));
                        ?>
                        <div class="ev-order-summary">
                            <div class="ev-order-summary-left">
                                <span class="ev-order-summary-label">ข้อมูลคำสั่งซื้อที่เลือก</span>
                                <div class="ev-order-summary-meta">
                                    <strong><?= evEsc($selectedOrder["order_no"]); ?></strong>
                                    <span>&middot;</span>
                                    <span class="ev-order-summary-price"><?= evFormatBaht($selectedOrder["total_price"]); ?></span>
                                </div>
                            </div>
                            <span class="ev-status-pill <?= $selClass; ?>"><?= evEsc($selLabel); ?></span>
                        </div>
                        <?php } ?>

                        <div class="ev-field">
                            <label class="ev-label" for="order_no">
                                เลขที่ใบสั่งซื้อ <span class="ev-req">*</span>
                            </label>
                            <div class="ev-input-wrap">
                                <i class="fa fa-hashtag ev-input-icon" aria-hidden="true"></i>
                                <input class="ev-input" id="order_no" type="text" name="order_no"
                                    value="<?= evEsc($order_no); ?>"
                                    placeholder="กรอกเลขที่ใบสั่งซื้อ เช่น ORD20260101120000"
                                    autocomplete="off">
                            </div>

                            <?php if (!empty($pendingOrders)) { ?>
                            <div class="ev-quick-orders">
                                <span class="ev-quick-label">เลือกรายการรอชำระเงินของคุณ:</span>
                                <div class="ev-quick-chips">
                                    <?php foreach ($pendingOrders as $po) {
                                        $poNo = (string)($po["order_no"] ?? "");
                                        $isCur = ($order_no !== "" && $order_no === $poNo);
                                    ?>
                                    <button type="button"
                                        class="ev-quick-chip js-pick-order<?= $isCur ? ' is-selected' : ''; ?>"
                                        data-order-no="<?= evEsc($poNo); ?>">
                                        <span><?= evEsc($poNo); ?></span>
                                        <small><?= evFormatBaht($po["total_price"]); ?></small>
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="ev-field">
                            <label class="ev-label" for="evidence_slip">
                                ภาพสลิป / หลักฐานการโอนเงิน <span class="ev-req">*</span>
                            </label>

                            <div class="ev-dropzone" id="ev-dropzone">
                                <input type="file" id="evidence_slip" name="evidence_slip"
                                    class="ev-file-input"
                                    accept="image/jpeg,image/png,image/webp,image/gif">

                                <div class="ev-dropzone-empty" id="ev-dropzone-empty">
                                    <div class="ev-dropzone-icon">
                                        <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                                    </div>
                                    <div class="ev-dropzone-title">คลิกเพื่อเลือกไฟล์ภาพ หรือลากไฟล์มาวางที่นี่</div>
                                    <div class="ev-dropzone-hint">รองรับไฟล์ JPG, PNG, WEBP, GIF ขนาดไม่เกิน 5 MB</div>
                                </div>

                                <div class="ev-preview-wrap" id="ev-preview-wrap" style="display: none;">
                                    <div class="ev-preview-thumb">
                                        <img id="ev-preview-img" src="" alt="ตัวอย่างสลิปการโอนเงิน">
                                    </div>
                                    <div class="ev-preview-info">
                                        <div class="ev-preview-name" id="ev-preview-name"></div>
                                        <div class="ev-preview-size" id="ev-preview-size"></div>
                                        <div class="ev-preview-actions">
                                            <button type="button" class="ev-preview-btn" id="ev-change-file">
                                                <i class="fa fa-refresh mr-1" aria-hidden="true"></i>เปลี่ยนรูป
                                            </button>
                                            <button type="button" class="ev-preview-btn ev-preview-btn--remove" id="ev-remove-file">
                                                <i class="fa fa-trash-o mr-1" aria-hidden="true"></i>ลบไฟล์
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="ev-submit-btn" class="ev-submit-btn">
                            <i class="fa fa-paper-plane-o mr-2" aria-hidden="true"></i>
                            <span>ส่งหลักฐานการชำระเงิน</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Guidance Card -->
            <div class="col-lg-5">
                <div class="ev-info-card">
                    <span class="ev-info-tag">PAYMENT VERIFICATION · OAR PSU PATTANI</span>
                    <h2 class="ev-info-title">คำแนะนำการแจ้งหลักฐาน</h2>
                    <p class="ev-info-desc">
                        เพื่อให้เจ้าหน้าที่ตรวจสอบยอดโอนและดำเนินการจัดส่งได้อย่างรวดเร็ว กรุณาตรวจสอบข้อมูลต่อไปนี้ก่อนส่งหลักฐาน
                    </p>

                    <ul class="ev-info-list">
                        <li>
                            <div class="ev-info-icon">
                                <i class="fa fa-file-text-o" aria-hidden="true"></i>
                            </div>
                            <div class="ev-info-text">
                                <strong>1. ตรวจสอบเลขที่ใบสั่งซื้อ</strong>
                                <p class="mb-0">ระบุเลขที่ใบสั่งซื้อ (ขึ้นต้นด้วย ORD...) ให้ตรงกับรายการที่ชำระเงิน</p>
                            </div>
                        </li>

                        <li>
                            <div class="ev-info-icon">
                                <i class="fa fa-picture-o" aria-hidden="true"></i>
                            </div>
                            <div class="ev-info-text">
                                <strong>2. ภาพสลิปชัดเจนครบถ้วน</strong>
                                <p class="mb-0">เห็นยอดเงิน วันที่-เวลาที่โอน และเลขที่อ้างอิง/QR บนสลิปชัดเจน ไม่เบลอหรือถูกตัดขอบ</p>
                            </div>
                        </li>

                        <li>
                            <div class="ev-info-icon">
                                <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                            </div>
                            <div class="ev-info-text">
                                <strong>3. รอเจ้าหน้าที่ยืนยันสถานะ</strong>
                                <p class="mb-0">เมื่อเจ้าหน้าที่ตรวจสอบเรียบร้อย สถานะคำสั่งซื้อจะเปลี่ยนเป็น "รอจัดส่ง" โดยอัตโนมัติ</p>
                            </div>
                        </li>
                    </ul>

                    <div class="ev-help-box">
                        <div class="ev-help-text">
                            <strong>พบปัญหาการโอนเงิน?</strong>
                            <span>ติดต่อโทร <a href="tel:073331300">073-331-300</a> (จันทร์ – ศุกร์)</span>
                        </div>
                        <a href="?page=contact" class="ev-help-pill">
                            <i class="fa fa-envelope-o mr-1" aria-hidden="true"></i>ติดต่อเรา
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
