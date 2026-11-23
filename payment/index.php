<?php
    include_once("../config/all.php");

    if (!function_exists("payEsc")) {
        function payEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("generateRefSecure")) {
        function generateRefSecure($prefix = "R") {
            return $prefix . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        }
    }

    $rawOrderNo = trim((string)($_GET["order_no"] ?? ""));
    $isLoggedIn = isset($_SESSION["user_info"]);
    $backUrl = $isLoggedIn
        ? "../?page=shipping_list"
        : ($rawOrderNo !== "" ? "../?page=search-order&order_no=" . rawurlencode($rawOrderNo) : "../?page=search-order");

    $order = null;
    $pageState = "not_found"; // ready | paid | cancelled | not_found | qr_error
    $errorMsg = "ไม่พบข้อมูลคำสั่งซื้อที่ระบุ กรุณาตรวจสอบเลขที่ใบสั่งซื้ออีกครั้ง";

    $ref1 = "";
    $ref2 = "";
    $ref3 = "OAR00026";
    $qrImageBase64 = "";
    $totalAmountNum = 0.0;
    $totalAmountFmt = "0.00";
    $displayBillerId = !empty($biller_id) ? $biller_id : "099400058086003";

    if ($rawOrderNo !== "") {
        $orderNoEsc = $DB->Escape($rawOrderNo);
        $rows = $DB->QueryObj("
            SELECT order_id, order_no, total_price, shipping_price, status_id, order_status, create_date
            FROM orders
            WHERE order_no = '{$orderNoEsc}'
            LIMIT 1
        ");

        if (!empty($rows)) {
            $order = $rows[0];
            $statusId = (int)($order["status_id"] ?? 0);
            $totalAmountNum = (float)($order["total_price"] ?? 0);
            $totalAmountFmt = number_format($totalAmountNum, 2, '.', '');

            if ($statusId === 2 || $statusId === 3) {
                $pageState = "paid";
            } elseif ($statusId === 4) {
                $pageState = "cancelled";
            } elseif ($statusId === 1 && $totalAmountNum > 0) {
                $requestUId = "OAR" . strtoupper(bin2hex(random_bytes(8)));
                $ref1 = generateRefSecure("PAY");
                $ref2 = generateRefSecure("CHK");

                $res = $pay->CreateQR30($requestUId, $totalAmountFmt, $ref1, $ref2, $ref3);
                if (is_array($res) && !empty($res["data"]["qrImage"])) {
                    $qrImageBase64 = (string)$res["data"]["qrImage"];
                    $pageState = "ready";
                } else {
                    $pageState = "qr_error";
                    $scbDesc = is_array($res) ? (string)($res["status"]["description"] ?? "") : "";
                    $errorMsg = $scbDesc !== ""
                        ? "ไม่สามารถสร้าง QR Code ได้ชั่วคราว ({$scbDesc})"
                        : "ไม่สามารถเชื่อมต่อระบบสร้าง Thai QR Payment ได้ชั่วคราว กรุณาลองรีเฟรชหน้าใหม่อีกครั้ง";
                }
            } else {
                $pageState = "qr_error";
                $errorMsg = "ยอดเงินของคำสั่งซื้อนี้ไม่ถูกต้อง ไม่สามารถสร้าง QR Code ได้";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <title>ชำระเงิน | OAR Shopping</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/icons/favicon.png" />
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/util.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/main.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../style.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('style.css'); ?>">

    <script src="../vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="../vendor/sweetalert/sweetalert.min.js"></script>
    <script src="<?= assetVer('script.js'); ?>"></script>
</head>

<body class="animsition oar-pay-body">
    <!-- Topbar -->
    <header class="oar-pay-topbar">
        <div class="container">
            <div class="oar-pay-topbar-inner">
                <a href="../" class="oar-pay-logo" aria-label="OAR Shopping หน้าแรก">
                    <img src="../images/icons/S__8732697.png" alt="OAR Shopping">
                </a>
                <a href="<?= payEsc($backUrl); ?>" class="oar-pay-back-btn">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    <span><?= $isLoggedIn ? 'รายการสั่งซื้อของฉัน' : 'ตรวจสอบคำสั่งซื้อ'; ?></span>
                </a>
            </div>
        </div>
    </header>

    <main class="oar-pay-main">
        <div class="container">
            <?php if ($pageState === "ready") { ?>
                <div class="oar-pay-head">
                    <span class="oar-eyebrow">THAI QR PROMPTPAY</span>
                    <h1 class="oar-pay-heading">ชำระเงินด้วย QR Code</h1>
                    <p class="oar-pay-sub mb-0">
                        สแกนผ่านแอปธนาคารใดก็ได้ ระบบจะตรวจสอบยอดเงินและปรับสถานะให้อัตโนมัติภายใน 1 นาที
                    </p>
                </div>

                <input type="hidden" id="order_no" value="<?= payEsc($order["order_no"]); ?>">
                <input type="hidden" id="ref1" value="<?= payEsc($ref1); ?>">
                <input type="hidden" id="ref2" value="<?= payEsc($ref2); ?>">
                <input type="hidden" id="redirect_url" value="<?= payEsc($backUrl); ?>">

                <div class="oar-pay-card">
                    <div class="row no-gutters align-items-stretch">
                        <!-- Left: QR Code Column -->
                        <div class="col-lg-5 oar-pay-qr-col">
                            <div class="oar-pay-orb oar-pay-orb--1" aria-hidden="true"></div>
                            <div class="oar-pay-orb oar-pay-orb--2" aria-hidden="true"></div>
                            <div class="oar-pay-qr-inner">
                                <div class="thaiqrpayment-box">
                                    <div class="thaiqrpayment">
                                        <img src="../images/icons/Thai_QR_Logo.png" alt="Thai QR Payment">
                                    </div>
                                    <div class="qr-box">
                                        <div class="qr-img-wrap">
                                            <img src="data:image/jpeg;base64,<?= payEsc($qrImageBase64); ?>"
                                                alt="Thai QR PromptPay" class="qrcode" id="qr-image">
                                            <div class="qr-overlay" id="qr-overlay" style="display: none;">
                                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                <strong>QR Code หมดอายุแล้ว</strong>
                                                <button type="button" class="qr-reload-btn" id="btn-reload-qr">
                                                    <i class="fa fa-refresh mr-1" aria-hidden="true"></i>สร้าง QR Code ใหม่
                                                </button>
                                            </div>
                                        </div>
                                        <div class="qr-amount-strip">
                                            <span>ยอดชำระทั้งหมด</span>
                                            <strong>฿<?= number_format($totalAmountNum, 2); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="countdown-box" id="countdown-box">
                                    <i class="fa fa-clock-o mr-1" aria-hidden="true"></i>
                                    ชำระเงินภายใน <strong id="countdown">07:00</strong> นาที
                                </div>

                                <a href="data:image/jpeg;base64,<?= payEsc($qrImageBase64); ?>"
                                    download="QR-<?= payEsc($order["order_no"]); ?>.jpg"
                                    class="oar-pay-download-btn">
                                    <i class="fa fa-download mr-2" aria-hidden="true"></i>บันทึกภาพ QR Code
                                </a>
                            </div>
                        </div>

                        <!-- Right: Payment Details Column -->
                        <div class="col-lg-7 oar-pay-info-col">
                            <div class="oar-pay-info-inner">
                                <div class="info-title">
                                    <span class="info-title-badge">OAR SHOPPING · PSU PATTANI</span>
                                    <h2>รายละเอียดการชำระเงิน</h2>
                                </div>

                                <div class="row oar-info-grid">
                                    <div class="col-12">
                                        <div class="info-item info-item--highlight">
                                            <div class="info-label">เลขที่ใบสั่งซื้อ</div>
                                            <div class="info-value"><?= payEsc($order["order_no"]); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">ยอดเงินที่ต้องชำระ</div>
                                            <div class="info-value info-value--price">฿<?= number_format($totalAmountNum, 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">เลขอ้างอิง 3</div>
                                            <div class="info-value"><?= payEsc($ref3); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">เลขอ้างอิง 1</div>
                                            <div class="info-value info-value--mono"><?= payEsc($ref1); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">เลขอ้างอิง 2</div>
                                            <div class="info-value info-value--mono"><?= payEsc($ref2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">บัญชีปลายทาง (Biller ID : <?= payEsc($displayBillerId); ?>)</div>
                                            <div class="info-value">สำนักวิทยบริการ มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="warn-text">
                                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span>กรุณาชำระเงินผ่านการสแกน QR Code นี้เท่านั้น และไม่ปิดหน้านี้ระหว่างรอระบบตรวจสอบยอดเงิน</span>
                                </div>

                                <div class="oar-pay-status-box">
                                    <div class="after-pay-message">
                                        <i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i>
                                        หากชำระเงินแล้ว กรุณารอสักครู่ ระบบกำลังตรวจสอบการชำระเงินอัตโนมัติ...
                                    </div>
                                    <div class="after-pay-link">
                                        หากชำระเงินแล้วแต่ระบบยังไม่ปรับสถานะ
                                        <a href="../?page=evidence&order_no=<?= rawurlencode((string)$order["order_no"]); ?>">
                                            คลิกที่นี่เพื่อส่งหลักฐานการโอนเงิน
                                        </a>
                                    </div>
                                </div>

                                <div class="oar-pay-footer-actions">
                                    <a href="<?= payEsc($backUrl); ?>" class="oar-pay-cancel-link">
                                        <i class="fa fa-arrow-left mr-2" aria-hidden="true"></i>ย้อนกลับไปหน้ารายการสั่งซื้อ
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php } elseif ($pageState === "paid") { ?>
                <div class="oar-pay-state-card">
                    <div class="oar-pay-state-icon is-success">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                    </div>
                    <h2>คำสั่งซื้อนี้ชำระเงินเรียบร้อยแล้ว</h2>
                    <p>
                        เลขที่ใบสั่งซื้อ <strong><?= payEsc($order["order_no"]); ?></strong>
                        ได้รับการยืนยันการชำระเงินแล้ว ไม่จำเป็นต้องสแกนชำระซ้ำ
                    </p>
                    <div class="oar-pay-state-actions">
                        <a href="<?= payEsc($backUrl); ?>" class="oar-pay-primary-btn">
                            <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>ดูสถานะคำสั่งซื้อ
                        </a>
                        <a href="../" class="oar-pay-outline-btn">กลับหน้าแรก</a>
                    </div>
                </div>

            <?php } elseif ($pageState === "cancelled") { ?>
                <div class="oar-pay-state-card">
                    <div class="oar-pay-state-icon is-muted">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </div>
                    <h2>คำสั่งซื้อนี้ถูกยกเลิกแล้ว</h2>
                    <p>
                        เลขที่ใบสั่งซื้อ <strong><?= payEsc($order["order_no"]); ?></strong>
                        ถูกยกเลิกแล้ว จึงไม่สามารถชำระเงินได้ หากต้องการสั่งซื้อกรุณาทำรายการใหม่
                    </p>
                    <div class="oar-pay-state-actions">
                        <a href="../?page=product" class="oar-pay-primary-btn">
                            <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>เลือกซื้อสินค้าใหม่
                        </a>
                        <a href="<?= payEsc($backUrl); ?>" class="oar-pay-outline-btn">ดูรายการสั่งซื้อ</a>
                    </div>
                </div>

            <?php } else { ?>
                <div class="oar-pay-state-card">
                    <div class="oar-pay-state-icon is-warn">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <h2><?= $pageState === "qr_error" ? "ไม่สามารถสร้าง QR Code ได้" : "ไม่พบคำสั่งซื้อ"; ?></h2>
                    <p><?= payEsc($errorMsg); ?></p>
                    <div class="oar-pay-state-actions">
                        <?php if ($pageState === "qr_error") { ?>
                            <a href="<?= payEsc($_SERVER["REQUEST_URI"] ?? "./"); ?>" class="oar-pay-primary-btn">
                                <i class="fa fa-refresh mr-2" aria-hidden="true"></i>ลองใหม่อีกครั้ง
                            </a>
                        <?php } ?>
                        <a href="<?= payEsc($backUrl); ?>" class="oar-pay-outline-btn">
                            <i class="fa fa-arrow-left mr-2" aria-hidden="true"></i>กลับไปตรวจสอบคำสั่งซื้อ
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <?php include_once("../master/footer.php"); ?>

    <script src="<?= assetVer('../vendor/animsition/js/animsition.min.js'); ?>"></script>
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= assetVer('../js/main.js'); ?>"></script>
</body>

</html>
