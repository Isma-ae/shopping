<!-- Breadcrumb -->
<section class="contact-breadcrumb-bar">
    <div class="container">
        <nav class="contact-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">ติดต่อเรา</span>
        </nav>
    </div>
</section>

<!-- Contact Page Section -->
<section class="contact-page-section">
    <div class="container">
        <div class="contact-page-head">
            <span class="oar-eyebrow">CONTACT US</span>
            <h1 class="oar-heading">ติดต่อเรา</h1>
            <p class="contact-head-sub mb-0">
                มีคำถามเกี่ยวกับสินค้า การจัดส่ง หรือสถานะคำสั่งซื้อ ส่งข้อความถึงทีมงานสำนักวิทยบริการ ม.อ.ปัตตานี ได้ทันที
            </p>
        </div>

        <div class="row align-items-stretch">
            <!-- Left: Message Form Card -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="contact-form-card">
                    <div class="contact-card-head">
                        <div class="contact-card-icon">
                            <i class="fa fa-envelope-o" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="contact-card-title">ส่งข้อความถึงเรา</h2>
                            <p class="contact-card-sub mb-0">กรอกรายละเอียดด้านล่าง ทีมงานจะตรวจสอบและติดต่อกลับทางอีเมลของคุณ</p>
                        </div>
                    </div>

                    <form id="contact-form" class="contact-form-body">
                        <!-- Honeypot anti-bot field -->
                        <div class="contact-hp" aria-hidden="true">
                            <label for="contact_website">Website</label>
                            <input type="text" id="contact_website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="contact-field">
                            <label class="contact-label" for="contact_email">
                                ที่อยู่อีเมลของคุณ <span class="contact-req">*</span>
                            </label>
                            <div class="contact-input-wrap">
                                <i class="fa fa-envelope-o contact-input-icon" aria-hidden="true"></i>
                                <input class="contact-input" id="contact_email" type="email" name="email"
                                    placeholder="example@psu.ac.th" autocomplete="email">
                            </div>
                        </div>

                        <div class="contact-field">
                            <div class="contact-label-row">
                                <label class="contact-label mb-0" for="contact_msg">
                                    ข้อความ <span class="contact-req">*</span>
                                </label>
                                <span class="contact-char-count" id="contact-char-count">0 / 2,000</span>
                            </div>
                            <textarea class="contact-textarea" id="contact_msg" name="msg" rows="5" maxlength="2000"
                                placeholder="เราสามารถช่วยอะไรคุณได้บ้าง? เช่น สอบถามไซส์ สต๊อกสินค้า หรือเลขที่ใบสั่งซื้อ"></textarea>
                        </div>

                        <!-- CAPTCHA Verification -->
                        <div class="contact-captcha-box">
                            <label class="contact-captcha-label" for="captcha-input">
                                <i class="fa fa-shield" aria-hidden="true"></i>
                                ยืนยันตัวตนเพื่อป้องกันบอท <span class="contact-req ml-1">*</span>
                            </label>
                            <div class="contact-captcha-row">
                                <div class="contact-captcha-img-wrap">
                                    <img id="captcha-img" src="api/captcha.php?t=<?= time(); ?>" alt="รหัสยืนยัน CAPTCHA">
                                    <button type="button" class="contact-captcha-refresh" id="refresh-captcha"
                                        title="เปลี่ยนรหัสใหม่" aria-label="เปลี่ยนรหัส CAPTCHA ใหม่">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <input type="text" id="captcha-input" name="captcha" class="contact-captcha-input"
                                    placeholder="กรอกรหัส 5 หลัก" maxlength="5" autocomplete="off" spellcheck="false">
                            </div>
                        </div>

                        <button type="button" id="send-message" class="contact-submit-btn">
                            <i class="fa fa-paper-plane-o mr-2" aria-hidden="true"></i>
                            <span>ส่งข้อความ</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Contact Info -->
            <div class="col-lg-5">
                <div class="contact-info-card">
                    <span class="contact-info-tag">OFFICE OF ACADEMIC RESOURCES · PSU PATTANI</span>
                    <h2 class="contact-info-title">ช่องทางการติดต่อ</h2>
                    <p class="contact-info-desc">
                        ติดต่อสอบถามข้อมูลสินค้า นัดรับสินค้าหน้าร้าน หรือแจ้งปัญหาการใช้งานระบบได้ผ่านช่องทางด้านล่าง
                    </p>

                    <ul class="contact-info-list">
                        <li>
                            <div class="contact-info-icon">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </div>
                            <div class="contact-info-text">
                                <strong>ที่อยู่และจุดรับสินค้าหน้าร้าน</strong>
                                <p>
                                    เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี<br>
                                    เลขที่ 181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล อ.เมือง จ.ปัตตานี 94000
                                </p>
                                <a href="https://maps.google.com/?q=%E0%B8%AA%E0%B8%B3%E0%B8%99%E0%B8%B1%E0%B8%81%E0%B8%A7%E0%B8%B4%E0%B8%97%E0%B8%A2%E0%B8%9A%E0%B8%A3%E0%B8%B4%E0%B8%81%E0%B8%B2%E0%B8%A3+%E0%B8%A1.%E0%B8%AD.%E0%B8%9B%E0%B8%B1%E0%B8%95%E0%B8%95%E0%B8%B2%E0%B8%99%E0%B8%B5"
                                    target="_blank" rel="noopener noreferrer" class="contact-map-pill">
                                    <i class="fa fa-location-arrow mr-1" aria-hidden="true"></i>
                                    เปิดแผนที่ Google Maps
                                </a>
                            </div>
                        </li>

                        <li>
                            <div class="contact-info-icon">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                            </div>
                            <div class="contact-info-text">
                                <strong>โทรศัพท์</strong>
                                <p class="mb-0">
                                    จันทร์ – ศุกร์: <a href="tel:073331300">073-331-300</a><br>
                                    เสาร์ – อาทิตย์: <a href="tel:073313486">073-313-486</a><br>
                                    โทรสาร: 073-333-587
                                </p>
                            </div>
                        </li>

                        <li>
                            <div class="contact-info-icon">
                                <i class="fa fa-facebook" aria-hidden="true"></i>
                            </div>
                            <div class="contact-info-text">
                                <strong>Facebook Page &amp; ช่องทางออนไลน์</strong>
                                <p class="mb-2">
                                    <a href="https://www.facebook.com/OAR.PSU" target="_blank" rel="noopener noreferrer">
                                        สำนักวิทยบริการ ม.อ.ปัตตานี
                                        <i class="fa fa-external-link ml-1" aria-hidden="true"></i>
                                    </a>
                                </p>
                                <div class="contact-social-row">
                                    <a href="https://www.facebook.com/OAR.PSU" target="_blank" rel="noopener noreferrer" title="Facebook">
                                        <i class="fa fa-facebook" aria-hidden="true"></i>
                                    </a>
                                    <a href="https://www.youtube.com/@OARChannel" target="_blank" rel="noopener noreferrer" title="YouTube">
                                        <i class="fa fa-youtube-play" aria-hidden="true"></i>
                                    </a>
                                    <a href="https://www.oas.psu.ac.th/" target="_blank" rel="noopener noreferrer" title="เว็บไซต์สำนักวิทยบริการ">
                                        <i class="fa fa-globe" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
