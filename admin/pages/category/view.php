<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">ประเภท</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="#">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">ประเภทสินค้า / บริษัทขนส่ง</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">ประเภทสินค้า</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="?page=product-add" id="addCategory">
                            <i class="fa fa-plus"></i>
                            เพิ่มประเภทสินค้า
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="category-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10px;">#</th>
                                    <th>ประเภทสินค้า</th>
                                    <th style="width: 85px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">บริษัทขนส่ง</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="?page=product-add" id="addShipping">
                            <i class="fa fa-plus"></i>
                            เพิ่มบริษัทขนส่ง
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shipping-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10px;">#</th>
                                    <th>ชื่อบริษัทขนส่ง</th>
                                    <th style="width: 85px;">จัดการ</th>
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


<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold category-label"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-category">
                    <div class="row">
                        <input type="hidden" id="fn" name="fn" value="">
                        <input type="hidden" id="category_id" name="category_id">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="category_name">ชื่อประเภทสินค้า</label>
                                <input type="text" class="form-control" id="category_name" name="category_name"
                                    placeholder="ชื่อประเภทสินค้า...">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="add-category" class="btn btn-success">
                    เพิ่ม
                </button>
                <button type="button" id="edit-category" class="btn btn-warning">
                    แก้ไข
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shippingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold shipping-label"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-shipping">
                    <div class="row">
                        <input type="hidden" id="fn_shipping" name="fn" value="">
                        <input type="hidden" id="transported_id" name="transported_id">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="transported_name">ชื่อบริษัทขนส่ง</label>
                                <input type="text" class="form-control" id="transported_name" name="transported_name"
                                    placeholder="ชื่อบริษัทขนส่ง...">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="add-shipping" class="btn btn-success">
                    เพิ่ม
                </button>
                <button type="button" id="edit-shipping" class="btn btn-warning">
                    แก้ไข
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>