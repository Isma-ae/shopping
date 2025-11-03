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
                <a href="?page=orders">ย้อนกลับ</a>
            </li>
        </ul>
    </div>
    <?php
        $sql="SELECT
                order_id,
                order_no,
                orders.user_id,
                tb_address.name AS address_recipient_name,
                tb_address.phone AS address_recipient_phone,
                receipt_id,
                receipt_name,
                receipt_number,
                receipt_address,
                receipt_link,
                orders.shipping_name,
                total_price,
                orders.status_id,
                orders.shipping_type,
                orders.shipping_name,
                orders.shipping_phone,
                orders.shipping_department,
                address_at,
                subdistrict_name_in_thai,
                district_name_in_thai,
                province_name_in_thai,
                zip_code,
                orders.transported_id,
                transported_name,
                parcel_number
            FROM orders 
            LEFT JOIN tb_address ON orders.address_id = tb_address.id
            LEFT JOIN provinces
                ON tb_address.province_id = provinces.province_id
            LEFT JOIN districts
                ON tb_address.district_id = districts.district_id
            LEFT JOIN subdistricts
                ON tb_address.subdistrict_id = subdistricts.subdistrict_id
            LEFT JOIN transported
                ON orders.transported_id = transported.transported_id
            WHERE order_id = '".$_GET["order_id"]."'";
        $obj = $DB->QueryObj($sql);
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">รายละเอียดสินค้าในใบสั่งซื้อ</h4>
                        <?php
                            if ($obj[0]["status_id"] == 2 && $obj[0]["shipping_type"] == 3) {
                        ?>
                        <a class="btn btn-outline-success btn-round ms-auto"
                            href="print.php?order_id=<?=$_GET["order_id"];?>" target="_blank">
                            <i class="fa fa-print"></i>
                            พิมพ์ที่อยู่จัดส่ง
                        </a>
                        <?php }?>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" id="order_id" value="<?=$_GET["order_id"];?>">
                    <?php
                        if ($obj[0]["shipping_type"] == "1") {
                            $ship_type = '<span class="text-primary fw-bold">รับหน้าร้าน</span>';
                            if($obj[0]["shipping_name"] != "") {
                                $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                    <span class="cl6">ผู้รับ:</span> '.$obj[0]["shipping_name"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6">โทร:</span> '.$obj[0]["shipping_phone"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6">คณะ / หน่วยงาน:</span> '.$obj[0]["shipping_department"].'
                                </p>';
                            } else {
                                $address = '';
                            }
                        } elseif ($obj[0]["shipping_type"] == "2") {
                            $ship_type = '<span class="text-primary fw-bold">ส่งภายในวิทยาเขต</span>';
                            $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                <span class="cl6">ผู้รับ:</span> '.$obj[0]["shipping_name"].'
                                <span class="cl12 ml-4 mr-6">|</span>
                                <span class="cl6">โทร:</span> '.$obj[0]["shipping_phone"].'
                                <span class="cl12 ml-4 mr-6">|</span>
                                <span class="cl6">คณะ / หน่วยงาน:</span> '.$obj[0]["shipping_department"].'
                            </p>';
                        } else {
                            $ship_type = '<span class="text-primary fw-bold">ส่งภายนอกวิทยาเขต</span>';
                            if ($obj[0]["user_id"] != 0) {
                                $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                    <span class="cl6">ผู้รับ:</span> '.$obj[0]["address_recipient_name"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6">โทร:</span> '.$obj[0]["address_recipient_phone"].'<br>
                                    <span class="cl6">ที่อยู่:</span> '.$obj[0]["address_at"].' ตำบล'.$obj[0]["subdistrict_name_in_thai"].' อำเภอ'.$obj[0]["district_name_in_thai"].' จังหวัด'.$obj[0]["province_name_in_thai"].' '.$obj[0]["zip_code"].'
                                    <br>
                                    <span class="cl6 text-primary">จัดส่งโดย:</span> '.$obj[0]["transported_name"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6 text-primary">หมายเลขพัสดุ:</span> '.$obj[0]["parcel_number"].'
                                </p>';
                            } else {
                                $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                    <span class="cl6">ผู้รับ:</span> '.$obj[0]["shipping_name"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6">โทร:</span> '.$obj[0]["shipping_phone"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6">ที่อยู่:</span> '.$obj[0]["shipping_department"].'
                                    <br>
                                    <span class="cl6 text-primary">จัดส่งโดย:</span> '.$obj[0]["transported_name"].'
                                    <span class="cl12 ml-4 mr-6">|</span>
                                    <span class="cl6 text-primary">หมายเลขพัสดุ:</span> '.$obj[0]["parcel_number"].'
                                </p>';
                            }
                        }

                        if ($obj[0]["receipt_id"] == 1) {
                            $receipt = '<p class="stext-116 cl8 trans-04 p-t-10">
                                <span class="cl6 text-primary fw-bold">นามออกใบเสร็จ:</span> '.$obj[0]["receipt_name"].'
                                <span class="cl12 ml-4 mr-6">|</span>
                                <span class="cl6 text-primary fw-bold">หมายเลขผู้เสียภาษี:</span> '.$obj[0]["receipt_number"].'<br>
                                <span class="cl6 text-primary fw-bold">ที่อยู่:</span> '.$obj[0]["receipt_address"].'
                            </p>';
                        } else {
                            $receipt = '';
                        }
                        $sql2 = "SELECT * FROM order_detail
                                INNER JOIN variants
                                    ON order_detail.variant_id = variants.variant_id
                                LEFT JOIN img
                                    ON variants.img_id = img.img_id
                                WHERE order_id = '".$_GET["order_id"]."'";
                        $obj2 = $DB->QueryObj($sql2);
                    ?>
                    <div class="p-3">
                        <div class="row">
                            <div class="col">
                                <h6 class="fw-bold mb-3 text-primary">🛒 <?=$obj[0]["order_no"];?></h6>
                            </div>
                            <div class="col" style="text-align:right">
                                <h6 class="fw-bold mb-3">
                                    สถานะ:
                                    <?php
                                        if ($obj[0]["status_id"] == 1) {
                                            echo '<span class="text-warning">รอชำระเงิน</span>';
                                        } elseif ($obj[0]["status_id"] == 2) {
                                            echo '<span class="text-primary">รอจัดส่ง</span>';
                                        } elseif ($obj[0]["status_id"] == 3) {
                                            echo '<span class="text-success">จัดส่งแล้ว</span>';
                                        } else {
                                            echo '<span class="text-danger">ยกเลิก</span>';
                                        }
                                    ?>
                                </h6>
                            </div>
                        </div>
                        <p class="stext-116 cl8 trans-04">
                            <span class="cl6">วิธีการรับสินค้า:</span> <?=$ship_type;?>
                        </p>
                        <?=$address?>
                        <?=$receipt?>

                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:10%">สินค้า</th>
                                        <th></th>
                                        <th style="width:15%">ราคา/หน่วย</th>
                                        <th style="width:10%">จำนวน</th>
                                        <th class="txt-right" style="width:15%">ราคารวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($obj2 as $i => $value2) {
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <img src="../file/product/<?=$value2["img_name"];?>" width="50px;">
                                        </td>
                                        <td>
                                            <?=$value2["product_name"];?><br>
                                            <?=$value2["variant_color"];?> | <?=$value2["variant_size"];?>
                                        </td>
                                        <td>฿<?= $value2["variant_sale"]; ?></td>
                                        <td><?= $value2["qty"]; ?></td>
                                        <td class="txt-right">฿<?= $value2["total_price"]; ?></td>
                                    </tr>
                                    <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <?php
                                    if($obj[0]["receipt_link"] != ""){
                                ?>
                                <p>เปิดดูใบเสร็จรับเงิน <a href="<?=$obj[0]["receipt_link"]?>"
                                        target="_blank">คลิ๊กที่นี่</a></p>
                                <?php }?>
                            </div>
                            <div class="col txt-right">
                                <h6 class="fw-bold mb-3 text-primary">รวมการสั่งซื้อ: ฿<?=$obj[0]["total_price"];?></h6>
                            </div>
                        </div>
                        <?php
                            $statusId = $obj[0]["status_id"];
                            $shippingType = $obj[0]["shipping_type"];
                            $statusEligible = in_array($statusId, [2, 3]);

                            
                            // ปุ่มอัปเดทข้อมูลจัดส่ง (admin_type 4, status_id 2/3, shipping_type 3)
                            if ($statusEligible && $shippingType == 3) {
                        ?>
                        <button type="button" class="btn btn-warning" id="update-shipping">
                            <i class="fas fa-edit"></i> อัปเดทข้อมูลจัดส่ง
                        </button>
                        <?php    }

                            // ปุ่มอัปเดทใบเสร็จรับเงิน (admin_type 3, status_id 2/3)
                            if ($statusEligible) {
                        ?>
                        <button type="button" class="btn btn-success" id="update-receipt">
                            <i class="fas fa-file-upload"></i> อัปเดทใบเสร็จรับเงิน
                        </button>
                        <?php    }
                        ?>
                        <button type="button" class="btn btn-primary" id="update-status">
                            <i class="fas fa-exchange-alt"></i> เปลี่ยนสถานะ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="transported_now" value="<?=$obj[0]["transported_id"]?>">
<input type="hidden" id="parcel_now" value="<?=$obj[0]["parcel_number"]?>">
<div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="shippingModalLabel">ข้อมูลจัดส่ง</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="defaultSelect">จัดส่งโดย</label>
                    <select class="form-select form-control" id="transported_id">
                        <option value="0" disabled selected>-- กรุณาเลือกบริษัทขนส่ง --</option>
                        <?php
                            $transporteds = $DB->QueryObj("SELECT * FROM transported");
                            foreach ($transporteds as $transported) {
                                if ($transported["transported_id"] == $obj[0]["transported_id"]) {
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }
                                echo '<option value="'.$transported["transported_id"].'" '.$selected.'>'.$transported["transported_name"].'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="parcel_number">หมายเลขพัสดุ</label>
                    <input type="text" class="form-control" id="parcel_number" value="<?=$obj[0]["parcel_number"];?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="save-shipping">บัททึก</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="receipt_now" value="<?=$obj[0]["receipt_link"]?>">
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="receiptModalLabel">อัพเดทใบเสร็จรับเงิน</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="receipt_link">ลิงค์ใบเสร็จรับเงิน</label>
                    <input type="text" class="form-control" id="receipt_link" value="<?=$obj[0]["receipt_link"];?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="save-receipt">บัททึก</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="status_now" value="<?=$obj[0]["status_id"]?>">
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="statusModalLabel">อัพเดทสถานะ</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">สถานะ</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="status_id" value="1" class="selectgroup-input"
                                <?=($obj[0]["status_id"] == 1) ? 'checked=""' : '' ;?>>
                            <span class="selectgroup-button">รอชำระเงิน</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status_id" value="2" class="selectgroup-input"
                                <?=($obj[0]["status_id"] == 2) ? 'checked=""' : '' ;?>>
                            <span class="selectgroup-button">รอจัดส่ง</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status_id" value="3" class="selectgroup-input"
                                <?=($obj[0]["status_id"] == 3) ? 'checked=""' : '' ;?>>
                            <span class="selectgroup-button">จัดส่งแล้ว</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status_id" value="4" class="selectgroup-input"
                                <?=($obj[0]["status_id"] == 4) ? 'checked=""' : '' ;?>>
                            <span class="selectgroup-button">ยกเลิก</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="save-status">บัททึก</button>
            </div>
        </div>
    </div>
</div>