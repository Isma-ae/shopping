<?php
    if (!function_exists("homeFetchRows")) {
        function homeFetchRows($DB, $sql) {
            $res = $DB->Query($sql);
            if (!$res || $res === true) {
                return [];
            }
            $rows = [];
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }
    }

    if (!function_exists("homeEsc")) {
        function homeEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("homeFormatPriceRange")) {
        function homeFormatPriceRange($minPrice, $maxPrice) {
            $min = (float)$minPrice;
            $max = (float)$maxPrice;
            if ($min <= 0 && $max <= 0) {
                return "สอบถามราคา";
            }
            if ($max > $min && $min > 0) {
                return "฿" . number_format($min, 0) . " - ฿" . number_format($max, 0);
            }
            $val = $min > 0 ? $min : $max;
            return "฿" . number_format($val, 0);
        }
    }

    $slides = homeFetchRows($DB, "
        SELECT slide_id, slide_name, slide_detail, slide_img, slide_link
        FROM slide
        ORDER BY slide_id ASC
    ");

    $categories = homeFetchRows($DB, "
        SELECT
            c.category_id,
            c.category_name,
            COUNT(DISTINCT p.product_id) AS product_count,
            COALESCE(MAX(im.img_name), '') AS sample_img
        FROM category c
        INNER JOIN product p
            ON c.category_id = p.category_id
            AND p.product_status = 2
        LEFT JOIN (
            SELECT product_id, MIN(img_name) AS img_name
            FROM img
            WHERE img_main = '1'
            GROUP BY product_id
        ) im ON p.product_id = im.product_id
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_id ASC
    ");

    $products = homeFetchRows($DB, "
        SELECT
            p.product_id AS raw_product_id,
            MD5(p.product_id) AS product_hash,
            p.product_name,
            p.category_id,
            COALESCE(im.img_name, '') AS img_name,
            COALESCE(vs.min_price, 0) AS min_price,
            COALESCE(vs.max_price, 0) AS max_price,
            COALESCE(os.total_ordered, 0) AS total_sold
        FROM product p
        LEFT JOIN (
            SELECT product_id, MIN(img_name) AS img_name
            FROM img
            WHERE img_main = '1'
            GROUP BY product_id
        ) im ON p.product_id = im.product_id
        LEFT JOIN (
            SELECT
                product_id,
                MIN(NULLIF(variant_sale, 0)) AS min_price,
                MAX(NULLIF(variant_sale, 0)) AS max_price
            FROM variants
            GROUP BY product_id
        ) vs ON p.product_id = vs.product_id
        LEFT JOIN (
            SELECT
                v.product_id,
                COALESCE(SUM(od.qty), 0) AS total_ordered
            FROM order_detail od
            INNER JOIN variants v
                ON od.variant_id = v.variant_id
            INNER JOIN orders o
                ON od.order_id = o.order_id
            WHERE o.status_id IN (2, 3)
            GROUP BY v.product_id
        ) os ON p.product_id = os.product_id
        WHERE p.product_status = 2
        ORDER BY p.product_id DESC
        LIMIT 12
    ");

    $bestSellers = homeFetchRows($DB, "
        SELECT
            p.product_id AS raw_product_id,
            MD5(p.product_id) AS product_hash,
            p.product_name,
            COALESCE(c.category_name, 'สินค้าทั่วไป') AS category_name,
            COALESCE(im.img_name, '') AS img_name,
            COALESCE(vs.min_price, 0) AS min_price,
            COALESCE(SUM(od.qty), 0) AS total_sold
        FROM order_detail od
        INNER JOIN orders o
            ON od.order_id = o.order_id
            AND o.status_id IN (2, 3)
        INNER JOIN variants v
            ON od.variant_id = v.variant_id
        INNER JOIN product p
            ON v.product_id = p.product_id
            AND p.product_status = 2
        LEFT JOIN category c
            ON p.category_id = c.category_id
        LEFT JOIN (
            SELECT product_id, MIN(img_name) AS img_name
            FROM img
            WHERE img_main = '1'
            GROUP BY product_id
        ) im ON p.product_id = im.product_id
        LEFT JOIN (
            SELECT
                product_id,
                MIN(NULLIF(variant_sale, 0)) AS min_price
            FROM variants
            GROUP BY product_id
        ) vs ON p.product_id = vs.product_id
        GROUP BY p.product_id, p.product_name, c.category_name, im.img_name, vs.min_price
        HAVING total_sold > 0
        ORDER BY total_sold DESC, p.product_id DESC
        LIMIT 4
    ");
?>

<!-- Section 1: Hero Banner Slider (Full Screen) -->
<section class="section-slide home-hero">
    <div class="wrap-slick1">
        <?php if (count($slides) > 0) { ?>
            <div class="slick1">
                <?php
                    foreach ($slides as $idx => $rsl) {
                        $link = "#home-products";
                        if (!empty($rsl["slide_link"])) {
                            $link = $rsl["slide_link"];
                        } elseif (!empty($rsl["slide_detail"])) {
                            $link = "?page=slide-detail&slide_id=" . urlencode($rsl["slide_id"]);
                        }
                        $imgSrc = "file/banner/" . rawurlencode($rsl["slide_img"]);
                        $altText = !empty($rsl["slide_name"]) ? $rsl["slide_name"] : "OAR Shopping Banner " . ($idx + 1);
                ?>
                <a href="<?= homeEsc($link); ?>"
                    class="item-slick1"
                    style="background-image: url('<?= homeEsc($imgSrc); ?>');"
                    aria-label="<?= homeEsc($altText); ?>">
                    <?php if (!empty($rsl["slide_name"])) { ?>
                        <div class="home-hero-caption">
                            <span class="home-hero-caption-title"><?= homeEsc($rsl["slide_name"]); ?></span>
                        </div>
                    <?php } ?>
                </a>
                <?php } ?>
            </div>
            <div class="home-hero-dots" id="homeHeroDots" aria-label="ตัวเลือกแบนเนอร์"></div>
        <?php } else { ?>
            <div class="home-hero-fallback">
                <div class="container text-center py-5">
                    <span class="home-eyebrow">OAR PSU PATTANI</span>
                    <h1 class="home-fallback-title mt-2">OAR Shopping</h1>
                    <p class="home-fallback-sub">สินค้าที่ระลึกและของใช้ประจำสำนักวิทยบริการ มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี</p>
                    <a href="?page=product" class="btn home-btn-primary mt-2">เลือกชมสินค้า</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<!-- Section 2: Service Highlights Strip -->
<section class="home-highlights">
    <div class="container">
        <div class="row home-highlights-grid">
            <div class="col-6 col-lg-3 mb-3 mb-lg-0">
                <div class="home-highlight-item">
                    <div class="home-highlight-icon">
                        <i class="fa fa-university" aria-hidden="true"></i>
                    </div>
                    <div class="home-highlight-text">
                        <strong>รับหน้าร้านฟรี</strong>
                        <span>เคาน์เตอร์ยืมคืน สำนักวิทยบริการ</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3 mb-lg-0">
                <div class="home-highlight-item">
                    <div class="home-highlight-icon">
                        <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                    </div>
                    <div class="home-highlight-text">
                        <strong>ส่งฟรีในวิทยาเขต</strong>
                        <span>จัดส่งถึงคณะและหน่วยงาน ม.อ.ปัตตานี</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="home-highlight-item">
                    <div class="home-highlight-icon">
                        <i class="fa fa-truck" aria-hidden="true"></i>
                    </div>
                    <div class="home-highlight-text">
                        <strong>จัดส่งทั่วประเทศ</strong>
                        <span>บริการพัสดุนอกวิทยาเขต (เริ่มต้น ฿50)</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="home-highlight-item">
                    <div class="home-highlight-icon">
                        <i class="fa fa-qrcode" aria-hidden="true"></i>
                    </div>
                    <div class="home-highlight-text">
                        <strong>ชำระเงิน QR PromptPay</strong>
                        <span>สแกนจ่ายสะดวก ยืนยันยอดอัตโนมัติ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Category Showcase -->
<?php if (count($categories) > 0) { ?>
<section class="home-categories">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span class="home-eyebrow">BROWSE CATEGORIES</span>
                <h2 class="home-heading">เลือกช้อปตามหมวดหมู่</h2>
            </div>
            <a href="?page=product" class="home-link-more">
                ดูหมวดหมู่ทั้งหมด <i class="fa fa-angle-right ml-1"></i>
            </a>
        </div>

        <div class="row">
            <?php foreach ($categories as $cat) {
                $catImg = !empty($cat["sample_img"]) ? "file/product/" . $cat["sample_img"] : "images/icons/OARShopping.png";
            ?>
            <div class="col-6 col-md-3 mb-3">
                <button type="button"
                    class="home-category-card js-home-cat-trigger"
                    data-target-filter=".cat-<?= (int)$cat["category_id"]; ?>">
                    <div class="home-category-thumb">
                        <img src="<?= homeEsc($catImg); ?>"
                            alt="<?= homeEsc($cat["category_name"]); ?>"
                            onerror="this.onerror=null;this.src='images/icons/OARShopping.png';this.classList.add('is-fallback-img');">
                    </div>
                    <div class="home-category-info">
                        <span class="home-category-name"><?= homeEsc($cat["category_name"]); ?></span>
                        <span class="home-category-count"><?= (int)$cat["product_count"]; ?> รายการ</span>
                    </div>
                    <span class="home-category-arrow">
                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<!-- Section 4: Featured & New Arrivals Catalog -->
<section class="home-products" id="home-products">
    <div class="container">
        <div class="home-section-head home-products-head">
            <div>
                <span class="home-eyebrow">OAR OFFICIAL MERCHANDISE</span>
                <h2 class="home-heading">สินค้าแนะนำ &amp; มาใหม่ล่าสุด</h2>
            </div>
            <a href="?page=product" class="home-btn-outline">
                ดูสินค้าทั้งหมด <i class="fa fa-long-arrow-right ml-2"></i>
            </a>
        </div>

        <?php if (count($categories) > 0) { ?>
        <div class="home-filter-bar filter-tope-group">
            <button type="button" class="home-filter-pill how-active1" data-filter="*">
                ทั้งหมด (<?= count($products); ?>)
            </button>
            <?php foreach ($categories as $cat) { ?>
            <button type="button" class="home-filter-pill" data-filter=".cat-<?= (int)$cat["category_id"]; ?>">
                <?= homeEsc($cat["category_name"]); ?>
            </button>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (count($products) > 0) { ?>
        <div class="row isotope-grid home-product-grid">
            <?php
                foreach ($products as $row) {
                    $totalSold = (int)$row["total_sold"];
                    $catId = (int)$row["category_id"];
                    $priceText = homeFormatPriceRange($row["min_price"], $row["max_price"]);
                    $imgPath = !empty($row["img_name"]) ? "file/product/" . $row["img_name"] : "images/icons/OARShopping.png";
                    $detailUrl = "?page=detail&product=" . urlencode($row["product_hash"]);
            ?>
            <div class="col-6 col-md-4 col-lg-3 p-b-30 isotope-item cat-<?= $catId; ?>">
                <article class="oar-product-card">
                    <a href="<?= homeEsc($detailUrl); ?>" class="oar-product-thumb hov-img0">
                        <img src="<?= homeEsc($imgPath); ?>"
                            alt="<?= homeEsc($row["product_name"]); ?>"
                            onerror="this.onerror=null;this.src='images/icons/OARShopping.png';this.classList.add('is-fallback-img');">

                        <span class="oar-quick-cta">ดูรายละเอียดสินค้า</span>
                    </a>

                    <div class="oar-product-body">
                        <h3 class="oar-product-title">
                            <a href="<?= homeEsc($detailUrl); ?>" class="js-name-b2">
                                <?= homeEsc($row["product_name"]); ?>
                            </a>
                        </h3>

                        <div class="oar-product-footer">
                            <span class="oar-product-price"><?= homeEsc($priceText); ?></span>
                            <?php if ($totalSold > 0) { ?>
                                <span class="oar-product-sold">ขายแล้ว <?= number_format($totalSold); ?> ชิ้น</span>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="home-empty-products text-center py-5">
            <p class="text-muted mb-3">ยังไม่มีสินค้าเปิดจำหน่ายในขณะนี้</p>
        </div>
        <?php } ?>
    </div>
</section>

<!-- Section 5: Best Sellers Spotlight -->
<?php if (count($bestSellers) > 0) { ?>
<section class="home-bestsellers">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span class="home-eyebrow">POPULAR ITEMS</span>
                <h2 class="home-heading">สินค้ายอดนิยมขายดี</h2>
            </div>
            <a href="?page=product" class="home-link-more">
                เลือกดูทั้งหมด <i class="fa fa-angle-right ml-1"></i>
            </a>
        </div>

        <div class="row">
            <?php foreach ($bestSellers as $rank => $bs) {
                $bsUrl = "?page=detail&product=" . urlencode($bs["product_hash"]);
                $bsImg = !empty($bs["img_name"]) ? "file/product/" . $bs["img_name"] : "images/icons/OARShopping.png";
                $bsPrice = homeFormatPriceRange($bs["min_price"], $bs["min_price"]);
            ?>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <a href="<?= homeEsc($bsUrl); ?>" class="home-bestseller-card">
                    <span class="home-rank-pill">#<?= $rank + 1; ?> ขายดี</span>
                    <div class="home-bestseller-img">
                        <img src="<?= homeEsc($bsImg); ?>"
                            alt="<?= homeEsc($bs["product_name"]); ?>"
                            onerror="this.onerror=null;this.src='images/icons/OARShopping.png';this.classList.add('is-fallback-img');">
                    </div>
                    <div class="home-bestseller-body">
                        <span class="home-bestseller-cat"><?= homeEsc($bs["category_name"]); ?></span>
                        <h4 class="home-bestseller-title"><?= homeEsc($bs["product_name"]); ?></h4>
                        <div class="home-bestseller-bottom">
                            <span class="home-bestseller-price"><?= homeEsc($bsPrice); ?></span>
                            <span class="home-bestseller-sold">ขายแล้ว <?= number_format((int)$bs["total_sold"]); ?> ชิ้น</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<!-- Section 6: OAR PSU Story & How to Order CTA -->
<section class="home-cta-band">
    <div class="container">
        <div class="home-cta-card">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <span class="home-cta-tag">OFFICE OF ACADEMIC RESOURCES · PSU PATTANI</span>
                    <h2 class="home-cta-title">ของที่ระลึกสำนักวิทยบริการ<br>ม.อ.ปัตตานี</h2>
                    <p class="home-cta-desc">
                        เลือกชมเสื้อยืด เสื้อโปโล กระบอกน้ำสแตนเลส 304 และกระเป๋าผ้าคอลเลกชันพิเศษ
                        สั่งซื้อง่ายได้ทั้งสมาชิก PSU Passport และบุคคลทั่วไป
                    </p>
                    <div class="home-cta-actions">
                        <a href="?page=product" class="home-btn-white">
                            เลือกซื้อสินค้า <i class="fa fa-shopping-bag ml-2"></i>
                        </a>
                        <a href="?page=search-order" class="home-btn-ghost">
                            ติดตามคำสั่งซื้อ
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="home-steps">
                        <div class="home-step-item">
                            <span class="home-step-num">01</span>
                            <div>
                                <strong>เลือกสินค้า &amp; สี/ไซส์</strong>
                                <p>ดูรายละเอียดขนาดและสต๊อกคงเหลือจริงได้ทันที</p>
                            </div>
                        </div>
                        <div class="home-step-item">
                            <span class="home-step-num">02</span>
                            <div>
                                <strong>เลือกวิธีรับหรือจัดส่ง</strong>
                                <p>รับที่เคาน์เตอร์สำนักฯ ส่งในวิทยาเขต หรือส่งพัสดุทั่วไทย</p>
                            </div>
                        </div>
                        <div class="home-step-item">
                            <span class="home-step-num">03</span>
                            <div>
                                <strong>สแกน QR PromptPay</strong>
                                <p>ชำระเงินผ่าน Mobile Banking ตรวจสอบยอดอัตโนมัติ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
