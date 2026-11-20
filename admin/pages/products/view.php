<?php
    $categoryList = $DB->QueryObj("SELECT category_id, category_name FROM category ORDER BY category_id ASC");
?>
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">จัดการสินค้า</h3>
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
        </ul>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round prd-stat-card is-active mb-0" data-filter="all">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สินค้าทั้งหมด</p>
                                <h4 class="card-title" id="stat-total">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round prd-stat-card mb-0" data-filter="active">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-store"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">วางจำหน่าย</p>
                                <h4 class="card-title" id="stat-active">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round prd-stat-card mb-0" data-filter="pending">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รอวางจำหน่าย</p>
                                <h4 class="card-title" id="stat-pending">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stats card-round prd-stat-card mb-0" data-filter="lowstock">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สต็อกใกล้หมด / หมด</p>
                                <h4 class="card-title" id="stat-lowstock">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Products Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="fas fa-object-group text-primary me-2"></i>รายการสินค้าทั้งหมด
                            </h4>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                            <div class="prd-cat-filter-wrap">
                                <span class="prd-filter-label">ประเภท:</span>
                                <select id="filter-category" class="form-select form-select-sm prd-filter-select">
                                    <option value="">ทุกประเภท</option>
                                    <?php foreach ($categoryList as $cat) { ?>
                                        <option value="<?= (int)$cat['category_id']; ?>">
                                            <?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <a class="btn btn-primary btn-round btn-sm px-3" href="?page=product-add" id="add">
                                <i class="fa fa-plus me-1"></i>เพิ่มสินค้า
                            </a>
                        </div>
                    </div>

                    <!-- Status Filter Pills -->
                    <ul class="nav nav-pills nav-secondary prd-status-tabs mt-3" id="prd-status-tabs">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-filter="all">
                                ทั้งหมด <span class="prd-tab-badge" id="tab-cnt-all">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="active">
                                วางจำหน่าย <span class="prd-tab-badge" id="tab-cnt-active">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="pending">
                                รอวางจำหน่าย <span class="prd-tab-badge" id="tab-cnt-pending">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-filter="lowstock">
                                สต็อกใกล้หมด / หมด <span class="prd-tab-badge" id="tab-cnt-lowstock">0</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 48px;" class="text-center">#</th>
                                    <th>สินค้า</th>
                                    <th style="width: 140px;">ประเภท</th>
                                    <th style="width: 130px;" class="text-end">ราคาขาย</th>
                                    <th style="width: 145px;" class="text-center">คงเหลือ</th>
                                    <th style="width: 150px;" class="text-center">สถานะ</th>
                                    <th style="width: 175px;" class="text-center">จัดการ</th>
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

<!-- Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="productModalLabel">
                        <i class="fas fa-edit text-warning me-2"></i>แก้ไขข้อมูลสินค้า
                    </h5>
                    <small class="text-muted" id="modal-product-code"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-product">
                    <input type="hidden" id="fn" name="fn" value="edit_product">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="product_name" class="form-label fw-semibold">
                                ชื่อสินค้า <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="product_name" name="product_name"
                                placeholder="กรอกชื่อสินค้า..." maxlength="200" required>
                        </div>
                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">
                                ประเภทสินค้า <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="0" disabled selected>-- เลือกประเภทสินค้า --</option>
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
                                    <label class="prd-reserve-option" for="reserve_id1">
                                        <input class="form-check-input" type="radio" name="reserve_id" id="reserve_id1" value="1">
                                        <div>
                                            <div class="fw-semibold">เปิดให้จอง (Pre-order)</div>
                                            <small class="text-muted">ลูกค้าสามารถสั่งจองได้แม้สต็อกเป็น 0</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="prd-reserve-option" for="reserve_id2">
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
                <button type="button" id="edit-product" class="btn btn-warning px-4">
                    <i class="fas fa-save me-1"></i>บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Product Image Preview Modal -->
<div class="modal fade" id="productImgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold mb-0" id="img-preview-title">รูปภาพสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <img id="img-preview-src" src="" alt="" class="img-fluid rounded prd-preview-img">
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <a href="#" id="img-preview-manage" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-images me-1"></i>จัดการรูปภาพสินค้านี้
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
