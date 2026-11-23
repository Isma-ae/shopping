<?php
    $initialOrderNo = trim((string)($_GET["order_no"] ?? ""));
    $isLoggedIn = isset($_SESSION["user_info"]);
?>

<!-- Breadcrumb -->
<section class="so-breadcrumb-bar">
    <div class="container">
        <nav class="so-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">ค้นหาจากเลขที่ใบสั่งซื้อ</span>
        </nav>
    </div>
</section>

<!-- Main Search Order Section -->
<section class="so-page-section">
    <div class="container">
        <div class="so-page-head">
            <div>
                <span class="oar-eyebrow">TRACK ORDER</span>
                <h1 class="oar-heading">ค้นหาและติดตามคำสั่งซื้อ</h1>
                <p class="so-head-sub mb-0">
                    กรอกเลขที่ใบสั่งซื้อเพื่อตรวจสอบสถานะการชำระเงิน การจัดส่ง และหมายเลขพัสดุได้ทันที
                </p>
            </div>

            <?php if ($isLoggedIn) { ?>
                <a href="?page=shipping_list" class="so-secondary-btn">
                    <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>รายการสั่งซื้อของฉัน
                </a>
            <?php } else { ?>
                <a href="login/" class="so-secondary-btn">
                    <i class="fa fa-sign-in mr-2" aria-hidden="true"></i>เข้าสู่ระบบเพื่อดูประวัติทั้งหมด
                </a>
            <?php } ?>
        </div>

        <!-- Search Card -->
        <div class="so-search-card">
            <form id="search-order-form" class="so-search-form" autocomplete="off">
                <div class="so-input-group">
                    <i class="fa fa-search so-input-icon" aria-hidden="true"></i>
                    <input type="text"
                        id="order-no-input"
                        name="order_no"
                        class="so-input"
                        value="<?= htmlspecialchars($initialOrderNo, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="กรอกเลขที่ใบสั่งซื้อ เช่น ORD20260101120000"
                        aria-label="เลขที่ใบสั่งซื้อ">
                    <button type="button" id="clear-order-no" class="so-clear-btn<?= $initialOrderNo !== '' ? ' is-visible' : ''; ?>" title="ล้างคำค้นหา" aria-label="ล้างคำค้นหา">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <button type="submit" id="search-order" class="so-submit-btn">
                    <i class="fa fa-search mr-2" aria-hidden="true"></i>
                    <span>ค้นหาคำสั่งซื้อ</span>
                </button>
            </form>
            <div class="so-search-hint">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <span>สามารถดูเลขที่ใบสั่งซื้อได้จากหน้ายืนยันคำสั่งซื้อ หรือใบแจ้งชำระเงินของคุณ (ขึ้นต้นด้วย <strong>ORD...</strong>)</span>
            </div>
        </div>

        <!-- Result Area -->
        <div id="order-list" class="so-results-wrap" data-initial-order="<?= htmlspecialchars($initialOrderNo, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="so-state-card">
                <div class="so-state-icon">
                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                </div>
                <h3 class="so-state-title">กรอกเลขที่ใบสั่งซื้อเพื่อเริ่มค้นหา</h3>
                <p class="so-state-desc">
                    ตรวจสอบรายละเอียดสินค้า สถานะการชำระเงิน ช่องทางการรับสินค้า และเลขพัสดุได้จากที่นี่
                </p>
            </div>
        </div>
    </div>
</section>
