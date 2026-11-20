<?php
    $product_id = trim((string)($_GET["product_id"] ?? ""));
    $productRow = null;
    $productImages = [];

    if ($product_id !== "") {
        $pidEsc = $DB->Escape($product_id);
        $rows = $DB->QueryObj("
            SELECT
                p.product_id,
                p.product_name,
                p.product_status,
                p.reserve_id,
                COALESCE(c.category_name, 'ไม่ระบุประเภท') AS category_name,
                (
                    SELECT COALESCE(MAX(CASE WHEN img_main = '1' THEN img_name END), MIN(img_name))
                    FROM img
                    WHERE product_id = p.product_id
                ) AS main_img
            FROM product p
            LEFT JOIN category c ON p.category_id = c.category_id
            WHERE p.product_id = '{$pidEsc}'
            LIMIT 1
        ");
        if (!empty($rows)) {
            $productRow = $rows[0];
            $productImages = $DB->QueryObj("
                SELECT img_id, img_name, img_main
                FROM img
                WHERE product_id = '{$pidEsc}'
                ORDER BY img_main DESC, img_id ASC
            ");
        }
    }
?>
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">สต็อกและตัวเลือกสินค้า</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="./">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="?page=products">จัดการสินค้า</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">สต็อกและตัวเลือกสินค้า</a>
            </li>
        </ul>
    </div>

    <?php if (!$productRow) { ?>
        <div class="card card-round">
            <div class="card-body text-center py-5">
                <div class="ps-empty-icon mb-3">
                    <i class="fas fa-box-open"></i>
                </div>
                <h4 class="fw-bold mb-2">ไม่พบข้อมูลสินค้า</h4>
                <p class="text-muted mb-4">ไม่พบรหัสสินค้าที่ระบุ หรือสินค้านี้อาจถูกลบออกจากระบบแล้ว</p>
                <a href="?page=products" class="btn btn-primary btn-round">
                    <i class="fas fa-arrow-left me-1"></i>กลับไปหน้าจัดการสินค้า
                </a>
            </div>
        </div>
    <?php } else {
        $safePid     = htmlspecialchars($productRow["product_id"], ENT_QUOTES, "UTF-8");
        $urlPid      = urlencode($productRow["product_id"]);
        $safeName    = htmlspecialchars($productRow["product_name"], ENT_QUOTES, "UTF-8");
        $safeCat     = htmlspecialchars($productRow["category_name"], ENT_QUOTES, "UTF-8");
        $isActive    = ((int)$productRow["product_status"] === 2);
        $isPreorder  = ((int)$productRow["reserve_id"] === 1);
        $mainImgName = $productRow["main_img"] ?? "";
    ?>

    <!-- Product Hero Summary Card -->
    <div class="card card-round ps-hero-card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <?php if ($mainImgName !== "") { ?>
                        <img src="../file/product/<?= urlencode($mainImgName); ?>" alt="<?= $safeName; ?>" class="ps-hero-thumb">
                    <?php } else { ?>
                        <div class="ps-hero-thumb-empty"><i class="fas fa-image"></i></div>
                    <?php } ?>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="ps-code-chip">#<?= $safePid; ?></span>
                            <span class="ps-cat-chip"><?= $safeCat; ?></span>
                            <?php if ($isActive) { ?>
                                <span class="ps-state-chip is-active"><span class="dot"></span>วางจำหน่าย</span>
                            <?php } else { ?>
                                <span class="ps-state-chip is-pending"><span class="dot"></span>รอวางจำหน่าย</span>
                            <?php } ?>
                            <?php if ($isPreorder) { ?>
                                <span class="ps-preorder-chip"><i class="fas fa-bookmark me-1"></i>เปิดจองเมื่อหมด</span>
                            <?php } ?>
                        </div>
                        <h4 class="fw-bold mb-0"><?= $safeName; ?></h4>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                    <a href="?page=products" class="btn btn-outline-secondary btn-round btn-sm px-3">
                        <i class="fas fa-arrow-left me-1"></i>กลับ
                    </a>
                    <a href="?page=product-image&product_id=<?= $urlPid; ?>" class="btn btn-outline-success btn-round btn-sm px-3">
                        <i class="fas fa-images me-1"></i>จัดการรูปภาพ (<?= count($productImages); ?>)
                    </a>
                    <a href="?page=variant-add&product_id=<?= $urlPid; ?>" class="btn btn-primary btn-round btn-sm px-3">
                        <i class="fa fa-plus me-1"></i>เพิ่มตัวเลือกสินค้า
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round ps-stat-card is-active mb-0" data-filter="all">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ตัวเลือกทั้งหมด</p>
                                <h4 class="card-title" id="stat-variants">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round ps-stat-card mb-0" data-filter="instock">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">คงเหลือรวม</p>
                                <h4 class="card-title" id="stat-remaining">0</h4>
                                <small class="text-muted ps-kpi-sub" id="stat-stock-breakdown">ตั้งต้น 0 · ขายแล้ว 0</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round ps-stat-card mb-0" data-filter="lowstock">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ใกล้หมด (1–5 ชิ้น)</p>
                                <h4 class="card-title" id="stat-lowstock">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round ps-stat-card mb-0" data-filter="outstock">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สต็อกหมด</p>
                                <h4 class="card-title" id="stat-outstock">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Stock Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-cubes text-primary me-2"></i>รายการตัวเลือกสินค้าและสต็อก
                        </h4>
                        <div class="ps-color-filter-wrap ms-auto">
                            <span class="ps-filter-label">สี:</span>
                            <select id="filter-color" class="form-select form-select-sm ps-filter-select">
                                <option value="">ทุกสี</option>
                            </select>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-secondary ps-status-tabs mt-3" id="ps-status-tabs">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-filter="all">
                                ทั้งหมด <span class="ps-tab-badge" id="tab-cnt-all">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="instock">
                                พร้อมขาย <span class="ps-tab-badge" id="tab-cnt-instock">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="lowstock">
                                ใกล้หมด <span class="ps-tab-badge" id="tab-cnt-lowstock">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="outstock">
                                หมดสต็อก <span class="ps-tab-badge" id="tab-cnt-outstock">0</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productTable" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 44px;" class="text-center">#</th>
                                    <th>ตัวเลือกสินค้า</th>
                                    <th style="width: 120px;">สี</th>
                                    <th style="width: 150px;">ขนาด</th>
                                    <th style="width: 110px;" class="text-end">ราคาทุน</th>
                                    <th style="width: 125px;" class="text-end">ราคาขาย</th>
                                    <th style="width: 145px;" class="text-center">สต็อกคงเหลือ</th>
                                    <th style="width: 105px;" class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Variant Modal -->
    <div class="modal fade" id="variantModal" tabindex="-1" aria-labelledby="variantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="variantModalLabel">
                            <i class="fas fa-edit text-warning me-2"></i>แก้ไขตัวเลือกสินค้า
                        </h5>
                        <small class="text-muted" id="modal-variant-meta"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-variant">
                        <input type="hidden" id="product_id" name="product_id" value="<?= $safePid; ?>">
                        <input type="hidden" id="fn" name="fn" value="edit_variant">
                        <input type="hidden" id="variant_id" name="variant_id">
                        <input type="hidden" id="modal_total_ordered" value="0">

                        <!-- Image Picker -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <label class="form-label fw-semibold mb-0">รูปสินค้าประจำตัวเลือก / สี</label>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="sync_color_img" name="sync_color_img" value="1" checked>
                                    <label class="form-check-label small text-muted" for="sync_color_img">
                                        ใช้รูปนี้กับทุกไซส์ที่เป็นสีเดียวกัน
                                    </label>
                                </div>
                            </div>

                            <?php if (empty($productImages)) { ?>
                                <div class="alert alert-light border small mb-0 d-flex align-items-center justify-content-between">
                                    <span><i class="fas fa-info-circle text-primary me-1"></i>ยังไม่มีรูปภาพของสินค้านี้</span>
                                    <a href="?page=product-image&product_id=<?= $urlPid; ?>" class="btn btn-outline-success btn-xs">
                                        ไปอัปโหลดรูปภาพ
                                    </a>
                                </div>
                                <input type="hidden" name="img_id" value="0">
                            <?php } else { ?>
                                <div class="ps-img-grid">
                                    <label class="ps-img-option">
                                        <input type="radio" name="img_id" value="0">
                                        <div class="ps-img-card ps-img-card--none">
                                            <i class="fas fa-ban"></i>
                                            <span>ไม่ระบุรูป</span>
                                        </div>
                                    </label>
                                    <?php foreach ($productImages as $img) { ?>
                                        <label class="ps-img-option">
                                            <input type="radio" name="img_id" value="<?= (int)$img['img_id']; ?>">
                                            <div class="ps-img-card">
                                                <img src="../file/product/<?= urlencode($img['img_name']); ?>" alt="" loading="lazy">
                                                <span class="ps-img-check"><i class="fas fa-check"></i></span>
                                            </div>
                                        </label>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="variant_style" class="form-label fw-semibold">รูปแบบสินค้า</label>
                                <input type="text" class="form-control" id="variant_style" name="variant_style"
                                    placeholder="เช่น คอกลม, ทรงมาตรฐาน...">
                            </div>
                            <div class="col-md-6">
                                <label for="variant_color" class="form-label fw-semibold">สี</label>
                                <input type="text" class="form-control" id="variant_color" name="variant_color"
                                    placeholder="เช่น ขาว, ดำ, กรมท่า...">
                            </div>
                            <div class="col-md-5">
                                <label for="variant_size" class="form-label fw-semibold">ขนาด</label>
                                <input type="text" class="form-control" id="variant_size" name="variant_size"
                                    placeholder="เช่น S, M, L, XL, 550 ML...">
                            </div>
                            <div class="col-md-7">
                                <label for="size_detail" class="form-label fw-semibold">รายละเอียดขนาด (ถ้ามี)</label>
                                <input type="text" class="form-control" id="size_detail" name="size_detail"
                                    placeholder="เช่น อก 38 นิ้ว ยาว 27 นิ้ว">
                            </div>
                            <div class="col-md-4">
                                <label for="variant_price" class="form-label fw-semibold">ราคาต้นทุน (บาท)</label>
                                <input type="number" class="form-control" id="variant_price" name="variant_price"
                                    min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="variant_sale" class="form-label fw-semibold">ราคาขาย (บาท)</label>
                                <input type="number" class="form-control" id="variant_sale" name="variant_sale"
                                    min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="variant_stock" class="form-label fw-semibold">สต็อกตั้งต้นทั้งหมด (ชิ้น)</label>
                                <input type="number" class="form-control" id="variant_stock" name="variant_stock"
                                    min="0" step="1" placeholder="0" required>
                            </div>
                        </div>

                        <!-- Live Stock Calculator -->
                        <div class="ps-stock-calc mt-3" id="ps-stock-calc">
                            <div class="ps-stock-calc-item">
                                <span class="label">สต็อกตั้งต้น</span>
                                <strong id="calc-total">0</strong>
                            </div>
                            <div class="ps-stock-calc-op">&minus;</div>
                            <div class="ps-stock-calc-item">
                                <span class="label">ขายไปแล้ว (ชำระแล้ว)</span>
                                <strong id="calc-ordered">0</strong>
                            </div>
                            <div class="ps-stock-calc-op">=</div>
                            <div class="ps-stock-calc-item is-result">
                                <span class="label">คงเหลือพร้อมขาย</span>
                                <strong id="calc-remaining">0</strong>
                            </div>
                        </div>
                        <div class="small text-danger mt-1 d-none" id="calc-warning">
                            <i class="fas fa-exclamation-circle me-1"></i>สต็อกตั้งต้นน้อยกว่ายอดที่ขายไปแล้ว จะทำให้ยอดคงเหลือติดลบ
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="edit-variant" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i>บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="variantImgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold mb-0" id="vimg-preview-title">รูปภาพตัวเลือกสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="vimg-preview-src" src="" alt="" class="img-fluid rounded ps-preview-img">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <?php } ?>
</div>
