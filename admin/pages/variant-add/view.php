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
                        <?php
                            $sql = "SELECT
                                        product_id,
                                        product_name
                                    FROM product
                                    WHERE product_id = '".$_GET["product_id"]."'";
                            $obj = $DB->QueryObj($sql);
                        ?>
                        <input type="hidden" id="product_id" value="<?=$obj[0]["product_id"];?>">
                        <h4 class="card-title">เพิ่มรายการ <?=$obj[0]["product_name"];?></h4>
                        <a class="btn btn-success btn-round ms-auto" href="#" id="choose-variant">
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