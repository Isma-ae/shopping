<?php
    $isLoggedIn = isset($_SESSION["user_info"]);
?>

<!-- Breadcrumb -->
<section class="ship-breadcrumb-bar">
    <div class="container">
        <nav class="ship-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">การจัดส่งสินค้า</span>
        </nav>
    </div>
</section>

<!-- Main Shipping Info Section -->
<section class="ship-page-section">
    <div class="container">
        <div class="ship-page-head">
            <div>
                <span class="oar-eyebrow">SHIPPING &amp; DELIVERY</span>
                <h1 class="oar-heading">การจัดส่งสินค้าและอัตราค่าบริการ</h1>
                <p class="ship-head-sub mb-0">
                    เลือกช่องทางการรับสินค้าที่สะดวกได้ทั้งรับด้วยตนเองหน้าร้าน ส่งฟรีภายในวิทยาเขตปัตตานี และจัดส่งพัสดุทั่วประเทศ
                </p>
            </div>

            <div class="ship-head-actions">
                <?php if ($isLoggedIn) { ?>
                    <a href="?page=shipping_list" class="ship-secondary-btn">
                        <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>รายการสั่งซื้อของฉัน
                    </a>
                <?php } else { ?>
                    <a href="?page=search-order" class="ship-secondary-btn">
                        <i class="fa fa-search mr-2" aria-hidden="true"></i>ติดตามคำสั่งซื้อ
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- 3 Shipping Methods -->
        <div class="row ship-methods-row">
            <div class="col-md-4 mb-4">
                <article class="ship-method-card">
                    <div class="ship-method-top">
                        <div class="ship-method-icon">
                            <i class="fa fa-university" aria-hidden="true"></i>
                        </div>
                        <span class="ship-fee-pill is-free">ฟรีค่าจัดส่ง</span>
                    </div>
                    <h2 class="ship-method-title">รับสินค้าหน้าร้าน</h2>
                    <p class="ship-method-desc">
                        รับสินค้าด้วยตนเองได้ที่เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี เพียงแจ้งเลขที่ใบสั่งซื้อกับเจ้าหน้าที่
                    </p>
                    <div class="ship-method-meta">
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <span>เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี</span>
                    </div>
                </article>
            </div>

            <div class="col-md-4 mb-4">
                <article class="ship-method-card">
                    <div class="ship-method-top">
                        <div class="ship-method-icon">
                            <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                        </div>
                        <span class="ship-fee-pill is-free">ฟรีค่าจัดส่ง</span>
                    </div>
                    <h2 class="ship-method-title">ส่งภายในวิทยาเขต</h2>
                    <p class="ship-method-desc">
                        บริการนำส่งสินค้าฟรีถึงคณะ หน่วยงาน อาคาร หรือห้องทำงานภายในมหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี
                    </p>
                    <div class="ship-method-meta">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span>จัดส่งภายใน 1 วันทำการ (วันและเวลาราชการ)</span>
                    </div>
                </article>
            </div>

            <div class="col-md-4 mb-4">
                <article class="ship-method-card">
                    <div class="ship-method-top">
                        <div class="ship-method-icon">
                            <i class="fa fa-truck" aria-hidden="true"></i>
                        </div>
                        <span class="ship-fee-pill">เริ่มต้น ฿50</span>
                    </div>
                    <h2 class="ship-method-title">ส่งพัสดุทั่วประเทศ</h2>
                    <p class="ship-method-desc">
                        จัดส่งพัสดุภายนอกวิทยาเขตทั่วประเทศ อัตราค่าบริการชิ้นแรก <strong>฿50</strong> และชิ้นถัดไปชิ้นละ <strong>฿10</strong> พร้อมแจ้งเลขพัสดุ
                    </p>
                    <div class="ship-method-meta">
                        <i class="fa fa-barcode" aria-hidden="true"></i>
                        <span>แจ้งหมายเลขติดตามพัสดุในระบบทุกออเดอร์</span>
                    </div>
                </article>
            </div>
        </div>

        <!-- Details & Visual Showcase -->
        <div class="row align-items-stretch">
            <!-- Left: Steps & Conditions -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="ship-detail-card">
                    <div class="ship-card-head">
                        <div class="ship-card-icon">
                            <i class="fa fa-list-alt" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="ship-card-title">ขั้นตอนและเงื่อนไขการจัดส่ง</h2>
                            <p class="ship-card-sub mb-0">รายละเอียดการยืนยันคำสั่งซื้อ รอบจัดส่ง และการรับใบเสร็จรับเงิน</p>
                        </div>
                    </div>

                    <div class="ship-detail-body">
                        <ol class="ship-timeline">
                            <li>
                                <div class="ship-step-num">1</div>
                                <div class="ship-step-content">
                                    <h3>กรอกข้อมูลผู้รับและชำระเงินให้สมบูรณ์</h3>
                                    <p>
                                        กรุณาตรวจสอบชื่อผู้รับ หมายเลขโทรศัพท์ และที่อยู่จัดส่งให้ถูกต้อง พร้อมชำระเงินผ่าน
                                        <strong>Thai QR PromptPay</strong> ภายในระยะเวลาที่กำหนด (24 ชั่วโมง) เพื่อให้คำสั่งซื้อสมบูรณ์
                                        หากชำระแล้วสถานะยังไม่ปรับ สามารถแนบสลิปที่หน้า
                                        <a href="?page=evidence">แจ้งหลักฐานการโอนเงิน</a> ได้ทันที
                                    </p>
                                </div>
                            </li>

                            <li>
                                <div class="ship-step-num">2</div>
                                <div class="ship-step-content">
                                    <h3>จัดส่งสินค้าภายใน 1 วันทำการ</h3>
                                    <p>
                                        เมื่อระบบหรือเจ้าหน้าที่ยืนยันการชำระเงินเรียบร้อยแล้ว ทางร้านจะดำเนินการจัดเตรียมและจัดส่งสินค้าภายใน
                                        <strong>1 วันทำการ</strong> (ในวันและเวลาราชการ) พร้อมอัปเดตชื่อบริษัทขนส่งและ
                                        <strong>หมายเลขติดตามพัสดุ</strong> ให้ผู้ซื้อตรวจสอบได้ทันที
                                    </p>
                                </div>
                            </li>

                            <li>
                                <div class="ship-step-num">3</div>
                                <div class="ship-step-content">
                                    <h3>ตรวจสอบใบสำคัญรับเงินภายใน 2 วันทำการ</h3>
                                    <p>
                                        ระบบจะออกใบสำคัญรับเงินอิเล็กทรอนิกส์ไว้เป็นหลักฐานการสั่งซื้อ โดยท่านสามารถเปิดดูและดาวน์โหลดใบเสร็จรับเงินได้ในหน้า
                                        <a href="<?= $isLoggedIn ? '?page=shipping_list' : '?page=search-order'; ?>">รายการสั่งซื้อ / ติดตามคำสั่งซื้อ</a>
                                        ภายใน <strong>2 วันทำการ</strong> หลังชำระเงิน
                                    </p>
                                </div>
                            </li>
                        </ol>

                        <div class="ship-note-box">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <div>
                                <strong>หมายเหตุ:</strong>
                                กรณีสินค้าเป็นรายการสั่งจองล่วงหน้า หรือสั่งซื้อในช่วงวันหยุดราชการ รอบการจัดส่งอาจปรับตามกำหนดการที่ระบุไว้ในหน้ารายละเอียดสินค้า หากพบสินค้าชำรุดสามารถดูรายละเอียดได้ที่
                                <a href="?page=return">เงื่อนไขการเปลี่ยนคืนสินค้า</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Image & Pickup / Contact Card -->
            <div class="col-lg-5">
                <div class="ship-side-card">
                    <div class="ship-visual-wrap">
                        <img src="images/delivery-carn.jpg" alt="บริการจัดส่งสินค้า OAR Shopping">
                        <span class="ship-visual-badge">
                            <i class="fa fa-bolt mr-1" aria-hidden="true"></i>จัดส่งไวใน 1 วันทำการ
                        </span>
                    </div>

                    <div class="ship-side-body">
                        <span class="ship-side-tag">PICKUP &amp; SUPPORT · OAR PSU PATTANI</span>
                        <h3 class="ship-side-title">จุดรับสินค้าและสอบถามสถานะ</h3>
                        <p class="ship-side-address">
                            <i class="fa fa-map-marker mr-2" aria-hidden="true"></i>
                            เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี<br>
                            เลขที่ 181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล อ.เมือง จ.ปัตตานี 94000
                        </p>

                        <div class="ship-side-phones">
                            <div><i class="fa fa-phone mr-2" aria-hidden="true"></i>จันทร์ – ศุกร์: <a href="tel:073331300">073-331-300</a></div>
                            <div><i class="fa fa-phone mr-2" aria-hidden="true"></i>เสาร์ – อาทิตย์: <a href="tel:073313486">073-313-486</a></div>
                        </div>

                        <div class="ship-side-actions">
                            <a href="?page=search-order" class="ship-side-btn ship-side-btn--primary">
                                <i class="fa fa-search mr-2" aria-hidden="true"></i>ค้นหาเลขที่ใบสั่งซื้อ
                            </a>
                            <a href="?page=contact" class="ship-side-btn ship-side-btn--ghost">
                                <i class="fa fa-envelope-o mr-2" aria-hidden="true"></i>ติดต่อเจ้าหน้าที่
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
