<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">สินค้า</h3>
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
                <a href="#">รูปสินค้า</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
                $sql = "SELECT * FROM product WHERE product_id = '".$_GET["product_id"]."'";
                $obj = $DB->QueryObj($sql);
            ?>
            <input type="hidden" id="product_id" value="<?=$obj[0]["product_id"];?>">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"><?=$obj[0]["product_name"];?></h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="img_name">เพิ่มรูป</label>
                        <input type="file" class="form-control" id="img_name" name="img_name[]" multiple>
                    </div>
                    <div>
                        <label class="form-label">รูปสินค้าหลัก</label>
                        <div class="row image-data">
                        </div>
                        <div id="main-button">
                            <button type="button" class="btn btn-success" id="save-image">บันทึก</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">รูปสินค้าในแต่ละสี</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>รูป</th>
                                    <th>สี</th>
                                    <th style="width:90px;">เพิ่มรูป</th>
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

<div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fw-mediumbold"> รูปในแต่ละสี</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-variant">
                    <div class="row">
                        <input type="hidden" id="product_id" name="product_id" value="<?=$obj[0]["product_id"];?>">
                        <input type="hidden" id="fn" name="fn" value="edit_variant">
                        <input type="hidden" id="variant_id" name="variant_id">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="color_img">รูปสินค้าในแต่ละสี</label>
                                <div class="row color-img">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_color">สี</label>
                                <input type="text" class="form-control" id="variant_color" name="variant_color"
                                    placeholder="สี..." readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="edit-variant" class="btn btn-warning">
                    แก้ไขรูป
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>