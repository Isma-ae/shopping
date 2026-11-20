<?php
    if (!function_exists("catalogFetchRows")) {
        function catalogFetchRows($DB, $sql) {
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

    if (!function_exists("catalogEsc")) {
        function catalogEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    if (!function_exists("catalogFormatPriceRange")) {
        function catalogFormatPriceRange($minPrice, $maxPrice) {
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

    $searchRaw = trim((string)($_GET["search"] ?? ""));
    $searchSql = "";
    if ($searchRaw !== "") {
        $searchEsc = $DB->Escape($searchRaw);
        $searchSql = " AND p.product_name LIKE '%{$searchEsc}%'";
    }

    $sortKey = (string)($_GET["sort"] ?? "latest");
    $sortOrderMap = [
        "latest" => "p.product_id DESC",
        "bestseller" => "total_sold DESC, p.product_id DESC",
        "price_asc" => "(min_price <= 0) ASC, min_price ASC, p.product_id DESC",
        "price_desc" => "max_price DESC, p.product_id DESC"
    ];
    if (!isset($sortOrderMap[$sortKey])) {
        $sortKey = "latest";
    }
    $orderBySql = $sortOrderMap[$sortKey];

    $initialCategory = (int)($_GET["category"] ?? 0);

    $categories = catalogFetchRows($DB, "
        SELECT
            c.category_id,
            c.category_name,
            COUNT(DISTINCT p.product_id) AS product_count
        FROM category c
        INNER JOIN product p
            ON c.category_id = p.category_id
            AND p.product_status = 2 {$searchSql}
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_id ASC
    ");

    $products = catalogFetchRows($DB, "
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
        WHERE p.product_status = 2 {$searchSql}
        ORDER BY {$orderBySql}
    ");

    $totalProducts = count($products);
?>

<!-- Catalog Header Band -->
<section class="product-page-hero">
    <div class="container">
        <div class="product-hero-inner">
            <div>
                <span class="oar-eyebrow">OAR OFFICIAL CATALOG</span>
                <h1 class="oar-heading">สินค้าทั้งหมด</h1>
                <p class="product-hero-sub mb-0">
                    เลือกชมสินค้าและของที่ระลึกสำนักวิทยบริการ มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี
                </p>
            </div>
            <div class="product-result-meta">
                แสดง <strong id="productVisibleCount"><?= $totalProducts; ?></strong> รายการ
            </div>
        </div>
    </div>
</section>

<!-- Catalog Toolbar & Grid -->
<section class="product-catalog-section" data-initial-category="<?= $initialCategory; ?>">
    <div class="container">
        <!-- Toolbar: Search + Sort -->
        <div class="product-toolbar">
            <form method="get" action="" id="productFilterForm" class="product-toolbar-form">
                <input type="hidden" name="page" value="product">
                <?php if ($initialCategory > 0) { ?>
                    <input type="hidden" name="category" value="<?= $initialCategory; ?>">
                <?php } ?>

                <div class="product-search-box">
                    <i class="fa fa-search product-search-icon" aria-hidden="true"></i>
                    <input type="text"
                        name="search"
                        id="catalogSearchInput"
                        value="<?= catalogEsc($searchRaw); ?>"
                        placeholder="ค้นหาชื่อสินค้า เช่น เสื้อ, กระบอกน้ำ, กระเป๋า..."
                        autocomplete="off">
                    <?php if ($searchRaw !== "") { ?>
                        <a href="?page=product<?= $sortKey !== 'latest' ? '&amp;sort=' . urlencode($sortKey) : ''; ?>"
                            class="product-search-clear" title="ล้างคำค้นหา">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </a>
                    <?php } ?>
                    <button type="submit" class="product-search-btn">ค้นหา</button>
                </div>

                <div class="product-sort-wrap">
                    <label for="productSortSelect" class="product-sort-label mb-0">เรียงตาม:</label>
                    <select name="sort" id="productSortSelect" class="product-sort-select">
                        <option value="latest" <?= $sortKey === "latest" ? "selected" : ""; ?>>สินค้ามาใหม่</option>
                        <option value="bestseller" <?= $sortKey === "bestseller" ? "selected" : ""; ?>>ขายดีที่สุด</option>
                        <option value="price_asc" <?= $sortKey === "price_asc" ? "selected" : ""; ?>>ราคา: ต่ำ → สูง</option>
                        <option value="price_desc" <?= $sortKey === "price_desc" ? "selected" : ""; ?>>ราคา: สูง → ต่ำ</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($searchRaw !== "") { ?>
            <div class="product-active-search">
                <span>ผลการค้นหาสำหรับ: <strong>"<?= catalogEsc($searchRaw); ?>"</strong></span>
                <a href="?page=product" class="product-clear-link">ล้างคำค้นหา</a>
            </div>
        <?php } ?>

        <!-- Category Filter Pills -->
        <?php if (count($categories) > 0) { ?>
            <div class="oar-filter-bar filter-tope-group" id="productCategoryFilters">
                <button type="button" class="oar-filter-pill how-active1" data-filter="*" data-count="<?= $totalProducts; ?>">
                    สินค้าทั้งหมด (<?= $totalProducts; ?>)
                </button>
                <?php foreach ($categories as $cat) { ?>
                    <button type="button"
                        class="oar-filter-pill"
                        data-filter=".cat-<?= (int)$cat["category_id"]; ?>"
                        data-cat-id="<?= (int)$cat["category_id"]; ?>"
                        data-count="<?= (int)$cat["product_count"]; ?>">
                        <?= catalogEsc($cat["category_name"]); ?> (<?= (int)$cat["product_count"]; ?>)
                    </button>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- Product Grid -->
        <?php if ($totalProducts > 0) { ?>
            <div class="row isotope-grid product-catalog-grid">
                <?php
                    foreach ($products as $row) {
                        $totalSold = (int)$row["total_sold"];
                        $catId = (int)$row["category_id"];
                        $priceText = catalogFormatPriceRange($row["min_price"], $row["max_price"]);
                        $imgPath = !empty($row["img_name"]) ? "file/product/" . $row["img_name"] : "images/icons/OARShopping.png";
                        $detailUrl = "?page=detail&product=" . urlencode($row["product_hash"]);
                ?>
                <div class="col-6 col-md-4 col-lg-3 p-b-30 isotope-item cat-<?= $catId; ?>">
                    <article class="oar-product-card">
                        <a href="<?= catalogEsc($detailUrl); ?>" class="oar-product-thumb hov-img0">
                            <img src="<?= catalogEsc($imgPath); ?>"
                                alt="<?= catalogEsc($row["product_name"]); ?>"
                                onerror="this.onerror=null;this.src='images/icons/OARShopping.png';this.classList.add('is-fallback-img');">

                            <span class="oar-quick-cta">ดูรายละเอียดสินค้า</span>
                        </a>

                        <div class="oar-product-body">
                            <h3 class="oar-product-title">
                                <a href="<?= catalogEsc($detailUrl); ?>" class="js-name-b2">
                                    <?= catalogEsc($row["product_name"]); ?>
                                </a>
                            </h3>

                            <div class="oar-product-footer">
                                <span class="oar-product-price"><?= catalogEsc($priceText); ?></span>
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
            <div class="product-empty-state">
                <div class="product-empty-icon">
                    <i class="fa fa-search" aria-hidden="true"></i>
                </div>
                <h3 class="product-empty-title">ไม่พบสินค้าที่ตรงกับเงื่อนไข</h3>
                <p class="product-empty-desc">
                    <?php if ($searchRaw !== "") { ?>
                        ไม่พบสินค้าที่มีคำว่า "<?= catalogEsc($searchRaw); ?>" ลองค้นหาด้วยคำอื่นหรือดูสินค้าทั้งหมด
                    <?php } else { ?>
                        ยังไม่มีสินค้าที่เปิดจำหน่ายในขณะนี้
                    <?php } ?>
                </p>
                <a href="?page=product" class="product-empty-btn">ดูสินค้าทั้งหมด</a>
            </div>
        <?php } ?>
    </div>
</section>
