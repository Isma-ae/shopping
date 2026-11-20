<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">ประเภทสินค้าและบริษัทขนส่ง</h3>
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
                <a href="?page=category">ประเภทสินค้า / บริษัทขนส่ง</a>
            </li>
        </ul>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-6 col-lg-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-th-large"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ประเภทสินค้า</p>
                                <h4 class="card-title" id="statCategoryCount">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-lg-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">บริษัทขนส่ง</p>
                                <h4 class="card-title" id="statShippingCount">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Tables Side by Side -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-th-large text-primary me-2"></i>ประเภทสินค้า
                        </h4>
                        <button type="button" class="btn btn-primary btn-sm btn-round" id="addCategory">
                            <i class="fa fa-plus me-1"></i>เพิ่มประเภทสินค้า
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="category-table" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>ชื่อประเภทสินค้า</th>
                                    <th style="width: 105px;" class="text-center">จำนวนสินค้า</th>
                                    <th style="width: 96px;" class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-truck text-info me-2"></i>บริษัทขนส่ง
                        </h4>
                        <button type="button" class="btn btn-primary btn-sm btn-round" id="addShipping">
                            <i class="fa fa-plus me-1"></i>เพิ่มบริษัทขนส่ง
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shipping-table" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>ชื่อบริษัทขนส่ง</th>
                                    <th style="width: 110px;" class="text-center">คำสั่งซื้อที่ใช้</th>
                                    <th style="width: 96px;" class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold category-label">เพิ่มประเภทสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-category">
                    <input type="hidden" id="fn" name="fn" value="">
                    <input type="hidden" id="category_id" name="category_id" value="">
                    <div class="form-group px-0 mb-0">
                        <label for="category_name" class="fw-semibold mb-1">
                            ชื่อประเภทสินค้า <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="category_name" name="category_name"
                            placeholder="เช่น เสื้อผ้า, กระบอกน้ำ, ของที่ระลึก" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="add-category" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>เพิ่ม
                </button>
                <button type="button" id="edit-category" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Shipping Carrier Modal -->
<div class="modal fade" id="shippingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold shipping-label">เพิ่มบริษัทขนส่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-shipping">
                    <input type="hidden" id="fn_shipping" name="fn" value="">
                    <input type="hidden" id="transported_id" name="transported_id" value="">
                    <div class="form-group px-0 mb-0">
                        <label for="transported_name" class="fw-semibold mb-1">
                            ชื่อบริษัทขนส่ง <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="transported_name" name="transported_name"
                            placeholder="เช่น ไปรษณีย์ไทย (EMS), Flash Express, J&T" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="add-shipping" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>เพิ่ม
                </button>
                <button type="button" id="edit-shipping" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</div>
