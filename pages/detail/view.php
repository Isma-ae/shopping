<?php
    if (!function_exists("detailEsc")) {
        function detailEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    $productHash = $_GET["product"] ?? "";
    $productHashEsc = $DB->Escape($productHash);

    $sql = "SELECT
                p.*,
                COALESCE(c.category_name, 'สินค้าทั่วไป') AS category_name,
                MIN(NULLIF(v.variant_sale, 0)) AS min_price,
                MAX(NULLIF(v.variant_sale, 0)) AS max_price,
                COALESCE(os.total_sold, 0) AS total_sold
            FROM product p
            INNER JOIN variants v
                ON p.product_id = v.product_id
            LEFT JOIN category c
                ON p.category_id = c.category_id
            LEFT JOIN (
                SELECT
                    v2.product_id,
                    COALESCE(SUM(od.qty), 0) AS total_sold
                FROM order_detail od
                INNER JOIN variants v2
                    ON od.variant_id = v2.variant_id
                INNER JOIN orders o
                    ON od.order_id = o.order_id
                WHERE o.status_id IN (2, 3)
                GROUP BY v2.product_id
            ) os ON p.product_id = os.product_id
            WHERE MD5(p.product_id) = '{$productHashEsc}'
            GROUP BY p.product_id";
    $obj = $DB->QueryObj($sql);

    if (count($obj) === 0) {
?>
<section class="detail-not-found">
    <div class="container text-center py-5">
        <h3 class="mb-3">ไม่พบข้อมูลสินค้า</h3>
        <p class="text-muted mb-4">สินค้านี้อาจถูกนำออกจากระบบหรือลิงก์ไม่ถูกต้อง</p>
        <a href="?page=product" class="detail-btn-primary">กลับไปหน้ารวมสินค้า</a>
    </div>
</section>
<?php
        return;
    }

    $product = $obj[0];
    $minPrice = (float)($product["min_price"] ?? 0);
    $maxPrice = (float)($product["max_price"] ?? 0);
    if ($minPrice <= 0 && $maxPrice <= 0) {
        $product_price = "สอบถามราคา";
    } elseif ($maxPrice > $minPrice && $minPrice > 0) {
        $product_price = "฿" . number_format($minPrice, 0) . " - ฿" . number_format($maxPrice, 0);
    } else {
        $product_price = "฿" . number_format($minPrice > 0 ? $minPrice : $maxPrice, 0);
    }
    $totalSold = (int)($product["total_sold"] ?? 0);

    $obj4 = $DB->QueryObj("
        SELECT *
        FROM img
        WHERE MD5(product_id) = '{$productHashEsc}'
        ORDER BY img_main ASC, img_id ASC
    ");

    $obj2 = $DB->QueryObj("
        SELECT
            COALESCE(v.variant_color, '') AS variant_color,
            COALESCE(MAX(i.img_name), '') AS img_name
        FROM variants v
        LEFT JOIN img i
            ON v.img_id = i.img_id
        WHERE MD5(v.product_id) = '{$productHashEsc}'
        GROUP BY COALESCE(v.variant_color, '')
        ORDER BY MIN(v.variant_id) ASC
    ");

    $obj3 = $DB->QueryObj("
        SELECT
            COALESCE(variant_size, '') AS variant_size,
            COALESCE(size_detail, '') AS size_detail,
            size_order
        FROM variants
        WHERE MD5(product_id) = '{$productHashEsc}'
        GROUP BY COALESCE(variant_size, ''), COALESCE(size_detail, ''), size_order
        ORDER BY size_order ASC, MIN(variant_id) ASC
    ");

    $nonEmptyColors = [];
    foreach ($obj2 as $row2) {
        $c = trim((string)$row2["variant_color"]);
        if ($c !== "") {
            $nonEmptyColors[] = $c;
        }
    }

    $nonEmptySizes = [];
    foreach ($obj3 as $row3) {
        $s = trim((string)$row3["variant_size"]);
        $sd = trim((string)$row3["size_detail"]);
        if ($s !== "") {
            $nonEmptySizes[] = $sd !== "" ? ($s . " (" . $sd . ")") : $s;
        }
    }

    $showColorSelector = count($obj2) > 1;
    $singleColorValue = count($obj2) === 1 ? (string)$obj2[0]["variant_color"] : "";
?>

<input type="hidden" id="product_id" value="<?= detailEsc($productHash); ?>">

<!-- Breadcrumb -->
<section class="detail-breadcrumb-bar">
    <div class="container">
        <nav class="detail-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <a href="?page=product">สินค้าทั้งหมด</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <a href="?page=product&amp;category=<?= (int)$product["category_id"]; ?>">
                <?= detailEsc($product["category_name"]); ?>
            </a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current"><?= detailEsc($product["product_name"]); ?></span>
        </nav>
    </div>
</section>

<!-- Main Product Detail -->
<section class="sec-product-detail detail-page-section">
    <div class="container">
        <div class="detail-main-card">
            <div class="row align-items-start">
                <!-- Gallery Column -->
                <div class="col-md-5 col-lg-6 mb-4 mb-md-0">
                    <div class="detail-gallery-wrap">
                        <?php if (count($obj4) > 0) { ?>
                        <div class="wrap-slick3">
                            <div class="slick3-main detail-slick-main">
                                <?php foreach ($obj4 as $row4) {
                                    $imgUrl = "file/product/" . $row4["img_name"];
                                ?>
                                <div class="detail-main-slide">
                                    <img src="<?= detailEsc($imgUrl); ?>"
                                        alt="<?= detailEsc($product["product_name"]); ?>"
                                        data-large="<?= detailEsc($imgUrl); ?>"
                                        onerror="this.onerror=null;this.src='images/icons/S__8732697.png';this.classList.add('is-fallback-img');">
                                </div>
                                <?php } ?>
                            </div>

                            <?php if (count($obj4) > 1) { ?>
                            <div class="slick3-nav detail-slick-nav">
                                <?php foreach ($obj4 as $row5) {
                                    $thumbUrl = "file/product/" . $row5["img_name"];
                                ?>
                                <div class="detail-nav-slide">
                                    <img src="<?= detailEsc($thumbUrl); ?>"
                                        alt="<?= detailEsc($product["product_name"]); ?>"
                                        onerror="this.onerror=null;this.src='images/icons/S__8732697.png';this.classList.add('is-fallback-img');">
                                </div>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } else { ?>
                        <div class="detail-main-slide detail-no-img">
                            <img src="images/icons/S__8732697.png" alt="<?= detailEsc($product["product_name"]); ?>" class="is-fallback-img">
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Product Info Column -->
                <div class="col-md-7 col-lg-6">
                    <div class="detail-info-panel">
                        <div class="detail-top-meta">
                            <a href="?page=product&amp;category=<?= (int)$product["category_id"]; ?>" class="oar-eyebrow mb-0">
                                <?= detailEsc($product["category_name"]); ?>
                            </a>
                            <?php if ($totalSold > 0) { ?>
                                <span class="detail-sold-pill">ขายแล้ว <?= number_format($totalSold); ?> ชิ้น</span>
                            <?php } ?>
                        </div>

                        <h1 class="detail-product-title js-name-detail product_name">
                            <?= detailEsc($product["product_name"]); ?>
                        </h1>

                        <div class="detail-price-box">
                            <span class="detail-price-value price-data"><?= detailEsc($product_price); ?></span>
                            <span class="detail-stock-pill d-none" id="detail-stock-badge"></span>
                        </div>

                        <div class="detail-options-area">
                            <!-- Color Selector: visible only when > 1 color; hidden input when exactly 1 color -->
                            <?php if ($showColorSelector) { ?>
                            <div class="detail-option-group">
                                <div class="detail-option-label">สี</div>
                                <div class="selectgroup selectgroup-pills">
                                    <?php foreach ($obj2 as $key => $value2) {
                                        $colorLabel = trim((string)$value2["variant_color"]) !== "" ? $value2["variant_color"] : "มาตรฐาน";
                                        $colorImg = !empty($value2["img_name"]) ? "file/product/" . $value2["img_name"] : "";
                                    ?>
                                    <label class="selectgroup-item">
                                        <input type="radio"
                                            name="variant_color"
                                            value="<?= detailEsc($value2["variant_color"]); ?>"
                                            class="selectgroup-input"
                                            color-img="<?= detailEsc($colorImg); ?>"
                                            <?= $key === 0 ? 'checked="checked"' : ''; ?> />
                                        <span class="selectgroup-button"><?= detailEsc($colorLabel); ?></span>
                                    </label>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } else { ?>
                            <input type="hidden" name="variant_color" value="<?= detailEsc($singleColorValue); ?>">
                            <?php } ?>

                            <!-- Size Selector: populated by view.js (visible only when > 1 size, hidden input when 1 size) -->
                            <div class="detail-option-group size-data d-none"></div>

                            <!-- Quantity Selector -->
                            <div class="detail-option-group detail-qty-group">
                                <div class="detail-option-label">จำนวน</div>
                                <div class="detail-qty-row">
                                    <div class="wrap-num-product detail-num-box flex-w">
                                        <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" id="qty-down">
                                            <i class="fs-16 zmdi zmdi-minus"></i>
                                        </div>

                                        <input class="mtext-104 cl3 txt-center num-product" type="number" name="cart_qty"
                                            id="cart_qty" value="1" min="1" readonly>

                                        <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" id="qty-up">
                                            <i class="fs-16 zmdi zmdi-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="detail-actions">
                            <button type="button" id="add-cart" class="detail-btn-primary js-addcart-detail">
                                <i class="fa fa-shopping-cart mr-2" aria-hidden="true"></i>
                                เพิ่มสินค้าลงตะกร้า
                            </button>

                            <button type="button" id="buy-product" class="detail-btn-secondary">
                                <i class="fa fa-bolt mr-2" aria-hidden="true"></i>
                                ซื้อสินค้าทันที
                            </button>
                        </div>

                        <!-- Service Mini-Badges -->
                        <div class="detail-service-strip">
                            <div class="detail-service-item">
                                <i class="fa fa-university" aria-hidden="true"></i>
                                <span>รับหน้าร้านฟรีที่สำนักวิทยบริการ</span>
                            </div>
                            <div class="detail-service-item">
                                <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                <span>ส่งฟรีภายในวิทยาเขต ม.อ.ปัตตานี</span>
                            </div>
                            <div class="detail-service-item">
                                <i class="fa fa-qrcode" aria-hidden="true"></i>
                                <span>ชำระสะดวกผ่าน Thai QR PromptPay</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs: Description / Additional Info / Reviews -->
        <div class="detail-tabs-card">
            <div class="tab01">
                <ul class="nav nav-tabs detail-nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#description" role="tab">รายละเอียดสินค้า</a>
                    </li>
                    <?php if (count($nonEmptyColors) > 0 || count($nonEmptySizes) > 0) { ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#information" role="tab">ข้อมูลเพิ่มเติม</a>
                    </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#reviews" role="tab">
                            รีวิวลูกค้า <span class="detail-review-count" id="review-count-badge"></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content detail-tab-content">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="detail-description-prose">
                            <?= $product["product_detail"]; ?>
                        </div>
                    </div>

                    <?php if (count($nonEmptyColors) > 0 || count($nonEmptySizes) > 0) { ?>
                    <div class="tab-pane fade" id="information" role="tabpanel">
                        <div class="detail-spec-list">
                            <?php if (count($nonEmptyColors) > 0) { ?>
                            <div class="detail-spec-row">
                                <div class="detail-spec-label">สีที่มีจำหน่าย</div>
                                <div class="detail-spec-val"><?= detailEsc(implode(", ", $nonEmptyColors)); ?></div>
                            </div>
                            <?php } ?>
                            <?php if (count($nonEmptySizes) > 0) { ?>
                            <div class="detail-spec-row">
                                <div class="detail-spec-label">ขนาดที่มีจำหน่าย</div>
                                <div class="detail-spec-val"><?= detailEsc(implode(", ", $nonEmptySizes)); ?></div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="detail-reviews-wrap">
                            <div class="review-data"></div>

                            <div class="text-center mt-3">
                                <button type="button" id="load-more" class="detail-btn-loadmore" style="display:none;">
                                    โหลดรีวิวเพิ่มเติม
                                </button>
                            </div>

                            <form class="detail-review-form">
                                <h5 class="detail-review-form-title">เขียนรีวิวสินค้านี้</h5>

                                <div class="detail-rating-row">
                                    <span class="detail-rating-label">คะแนนของคุณ</span>
                                    <span class="wrap-rating fs-20 cl11 pointer">
                                        <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                        <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                        <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                        <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                        <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                        <input class="dis-none" type="number" name="review_rating" value="0">
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <label class="detail-form-label" for="review_detail">ความคิดเห็นของคุณ</label>
                                    <textarea class="detail-textarea" id="review_detail" name="review_detail" rows="4"
                                        placeholder="แบ่งปันประสบการณ์การใช้งานสินค้านี้..."></textarea>
                                </div>

                                <button type="button" id="insert-review" class="detail-btn-primary detail-btn-review">
                                    ส่งรีวิว
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
