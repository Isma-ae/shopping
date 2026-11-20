<?php
    $product_id = trim((string)($_GET["product_id"] ?? ""));
    $productRow = null;

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
        }
    }
?>
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">จัดการรูปภาพสินค้า</h3>
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
                <a href="#">จัดการรูปภาพสินค้า</a>
            </li>
        </ul>
    </div>

    <?php if (!$productRow) { ?>
        <div class="card card-round">
            <div class="card-body text-center py-5">
                <div class="pi-empty-icon mb-3">
                    <i class="fas fa-images"></i>
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
        $mainImgName = $productRow["main_img"] ?? "";
    ?>

    <input type="hidden" id="product_id" value="<?= $safePid; ?>">

    <!-- Product Hero Summary Card -->
    <div class="card card-round pi-hero-card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img id="hero-main-img"
                        src="<?= $mainImgName !== '' ? '../file/product/' . urlencode($mainImgName) : ''; ?>"
                        alt="<?= $safeName; ?>"
                        class="pi-hero-thumb<?= $mainImgName === '' ? ' d-none' : ''; ?>">
                    <div id="hero-main-empty" class="pi-hero-thumb-empty<?= $mainImgName !== '' ? ' d-none' : ''; ?>">
                        <i class="fas fa-image"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="pi-code-chip">#<?= $safePid; ?></span>
                            <span class="pi-cat-chip"><?= $safeCat; ?></span>
                            <?php if ($isActive) { ?>
                                <span class="pi-state-chip is-active"><span class="dot"></span>วางจำหน่าย</span>
                            <?php } else { ?>
                                <span class="pi-state-chip is-pending"><span class="dot"></span>รอวางจำหน่าย</span>
                            <?php } ?>
                        </div>
                        <h4 class="fw-bold mb-0"><?= $safeName; ?></h4>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                    <a href="?page=products" class="btn btn-outline-secondary btn-round btn-sm px-3">
                        <i class="fas fa-arrow-left me-1"></i>กลับ
                    </a>
                    <a href="?page=product-stock&product_id=<?= $urlPid; ?>" class="btn btn-outline-primary btn-round btn-sm px-3">
                        <i class="fas fa-layer-group me-1"></i>สต็อกและตัวเลือก
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Gallery & Multi-Upload -->
    <div class="card card-round mb-4">
        <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="fas fa-images text-primary me-2"></i>คลังรูปภาพสินค้า
                        <span class="badge badge-primary ms-2" id="gallery-count-badge">0 รูป</span>
                    </h4>
                    <small class="text-muted">คลิกปุ่มดาวที่มุมภาพเพื่อตั้งเป็นรูปหลักของสินค้า (อัปเดตทันที)</small>
                </div>
                <button type="button" class="btn btn-primary btn-round btn-sm px-3" id="btnTriggerUpload">
                    <i class="fas fa-cloud-upload-alt me-1"></i>อัปโหลดรูปเพิ่ม
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Drag & Drop Upload Zone -->
            <div class="pi-dropzone mb-4" id="upload-dropzone">
                <input type="file" id="img_name" name="img_name[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="d-none">
                <div class="pi-dropzone-content" id="dropzone-idle">
                    <div class="pi-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="fw-semibold text-dark">ลากไฟล์รูปภาพมาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</div>
                    <small class="text-muted">เลือกได้หลายรูปพร้อมกัน · รองรับ JPG, PNG, WEBP, GIF</small>
                </div>
                <div class="pi-dropzone-uploading d-none" id="dropzone-loading">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                    <div class="fw-semibold text-primary">กำลังอัปโหลดรูปภาพ...</div>
                </div>
            </div>

            <!-- Image Grid -->
            <div class="pi-gallery-grid" id="gallery-grid"></div>
            <div class="text-center py-4 d-none" id="gallery-empty">
                <div class="pi-empty-icon mb-2"><i class="far fa-images"></i></div>
                <div class="fw-semibold text-muted">ยังไม่มีรูปภาพของสินค้านี้</div>
                <small class="text-muted">อัปโหลดรูปด้านบนเพื่อเริ่มจัดการคลังภาพและรูปประจำสี</small>
            </div>
        </div>
    </div>

    <!-- Section 2: Color Variant Image Mapping -->
    <div class="card card-round">
        <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="fas fa-palette text-success me-2"></i>ผูกรูปภาพตามสีของสินค้า
                    </h4>
                    <small class="text-muted">เมื่อลูกค้าเลือกสีในหน้าร้าน ระบบจะเปลี่ยนไปแสดงรูปที่ผูกไว้กับสีนั้นอัตโนมัติ</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productTable" class="display table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 110px;" class="text-center">รูปประจำสี</th>
                            <th>สี</th>
                            <th>ขนาดที่มีในสีนี้</th>
                            <th style="width: 150px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bind Image to Color Modal -->
    <div class="modal fade" id="variantModal" tabindex="-1" aria-labelledby="variantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="variantModalLabel">
                            <i class="fas fa-palette text-warning me-2"></i>เลือกรูปสินค้าประจำสี
                        </h5>
                        <small class="text-muted" id="modal-color-subtitle"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-variant">
                        <input type="hidden" name="product_id" value="<?= $safePid; ?>">
                        <input type="hidden" name="fn" value="edit_variant">
                        <input type="hidden" id="variant_color" name="variant_color">

                        <div class="mb-2 fw-semibold">เลือกรูปภาพจากคลังของสินค้านี้</div>
                        <div class="pi-picker-grid color-img" id="modal-color-img-grid"></div>
                        <div class="alert alert-light border small d-none mt-2 mb-0" id="modal-no-img-alert">
                            <i class="fas fa-info-circle text-primary me-1"></i>ยังไม่มีรูปในคลัง กรุณาอัปโหลดรูปภาพในส่วนคลังรูปภาพด้านบนก่อนครับ
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="edit-variant" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i>บันทึกรูปประจำสี
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Preview Modal -->
    <div class="modal fade" id="piLightboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold mb-0" id="pi-lightbox-title">รูปภาพสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <img id="pi-lightbox-src" src="" alt="" class="img-fluid rounded pi-lightbox-img">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <?php } ?>
</div>
