<?php
    $categoryList = $DB->QueryObj("SELECT category_id, category_name FROM category ORDER BY category_id ASC");
?>
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">เพิ่มสินค้าใหม่</h3>
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
                <a href="?page=product-add">เพิ่มสินค้าใหม่</a>
            </li>
        </ul>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-cloud-download-alt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รายการรอจัดกลุ่ม (จากคลัง)</p>
                                <h4 class="card-title" id="stat-api-total">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-check-square"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รายการที่เลือกไว้</p>
                                <h4 class="card-title" id="stat-selected">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-cubes"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สต็อกรวมที่เลือก</p>
                                <h4 class="card-title" id="stat-selected-stock">0 ชิ้น</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Error Alert (Hidden by default) -->
    <div id="api-error-banner" class="alert alert-danger d-none align-items-center justify-content-between gap-2 mb-4" role="alert">
        <div>
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="api-error-text">ไม่สามารถดึงข้อมูลจากระบบคลังสินค้าได้</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRetryApi">
            <i class="fas fa-sync-alt me-1"></i>ลองใหม่
        </button>
    </div>

    <!-- Main Selection Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="fas fa-layer-group text-primary me-2"></i>เลือกรายการสินค้าเพื่อจัดกลุ่ม
                            </h4>
                            <p class="text-muted small mb-0">
                                ติ๊กเลือกรายการสี/ไซส์ที่ต้องการรวมเป็นสินค้า 1 ชิ้น แล้วกดปุ่ม "จัดกลุ่มและบันทึกสินค้า"
                            </p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                            <div class="pa-filter-wrap">
                                <span class="pa-filter-label">สี:</span>
                                <select id="filter-color" class="form-select form-select-sm pa-filter-select">
                                    <option value="">ทุกสี</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-round btn-sm" id="btnRefreshApi" title="ดึงข้อมูลล่าสุดจากคลัง">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-round btn-sm d-none" id="btnClearSelection">
                                <i class="fas fa-times me-1"></i>ล้างที่เลือก
                            </button>
                            <a href="?page=products" class="btn btn-outline-secondary btn-round btn-sm px-3">
                                <i class="fas fa-arrow-left me-1"></i>กลับ
                            </a>
                            <button type="button" class="btn btn-primary btn-round btn-sm px-3" id="btnSaveGroup">
                                <i class="fa fa-plus me-1"></i>จัดกลุ่มและบันทึกสินค้า
                                <span class="pa-sel-badge ms-1 d-none" id="btn-sel-count">0</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shirtTable" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 44px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="check-all-page" title="เลือกทั้งหมดในหน้านี้">
                                    </th>
                                    <th>สินค้า</th>
                                    <th style="width: 130px;">รูปแบบ</th>
                                    <th style="width: 110px;">สี</th>
                                    <th style="width: 135px;">ขนาด</th>
                                    <th style="width: 100px;" class="text-end">ราคาทุน</th>
                                    <th style="width: 105px;" class="text-end">ราคาขาย</th>
                                    <th style="width: 100px;" class="text-center">สต็อก</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Grouped Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="productModalLabel">
                        <i class="fas fa-box-open text-primary me-2"></i>สร้างกลุ่มสินค้าใหม่
                    </h5>
                    <small class="text-muted" id="modal-selected-summary">เลือกไว้ 0 ตัวเลือก</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Selected Variants Preview Strip -->
                <div class="pa-selected-box mb-3">
                    <div class="pa-selected-box-head">
                        <span class="fw-semibold small text-dark">
                            <i class="fas fa-check-circle text-success me-1"></i>ตัวเลือกที่จะนำเข้า
                        </span>
                        <span class="small text-muted" id="modal-selected-stock-note"></span>
                    </div>
                    <div class="pa-selected-chips" id="modal-selected-chips"></div>
                </div>

                <form id="form-product">
                    <div class="row g-3">
                        <!-- Main Image Upload -->
                        <div class="col-12">
                            <label for="img_name" class="form-label fw-semibold">รูปสินค้าหลัก (ไม่บังคับ สามารถเพิ่มภายหลังได้)</label>
                            <div class="pa-upload-row">
                                <div class="pa-preview-box">
                                    <img id="img-preview" src="assets/img/image.png" alt="ตัวอย่างรูปสินค้า" class="pa-preview-thumb d-none">
                                    <div id="img-placeholder" class="pa-preview-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>ยังไม่ได้เลือกรูป</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control" id="img_name" name="img_name" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <small class="text-muted">รองรับ JPG, PNG, WEBP, GIF (แนะนำสัดส่วนจัตุรัส 1:1)</small>
                                        <button type="button" class="btn btn-link btn-sm text-danger p-0 d-none" id="btnRemoveImg">
                                            <i class="fas fa-trash-alt me-1"></i>ลบรูปที่เลือก
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="product_name" class="form-label fw-semibold">
                                ชื่อสินค้า <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="product_name" name="product_name"
                                placeholder="กรอกชื่อสินค้าที่ต้องการแสดงหน้าร้าน..." maxlength="200" required>
                        </div>

                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">
                                ประเภทสินค้า <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="" disabled selected>-- เลือกประเภทสินค้า --</option>
                                <?php foreach ($categoryList as $cat) { ?>
                                    <option value="<?= (int)$cat['category_id']; ?>">
                                        <?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="product_detail" class="form-label fw-semibold">รายละเอียดสินค้า</label>
                            <textarea id="product_detail"></textarea>
                            <input type="hidden" name="product_detail">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-block mb-2">การเปิดจองเมื่อสินค้าหมด</label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="pa-reserve-option" for="reserve_id1">
                                        <input class="form-check-input" type="radio" name="reserve_id" id="reserve_id1" value="1">
                                        <div>
                                            <div class="fw-semibold">เปิดให้จอง (Pre-order)</div>
                                            <small class="text-muted">ลูกค้าสามารถสั่งจองได้แม้สต็อกเป็น 0</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="pa-reserve-option" for="reserve_id2">
                                        <input class="form-check-input" type="radio" name="reserve_id" id="reserve_id2" value="2" checked>
                                        <div>
                                            <div class="fw-semibold">ไม่เปิดให้จอง</div>
                                            <small class="text-muted">จำหน่ายเฉพาะจำนวนสินค้าที่มีในสต็อก</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="add-product" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i>บันทึกสินค้าใหม่
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Preview Modal -->
<div class="modal fade" id="apiImgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold mb-0" id="api-img-title">รูปสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <img id="api-img-src" src="" alt="" class="img-fluid rounded pa-lightbox-img">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
