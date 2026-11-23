<?php
    $isLoggedIn = isset($_SESSION["user_info"]);
?>

<!-- Breadcrumb -->
<section class="ret-breadcrumb-bar">
    <div class="container">
        <nav class="ret-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">เงื่อนไขการเปลี่ยนคืนสินค้า</span>
        </nav>
    </div>
</section>

<!-- Main Return Policy Section -->
<section class="ret-page-section">
    <div class="container">
        <div class="ret-page-head">
            <div>
                <span class="oar-eyebrow">RETURN &amp; EXCHANGE</span>
                <h1 class="oar-heading">เงื่อนไขการเปลี่ยนและคืนสินค้า</h1>
                <p class="ret-head-sub mb-0">
                    เพื่อความมั่นใจในการสั่งซื้อสินค้าทุกชิ้นกับ OAR Shopping หากสินค้าชำรุดจากกระบวนการผลิต สามารถขอเปลี่ยนคืนได้ตามเงื่อนไขด้านล่าง
                </p>
            </div>

            <div>
                <?php if ($isLoggedIn) { ?>
                    <a href="?page=shipping_list" class="ret-secondary-btn">
                        <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>รายการสั่งซื้อของฉัน
                    </a>
                <?php } else { ?>
                    <a href="?page=contact" class="ret-secondary-btn">
                        <i class="fa fa-envelope-o mr-2" aria-hidden="true"></i>ติดต่อเจ้าหน้าที่
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- 3 Key Conditions -->
        <div class="row ret-rules-row">
            <div class="col-md-4 mb-4">
                <article class="ret-rule-card">
                    <div class="ret-rule-top">
                        <div class="ret-rule-icon">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                        </div>
                        <span class="ret-rule-pill">ภายใน 7 วัน</span>
                    </div>
                    <h2 class="ret-rule-title">แจ้งเปลี่ยนคืนภายใน 7 วัน</h2>
                    <p class="ret-rule-desc">
                        หากสินค้าชำรุดเสียหายจากกระบวนการผลิต หรือได้รับสินค้าไม่ตรงตามคำสั่งซื้อ สามารถแจ้งขอเปลี่ยนคืนได้ภายใน <strong>7 วัน</strong> นับจากวันที่ได้รับสินค้า
                    </p>
                    <div class="ret-rule-meta">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span>นับจากวันที่รับสินค้าหน้าร้านหรือวันที่พัสดุถึงปลายทาง</span>
                    </div>
                </article>
            </div>

            <div class="col-md-4 mb-4">
                <article class="ret-rule-card">
                    <div class="ret-rule-top">
                        <div class="ret-rule-icon">
                            <i class="fa fa-tag" aria-hidden="true"></i>
                        </div>
                        <span class="ret-rule-pill is-green">สภาพสมบูรณ์</span>
                    </div>
                    <h2 class="ret-rule-title">สินค้าสภาพเดิม ป้ายครบถ้วน</h2>
                    <p class="ret-rule-desc">
                        สินค้าต้องอยู่ในสภาพสมบูรณ์ ไม่ผ่านการใช้งาน ไม่ผ่านการซักรีด และยังมี<strong>ป้ายสินค้า</strong> รวมถึงบรรจุภัณฑ์เดิมครบถ้วน
                    </p>
                    <div class="ret-rule-meta">
                        <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                        <span>ไม่รับเปลี่ยนคืนสินค้าที่ผ่านการใช้งานหรือซักแล้ว</span>
                    </div>
                </article>
            </div>

            <div class="col-md-4 mb-4">
                <article class="ret-rule-card">
                    <div class="ret-rule-top">
                        <div class="ret-rule-icon">
                            <i class="fa fa-video-camera" aria-hidden="true"></i>
                        </div>
                        <span class="ret-rule-pill is-amber">หลักฐานสำคัญ</span>
                    </div>
                    <h2 class="ret-rule-title">บันทึกวิดีโอขณะเปิดกล่อง</h2>
                    <p class="ret-rule-desc">
                        ขอความร่วมมือผู้ซื้อบันทึก<strong>คลิปวิดีโอต่อเนื่อง</strong>ขณะเปิดกล่องหรือซองพัสดุ จนกระทั่งเห็นสภาพสินค้าที่ชำรุดเสียหายอย่างชัดเจน
                    </p>
                    <div class="ret-rule-meta">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                        <span>ใช้เป็นหลักฐานประกอบการรับเปลี่ยนคืนสินค้า</span>
                    </div>
                </article>
            </div>
        </div>

        <!-- Steps & Support Card -->
        <div class="row align-items-stretch">
            <!-- Left: Return Steps -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="ret-detail-card">
                    <div class="ret-card-head">
                        <div class="ret-card-icon">
                            <i class="fa fa-refresh" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="ret-card-title">ขั้นตอนการขอเปลี่ยนหรือคืนสินค้า</h2>
                            <p class="ret-card-sub mb-0">ดำเนินการง่าย ๆ เพียง 3 ขั้นตอน เจ้าหน้าที่พร้อมดูแลอย่างรวดเร็ว</p>
                        </div>
                    </div>

                    <div class="ret-detail-body">
                        <ol class="ret-timeline">
                            <li>
                                <div class="ret-step-num">1</div>
                                <div class="ret-step-content">
                                    <h3>บันทึกคลิปวิดีโอขณะแกะพัสดุและตรวจเช็กสินค้า</h3>
                                    <p>
                                        เริ่มบันทึกวิดีโอก่อนเปิดกล่องหรือบรรจุภัณฑ์แบบไม่ตัดต่อ จนเห็นตัวสินค้าและจุดที่ชำรุดเสียหายชัดเจน พร้อมเก็บรักษาป้ายสินค้าและบรรจุภัณฑ์ให้อยู่ในสภาพสมบูรณ์
                                    </p>
                                </div>
                            </li>

                            <li>
                                <div class="ret-step-num">2</div>
                                <div class="ret-step-content">
                                    <h3>ติดต่อเจ้าหน้าที่พร้อมแจ้งเลขที่ใบสั่งซื้อ</h3>
                                    <p>
                                        แจ้ง<strong>เลขที่ใบสั่งซื้อ (ORD...)</strong> พร้อมแนบรายละเอียดปัญหาและหลักฐานวิดีโอผ่านหน้า
                                        <a href="?page=contact">ติดต่อเรา</a> หรือช่องทาง Facebook Page / โทรศัพท์ของสำนักวิทยบริการ ภายใน <strong>7 วัน</strong> นับจากวันที่ได้รับสินค้า
                                    </p>
                                </div>
                            </li>

                            <li>
                                <div class="ret-step-num">3</div>
                                <div class="ret-step-content">
                                    <h3>ส่งคืนและรับสินค้าชิ้นใหม่</h3>
                                    <p>
                                        เมื่อเจ้าหน้าที่ตรวจสอบและยืนยันสิทธิ์เรียบร้อยแล้ว ท่านสามารถนำสินค้ามาเปลี่ยนได้ที่เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี หรือส่งพัสดุคืนตามคำแนะนำของเจ้าหน้าที่
                                    </p>
                                </div>
                            </li>
                        </ol>

                        <div class="ret-note-box">
                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                            <div>
                                <strong>หมายเหตุสำคัญ:</strong>
                                ขอสงวนสิทธิ์ไม่รับเปลี่ยนหรือคืนสินค้าในกรณีที่สินค้าผ่านการใช้งาน/การซักแล้ว ป้ายสินค้าชำรุดสูญหาย หรือไม่มีคลิปวิดีโอขณะเปิดบรรจุภัณฑ์มายืนยัน สามารถดูข้อมูลการจัดส่งเพิ่มเติมได้ที่หน้า
                                <a href="?page=shipping">การจัดส่งสินค้า</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Visual & Contact Card -->
            <div class="col-lg-5">
                <div class="ret-side-card">
                    <div class="ret-visual-wrap">
                        <img src="images/images.jpg" alt="เงื่อนไขการเปลี่ยนและคืนสินค้า OAR Shopping">
                        <span class="ret-visual-badge">
                            <i class="fa fa-shield mr-1" aria-hidden="true"></i>รับประกันเปลี่ยนคืนภายใน 7 วัน
                        </span>
                    </div>

                    <div class="ret-side-body">
                        <span class="ret-side-tag">CUSTOMER CARE · OAR PSU PATTANI</span>
                        <h3 class="ret-side-title">จุดรับเปลี่ยนคืนและติดต่อเจ้าหน้าที่</h3>
                        <p class="ret-side-address">
                            <i class="fa fa-map-marker mr-2" aria-hidden="true"></i>
                            เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี<br>
                            เลขที่ 181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล อ.เมือง จ.ปัตตานี 94000
                        </p>

                        <div class="ret-side-phones">
                            <div><i class="fa fa-phone mr-2" aria-hidden="true"></i>จันทร์ – ศุกร์: <a href="tel:073331300">073-331-300</a></div>
                            <div><i class="fa fa-phone mr-2" aria-hidden="true"></i>เสาร์ – อาทิตย์: <a href="tel:073313486">073-313-486</a></div>
                        </div>

                        <div class="ret-side-actions">
                            <a href="?page=contact" class="ret-side-btn ret-side-btn--primary">
                                <i class="fa fa-envelope-o mr-2" aria-hidden="true"></i>แจ้งเปลี่ยนคืนสินค้า
                            </a>
                            <a href="?page=search-order" class="ret-side-btn ret-side-btn--ghost">
                                <i class="fa fa-search mr-2" aria-hidden="true"></i>ค้นหาคำสั่งซื้อ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
