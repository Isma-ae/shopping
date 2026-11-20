<?php
    if (!function_exists("footerFetchCategories")) {
        function footerFetchCategories($DB) {
            $sql = "
                SELECT
                    c.category_id,
                    c.category_name
                FROM category c
                INNER JOIN product p
                    ON c.category_id = p.category_id
                    AND p.product_status = 2
                GROUP BY c.category_id, c.category_name
                ORDER BY c.category_id ASC
            ";
            $res = $DB->Query($sql);
            $rows = [];
            if ($res && $res !== true) {
                while ($row = $res->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            return $rows;
        }
    }

    $footerCategories = footerFetchCategories($DB);
    $isLoggedIn = isset($_SESSION["user_info"]);

    $footerBase = "";
    $rootDir = realpath(dirname(__DIR__));
    $scriptDir = !empty($_SERVER["SCRIPT_FILENAME"]) ? realpath(dirname($_SERVER["SCRIPT_FILENAME"])) : false;
    if ($rootDir && $scriptDir && $scriptDir !== $rootDir && strpos($scriptDir, $rootDir) === 0) {
        $relPath = trim(str_replace("\\", "/", substr($scriptDir, strlen($rootDir))), "/");
        if ($relPath !== "") {
            $footerBase = str_repeat("../", substr_count($relPath, "/") + 1);
        }
    }
    $homeHref = $footerBase !== "" ? $footerBase : "./";
?>
<footer class="oar-footer">
    <div class="container">
        <div class="row oar-footer-main">
            <!-- Col 1: Brand & Payment -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <a href="<?= $homeHref; ?>" class="oar-footer-brand" aria-label="OAR Shopping หน้าแรก">
                    <img src="<?= $footerBase; ?>images/icons/S__8732697.png" alt="OAR Shopping">
                </a>
                <p class="oar-footer-about">
                    ร้านค้าออนไลน์จำหน่ายสินค้าที่ระลึกและของใช้ประจำสำนักวิทยบริการ
                    มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี
                </p>

                <div class="oar-footer-pay">
                    <img src="<?= $footerBase; ?>images/icons/Thai_QR_Logo.png" alt="Thai QR Payment">
                    <span>รองรับชำระเงินผ่าน Thai QR PromptPay</span>
                </div>

                <div class="oar-footer-social">
                    <a href="https://www.facebook.com/OAR.PSU" target="_blank" rel="noopener noreferrer"
                        title="Facebook สำนักวิทยบริการ ม.อ.ปัตตานี" aria-label="Facebook">
                        <i class="fa fa-facebook" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.youtube.com/@OARChannel" target="_blank" rel="noopener noreferrer"
                        title="YouTube OAR Channel" aria-label="YouTube">
                        <i class="fa fa-youtube-play" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.oas.psu.ac.th/" target="_blank" rel="noopener noreferrer"
                        title="เว็บไซต์สำนักวิทยบริการ ม.อ.ปัตตานี" aria-label="Website">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <!-- Col 2: Categories -->
            <div class="col-6 col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h4 class="oar-footer-title">หมวดหมู่สินค้า</h4>
                <ul class="oar-footer-links">
                    <li>
                        <a href="<?= $footerBase; ?>?page=product">สินค้าทั้งหมด</a>
                    </li>
                    <?php foreach ($footerCategories as $vcate) { ?>
                    <li>
                        <a href="<?= $footerBase; ?>?page=product&amp;category=<?= (int)$vcate["category_id"]; ?>">
                            <?= htmlspecialchars((string)$vcate["category_name"], ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Col 3: Customer Service -->
            <div class="col-6 col-lg-3 col-md-6 mb-4 mb-md-0">
                <h4 class="oar-footer-title">ช่วยเหลือและบริการ</h4>
                <ul class="oar-footer-links">
                    <li>
                        <a href="<?= $footerBase; ?>?page=search-order">ติดตามคำสั่งซื้อ</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=evidence">แจ้งหลักฐานการโอนเงิน</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=shipping">ข้อมูลการจัดส่งสินค้า</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=return">เงื่อนไขการเปลี่ยนคืนสินค้า</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=contact">ติดต่อเรา / ส่งข้อความ</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=privacy">นโยบายความเป็นส่วนตัว (PDPA)</a>
                    </li>
                    <?php if ($isLoggedIn) { ?>
                    <li>
                        <a href="<?= $footerBase; ?>?page=shipping_list">รายการสั่งซื้อของฉัน</a>
                    </li>
                    <li>
                        <a href="<?= $footerBase; ?>?page=cart">ตะกร้าสินค้า</a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Col 4: Contact & Pickup -->
            <div class="col-lg-3 col-md-6">
                <h4 class="oar-footer-title">ติดต่อเรา</h4>
                <ul class="oar-footer-contact">
                    <li>
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <div>
                            สำนักวิทยบริการ ม.อ.ปัตตานี<br>
                            181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล อ.เมือง จ.ปัตตานี 94000
                            <div class="mt-1">
                                <a href="https://maps.google.com/?q=%E0%B8%AA%E0%B8%B3%E0%B8%99%E0%B8%B1%E0%B8%81%E0%B8%A7%E0%B8%B4%E0%B8%97%E0%B8%A2%E0%B8%9A%E0%B8%A3%E0%B8%B4%E0%B8%81%E0%B8%B2%E0%B8%A3+%E0%B8%A1.%E0%B8%AD.%E0%B8%9B%E0%B8%B1%E0%B8%95%E0%B8%95%E0%B8%B2%E0%B8%99%E0%B8%B5"
                                    target="_blank" rel="noopener noreferrer" class="oar-footer-map-link">
                                    ดูแผนที่ Google Maps <i class="fa fa-external-link ml-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                        <div>รับสินค้าหน้าร้าน: เคาน์เตอร์ยืมคืน สำนักวิทยบริการ</div>
                    </li>
                    <li>
                        <i class="fa fa-phone" aria-hidden="true"></i>
                        <div>
                            จ.–ศ. <a href="tel:073331300">073-331-300</a><br>
                            ส.–อา. <a href="tel:073313486">073-313-486</a>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-fax" aria-hidden="true"></i>
                        <div>โทรสาร 073-333-587</div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="oar-footer-bottom">
            <p class="mb-0">
                Copyright &copy; <?= date("Y"); ?> OAR Shopping. All rights reserved &middot;
                <a href="https://www.oas.psu.ac.th/" target="_blank" rel="noopener noreferrer">
                    Office of Academic Resources, PSU Pattani
                </a>
                &middot;
                <a href="<?= $footerBase; ?>?page=privacy">นโยบายความเป็นส่วนตัว</a>
            </p>
        </div>
    </div>
</footer>
