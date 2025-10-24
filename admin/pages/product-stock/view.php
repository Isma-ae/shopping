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
                <a href="#">สต๊อกสินค้า</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <?php
                            $sql = "SELECT
                                        product_id,
                                        product_name
                                    FROM product
                                    WHERE product_id = '".$_GET["product_id"]."'";
                            $obj = $DB->QueryObj($sql);
                        ?>
                        <h4 class="card-title"><?=$obj[0]["product_name"];?></h4>
                        <a class="btn btn-primary btn-round ms-auto"
                            href="?page=variant-add&product_id=<?=$obj[0]["product_id"];?>">
                            <i class="fa fa-plus"></i>
                            เพิ่มรายการสินค้า
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>รหัสสินค้า</th>
                                    <th>รูปสินค้า</th>
                                    <th>รูปแบบสินค้า</th>
                                    <th>สี</th>
                                    <th>ขนาด</th>
                                    <th>ราคาทุน</th>
                                    <th>ราคาขาย</th>
                                    <th>สต๊อก</th>
                                    <th style="width:85px;">จัดการ</th>
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
                    <span class="fw-mediumbold"> แก้ไข</span>
                    <span class="fw-light"> สินค้า </span>
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
                                <div class="row">
                                    <?php
                                        $obj2 = $DB->QueryObj("SELECT * FROM img WHERE product_id = '".$_GET["product_id"]."'");
                                        foreach ($obj2 as $key2 => $value2) {
                                    ?>
                                    <div class="col-6 col-md-2 col-sm-4">
                                        <label class="imagecheck mb-4">
                                            <input name="img_id" type="radio" value="<?=$value2["img_id"];?>"
                                                class="imagecheck-input" />
                                            <figure class="imagecheck-figure">
                                                <img src="../file/product/<?=$value2["img_name"];?>" alt="title"
                                                    class="imagecheck-image" />
                                            </figure>
                                        </label>
                                    </div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_style">รูปแบบสินค้า</label>
                                <input type="text" class="form-control" id="variant_style" name="variant_style"
                                    placeholder="รูปแบบสินค้า...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_color">สี</label>
                                <input type="text" class="form-control" id="variant_color" name="variant_color"
                                    placeholder="สี...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_size">ขนาด</label>
                                <input type="text" class="form-control" id="variant_size" name="variant_size"
                                    placeholder="ขนาด...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="size_detail">รายละเอียดของขนาดสินค้า</label>
                                <input type="text" class="form-control" id="size_detail" name="size_detail"
                                    placeholder="รายละเอียดของขนาดสินค้า...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_price">ราคาต้นทุน</label>
                                <input type="text" class="form-control" id="variant_price" name="variant_price"
                                    placeholder="ราคาต้นทุน...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_sale">ราคาขาย</label>
                                <input type="text" class="form-control" id="variant_sale" name="variant_sale"
                                    placeholder="ราคาขาย...">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="variant_stock">สต๊อกสินค้า</label>
                                <input type="text" class="form-control" id="variant_stock" name="variant_stock"
                                    placeholder="สต๊อกสินค้า...">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="edit-variant" class="btn btn-warning">
                    แก้ไขสินค้า
                </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>