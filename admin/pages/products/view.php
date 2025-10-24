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
                <a href="#">สินค้า</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">สินค้า</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="?page=product-add" id="add">
                            <i class="fa fa-plus"></i>
                            เพิ่มสินค้า
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10px;">#</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>ประเภทสินค้า</th>
                                    <th>คงเหลือ</th>
                                    <th>สถานะ</th>
                                    <th style="width: 125px;">Action</th>
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
                                    <?php
                                        $category = $DB->QueryObj("SELECT * FROM category");
                                        foreach ($category as $categories) {
                                            echo '<option value="'.$categories["category_id"].'">'.$categories["category_name"].'</option>';
                                        }
                                    ?>
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
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="reserve">เปิดจองถ้าสินค้าหมด</label>
                                <div class="d-flex">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="reserve_id" id="reserve_id1"
                                            value="1" />
                                        <label class="form-check-label" for="reserve_id1">
                                            เปิดให้จอง
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="reserve_id" id="reserve_id2"
                                            value="2" checked />
                                        <label class="form-check-label" for="reserve_id2">
                                            ไม่เปิดให้จอง
                                        </label>
                                    </div>
                                </div>
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