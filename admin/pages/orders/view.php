<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">สินค้า</h3>
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
                <a href="#">รายการสั่งซื้อ</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">รายการสั่งซื้อ</h4>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line nav-color-secondary" id="line-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active choose-status" id="line-status1" status-id="1"
                                data-bs-toggle="pill" href="#">รอชำระเงิน <span class="text-danger count1"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link choose-status" id="line-status2" status-id="2" data-bs-toggle="pill"
                                href="#">รอจัดส่ง <span class="text-danger count2"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link choose-status" id="line-status3" status-id="3" data-bs-toggle="pill"
                                href="#">จัดส่งแล้ว <span class="text-danger count3"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link choose-status" id="line-status4" status-id="4" data-bs-toggle="pill"
                                href="#">ยกเลิก <span class="text-danger count4"></span></a>
                        </li>
                    </ul>
                    <div class="table-responsive mt-3">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10px;">#</th>
                                    <th>หมายเลขคำสั่งซื้อ</th>
                                    <th>ผู้ซื้อ</th>
                                    <th>ยอดสั่งซื้อ</th>
                                    <th>สถานะ</th>
                                    <th>การจัดส่ง</th>
                                    <th style="width: 40px;">Action</th>
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


<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold"> แก้ไข</span>
                    <span class="fw-light"> สินค้า </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-product">
                    <div class="row">
                        <input type="hidden" id="fn" name="fn" value="edit_product">
                        <input type="hidden" id="product_id" name="product_id">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="product_name">ชื่อสินค้า</label>
                                <input type="text" class="form-control" id="product_name" name="product_name"
                                    placeholder="ชื่อสินค้า...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="category_id">ประเภทสินค้า</label>
                                <select class="form-select form-control" id="category_id" name="category_id">
                                    <option value="0" disabled selected>-- กรุณาเลือกประเภทสินค้า --</option>
                                    <option value="1">เสื้อผ้า</option>
                                    <option value="2">ของใช้</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="password">รายละเอียดสินค้า</label>
                                <textarea id="product_detail"></textarea>
                                <input type="hidden" name="product_detail">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="edit-product" class="btn btn-warning">
                    แก้ไข
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>