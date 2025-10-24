<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">เพิ่มสินค้า</h3>
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
                <a href="?page=products">สินค้า</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">เพิ่มสินค้า</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">เพิ่มสินค้า</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="#" id="btnSaveGroup">
                            <i class="fa fa-plus"></i>
                            บันทึกสินค้าที่เลือก
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shirtTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>รายละเอียด</th>
                                    <th>รูปแบบสินค้า</th>
                                    <th>สี</th>
                                    <th>ขนาด</th>
                                    <th>ราคา</th>
                                    <th>ราคาขาย</th>
                                    <th>สต๊อก</th>
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

<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold"> เพิ่ม</span>
                    <span class="fw-light"> สินค้า </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-product">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <img id="img" src="assets/img/image.png" alt="community image" class="img-thumbnail"
                                style="width: 300px;" onerror="errorImage(this,'assets/img/image.png')">
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="img_name">รูปสินค้าหลัก</label>
                                <input type="file" class="form-control" id="img_name" name="img_name" accept="img/*"
                                    onchange="imgf_change(this,'#img','assets/img/image.png')">
                            </div>
                        </div>
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
                <button type="button" id="add-product" class="btn btn-primary">
                    เพิ่มสินค้า
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>