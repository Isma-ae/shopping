<?php 

if (!isset($_SESSION["user_info"])) {
    echo "<script>window.location.href = './';</script>";
    exit(); // สำคัญ! หยุดการทำงานของสคริปต์หลัง redirect
}
?>

<section class="bg1 txt-center p-lr-15 p-tb-92">
    <h2 class="ltext-105 cl0 txt-center">
        รายการสั่งซื้อ
    </h2>
</section>
<section class="sec-product-detail bg0 p-b-60">
    <div class="container">
        <div class="m-t-50 p-t-43 p-b-40">
            <!-- Tab01 -->
            <div class="tab01">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <?php
                        $sqlsum = "SELECT COUNT(order_id) AS sum_order FROM orders WHERE user_id = '".htmlspecialchars($currentUser['id'])."'";
                        $sum1 = $DB->QueryObj($sqlsum." AND status_id = 1");
                        $sum2 = $DB->QueryObj($sqlsum." AND status_id = 2");
                        $sum3 = $DB->QueryObj($sqlsum." AND status_id = 3");
                    ?>
                    <li class="nav-item p-b-10">
                        <a class="nav-link active" data-toggle="tab" href="#description" role="tab">รายการที่รอชำระ
                            <?=($sum1[0]["sum_order"] > 0) ? '<span class="cl1">('.$sum1[0]["sum_order"].')</span>' : '' ;?></a>
                    </li>

                    <li class="nav-item p-b-10">
                        <a class="nav-link" data-toggle="tab" href="#information" role="tab">รายการที่รอจัดส่ง
                            <?=($sum2[0]["sum_order"] > 0) ? '<span class="cl1">('.$sum2[0]["sum_order"].')</span>' : '' ;?></a>
                    </li>

                    <li class="nav-item p-b-10">
                        <a class="nav-link" data-toggle="tab" href="#reviews" role="tab">รายการที่จัดส่งแล้ว
                            <?=($sum3[0]["sum_order"] > 0) ? '<span class="cl1">('.$sum3[0]["sum_order"].')</span>' : '' ;?></a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-t-43">
                    <?php
                        $sql="SELECT * FROM orders 
                            LEFT JOIN tb_address ON orders.address_id = tb_address.id
                            LEFT JOIN provinces
                                ON tb_address.province_id = provinces.province_id
                            LEFT JOIN districts
                                ON tb_address.district_id = districts.district_id
                            LEFT JOIN subdistricts
                                ON tb_address.subdistrict_id = subdistricts.subdistrict_id
                            LEFT JOIN transported
                                ON orders.transported_id = transported.transported_id
                            WHERE orders.user_id = '".htmlspecialchars($currentUser['id'])."'";
                    ?>
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="p-lr-15-md">
                            <?php
                                $obj = $DB->QueryObj($sql." AND status_id = 1");
                                if (sizeof($obj) > 0) {
                                    foreach ($obj as $key => $value) {
                                        if ($value["shipping_type"] == "1") {
                                            $ship_type = 'รับหน้าร้าน';
                                            $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value["shipping_name"].'
                                            </p>';
                                        } elseif ($value["shipping_type"] == "2") {
                                            $ship_type = 'ส่งภายในวิทยาเขต';
                                            $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value["shipping_name"].'
                                                <span class="cl12 ml-4 mr-6">|</span>
                                                โทร'.$value["shipping_phone"].'
                                                <span class="cl6">คณะ / หน่วยงาน:</span> '.$value["shipping_department"].'
                                            </p>';
                                        } else {
                                            $ship_type = 'ส่งภายนอกวิทยาเขต';
                                            $address = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value["name"].' 
                                                <span class="cl12 ml-4 mr-6">|</span>
                                                <span class="cl6">โทร:</span> '.$value["phone"].'<br>
                                                <span class="cl6">ที่อยู่:</span> '.$value["address_at"].' ตำบล'.$value["subdistrict_name_in_thai"].' อำเภอ'.$value["district_name_in_thai"].' จังหวัด'.$value["province_name_in_thai"].' '.$value["zip_code"].'
                                            </p>';
                                        }
                                        
                            ?>
                            <div class="card m-t-13">
                                <div class="card-header">
                                    <div class="flex-w flex-sb-m">
                                        <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                            รหัสใบสั่งซื้อ:
                                            <span class="stext-101 cl1">
                                                <?=$value["order_no"];?>
                                            </span>
                                        </span>

                                        <strong class="stext-101 cl1 m-tb-10">
                                            รายการรอชำระ
                                        </strong>
                                    </div>
                                </div>
                                <div class="card-body bor10">
                                    <p class="stext-116 cl8 trans-04">
                                        <span class="cl6">วิธีการรับสินค้า:</span> <?=$ship_type;?>
                                    </p>
                                    <?=$address?>
                                    <?php
                                        $sql2 = "SELECT * FROM order_detail
                                                INNER JOIN variants
                                                    ON order_detail.variant_id = variants.variant_id
                                                LEFT JOIN img
                                                    ON variants.img_id = img.img_id
                                                WHERE order_id = '".$value["order_id"]."'";
                                        $obj2 = $DB->QueryObj($sql2);
                                        foreach ($obj2 as $i => $value2) {
                                    ?>
                                    <li class="flex-w p-b-30 p-t-30 p-lr-40 bor18 m-t-10"
                                        style="display:flex; align-items:center;">
                                        <!-- รูปสินค้า -->
                                        <a href="#" class="wrao-pic-w size-214 hov-ovelay1 m-r-20">
                                            <img src="file/product/<?=$value2["img_name"];?>" alt="PRODUCT"
                                                width="100%">
                                        </a>

                                        <!-- กล่องรายละเอียด -->
                                        <div class="flex-grow-1">
                                            <a href="#" class="stext-116 cl8 hov-cl1 trans-04">
                                                <?=$value2["product_name"];?>
                                            </a>

                                            <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                                <span>
                                                    <span class="cl4">สี</span> <?=$value2["variant_color"];?>
                                                    <span class="cl12 m-l-4 m-r-6">|</span>
                                                </span>

                                                <span>
                                                    <span class="cl4">ขนาด</span> <?=$value2["variant_size"];?>
                                                </span>
                                            </span>

                                            <span class="stext-116 cl6 p-t-10">
                                                x <?=$value2["qty"];?>
                                            </span>
                                        </div>

                                        <!-- ราคา -->
                                        <div style="text-align:right; min-width:80px; margin-left:auto;">
                                            <span class="stext-116 cl1">
                                                ฿<?=$value2["total_price"];?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php
                                        }
                                    ?>

                                </div>
                                <div class="d-flex p-t-18 p-b-15 p-lr-40 p-lr-15-sm"
                                    style="align-items:center; justify-content: space-between;">
                                    <!-- ข้อความแจ้งเตือนอยู่ซ้าย -->
                                    <div>
                                        <span class="stext-116 cl8 trans-04">
                                            ชำระเงินภายใน
                                            <span class="cl1">
                                                <?=date("d-m-Y H:i:s", strtotime($value["create_date"] . " +24 hours"));?>
                                            </span>
                                            โดยQR พร้อมเพย์
                                        </span>
                                    </div>

                                    <!-- กล่องรวมการสั่งซื้อ + ปุ่ม อยู่ขวา -->
                                    <div class="text-right">
                                        <div class="cl1 mb-2">
                                            รวมการสั่งซื้อ: <span class="mtext-112">฿<?=$value["total_price"];?></span>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 p-t-20">
                                            <a href="payment/?order_no=<?=$value["order_no"];?>"
                                                class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn1 p-lr-15 m-r-10 trans-04">
                                                ชำระเงินตอนนี้
                                            </a>
                                            <a href="#"
                                                class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04"
                                                order-id="<?=$value["order_id"];?>" id="cancel-order">
                                                ยกเลิกคำสั่งซื้อ
                                            </a>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <?php
                                    }
                                }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="information" role="tabpanel">
                        <div class="p-lr-15-md">
                            <?php
                                $obj0 = $DB->QueryObj($sql." AND status_id = 2");
                                if (sizeof($obj0) > 0) {
                                    foreach ($obj0 as $key0 => $value0) {
                                        if ($value0["shipping_type"] == "1") {
                                            $ship_type0 = 'รับหน้าร้าน';
                                            $address0 = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value["shipping_name"].'
                                            </p>';
                                        } elseif ($value0["shipping_type"] == "2") {
                                            $ship_type0 = 'ส่งภายในวิทยาเขต';
                                            $address0 = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value0["shipping_name"].'
                                                <span class="cl12 ml-4 mr-6">|</span> 
                                                โทร: '.$value0["shipping_phone"].'<br>
                                                <span class="cl6">คณะ / หน่วยงาน:</span> '.$value0["shipping_department"].'
                                            </p>';
                                        } else {
                                            $ship_type0 = 'ส่งภายนอกวิทยาเขต';
                                            $address0 = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value0["name"].'
                                                <span class="cl12 ml-4 mr-6">|</span>
                                                โทร'.$value0["phone"].'<br>
                                                <span class="cl6">ที่อยู่:</span> '.$value0["address_at"].' ตำบล'.$value0["subdistrict_name_in_thai"].' อำเภอ'.$value0["district_name_in_thai"].' จังหวัด'.$value0["province_name_in_thai"].' '.$value0["zip_code"].'
                                                <span class="cl12 m-l-4 m-r-6">|</span>
                                                <span class="cl6">จัดส่งโดย:</span> '.$value0["transported_name"].'
                                                <span class="cl12 m-l-4 m-r-6">|</span>
                                                <span class="cl6">หมายเลขพัสดุ:</span> '.$value0["parcel_number"].'
                                            </p>';
                                        }
                                        
                            ?>
                            <div class="card m-t-13">
                                <div class="card-header">
                                    <div class="flex-w flex-sb-m">
                                        <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                            รหัสใบสั่งซื้อ:
                                            <span class="stext-101 cl1">
                                                <?=$value0["order_no"];?>
                                            </span>
                                        </span>

                                        <strong class="stext-101 cl1 m-tb-10">
                                            รายการรอจัดส่ง
                                        </strong>
                                    </div>
                                </div>
                                <div class="card-body bor10">
                                    <p class="stext-116 cl8 trans-04">
                                        <span class="cl6">วิธีการรับสินค้า:</span> <?=$ship_type0;?>
                                    </p>
                                    <?=$address0?>
                                    <?php
                                        $sql02 = "SELECT * FROM order_detail
                                                INNER JOIN variants
                                                    ON order_detail.variant_id = variants.variant_id
                                                LEFT JOIN img
                                                    ON variants.img_id = img.img_id
                                                WHERE order_id = '".$value0["order_id"]."'";
                                        $obj02 = $DB->QueryObj($sql02);
                                        foreach ($obj02 as $i0 => $value02) {
                                    ?>
                                    <li class="flex-w p-b-30 p-t-30 p-lr-40 bor18 m-t-10"
                                        style="display:flex; align-items:center;">
                                        <!-- รูปสินค้า -->
                                        <a href="#" class="wrao-pic-w size-214 hov-ovelay1 m-r-20">
                                            <img src="file/product/<?=$value02["img_name"];?>" alt="PRODUCT"
                                                width="100%">
                                        </a>

                                        <!-- กล่องรายละเอียด -->
                                        <div class="flex-grow-1">
                                            <a href="#" class="stext-116 cl8 hov-cl1 trans-04">
                                                <?=$value02["product_name"];?>
                                            </a>

                                            <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                                <span>
                                                    <span class="cl4">สี</span> <?=$value02["variant_color"];?>
                                                    <span class="cl12 m-l-4 m-r-6">|</span>
                                                </span>

                                                <span>
                                                    <span class="cl4">ขนาด</span> <?=$value02["variant_size"];?>
                                                </span>
                                            </span>

                                            <span class="stext-116 cl6 p-t-10">
                                                x <?=$value02["qty"];?>
                                            </span>
                                        </div>

                                        <!-- ราคา -->
                                        <div style="text-align:right; min-width:80px; margin-left:auto;">
                                            <span class="stext-116 cl1">
                                                ฿<?=$value02["total_price"];?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php
                                        }
                                    ?>

                                </div>
                                <div class="d-flex p-t-18 p-b-15 p-lr-40 p-lr-15-sm"
                                    style="align-items:center; justify-content: space-between;">
                                    <!-- ข้อความแจ้งเตือนอยู่ซ้าย -->
                                    <div>
                                        <?php
                                            if($value0["receipt_link"] != ""){
                                        ?>
                                        <span class="stext-116 cl8 trans-04">
                                            เปิดดูใบเสร็จรับเงิน
                                            <a href="<?=$value0["receipt_link"]?>" target="_blank">
                                                คลิ๊กที่นี่
                                            </a>
                                        </span>
                                        <?php }?>
                                    </div>

                                    <!-- กล่องรวมการสั่งซื้อ + ปุ่ม อยู่ขวา -->
                                    <div class="text-right">
                                        <div class="cl1 mb-2">
                                            รวมการสั่งซื้อ: <span class="mtext-112">฿<?=$value0["total_price"];?></span>
                                        </div>

                                    </div>
                                </div>


                            </div>

                            <?php
                                    }
                                }
                            ?>
                        </div>
                    </div>

                    <!-- - -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="p-lr-15-md">
                            <?php
                                $obj00 = $DB->QueryObj($sql." AND status_id = 3");
                                if (sizeof($obj00) > 0) {
                                    foreach ($obj00 as $key00 => $value00) {
                                        if ($value00["shipping_type"] == "1") {
                                            $ship_type00 = 'รับหน้าร้าน';
                                            $address00 = '';
                                        } elseif ($value00["shipping_type"] == "2") {
                                            $ship_type00 = 'ส่งภายในวิทยาเขต';
                                            $address00 = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ผู้รับ:</span> '.$value00["shipping_name"].'
                                                <span class="cl12 ml-4 mr-6">|</span> 
                                                โทร: '.$value00["shipping_phone"].'<br>
                                                <span class="cl6">คณะ / หน่วยงาน:</span> '.$value00["shipping_department"].'
                                            </p>';
                                        } else {
                                            $ship_type00 = 'ส่งภายนอกวิทยาเขต';
                                            $address00 = '<p class="stext-116 cl8 trans-04 p-t-10">
                                                <span class="cl6">ที่อยู่:</span> '.$value00["address_at"].' ตำบล'.$value00["subdistrict_name_in_thai"].' อำเภอ'.$value00["district_name_in_thai"].' จังหวัด'.$value00["province_name_in_thai"].' '.$value00["zip_code"].'
                                                <span class="cl12 m-l-4 m-r-6">|</span>
                                                <span class="cl6">จัดส่งโดย:</span> '.$value00["transported_name"].'
                                                <span class="cl12 m-l-4 m-r-6">|</span>
                                                <span class="cl6">หมายเลขพัสดุ:</span> '.$value00["parcel_number"].'
                                            </p>';
                                        }
                                        
                            ?>
                            <div class="card m-t-13">
                                <div class="card-header">
                                    <div class="flex-w flex-sb-m">
                                        <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                            รหัสใบสั่งซื้อ:
                                            <span class="stext-101 cl1">
                                                <?=$value00["order_no"];?>
                                            </span>
                                        </span>

                                        <strong class="stext-101 cl1 m-tb-10">
                                            รายการจัดส่งแล้ว
                                        </strong>
                                    </div>
                                </div>
                                <div class="card-body bor10">
                                    <p class="stext-116 cl8 trans-04">
                                        <span class="cl6">วิธีการรับสินค้า:</span> <?=$ship_type00;?>
                                    </p>
                                    <?=$address00?>
                                    <?php
                                        $sql002 = "SELECT * FROM order_detail
                                                INNER JOIN variants
                                                    ON order_detail.variant_id = variants.variant_id
                                                LEFT JOIN img
                                                    ON variants.img_id = img.img_id
                                                WHERE order_id = '".$value00["order_id"]."'";
                                        $obj002 = $DB->QueryObj($sql002);
                                        foreach ($obj002 as $i00 => $value002) {
                                    ?>
                                    <li class="flex-w p-b-30 p-t-30 p-lr-40 bor18 m-t-10"
                                        style="display:flex; align-items:center;">
                                        <!-- รูปสินค้า -->
                                        <a href="#" class="wrao-pic-w size-214 hov-ovelay1 m-r-20">
                                            <img src="file/product/<?=$value002["img_name"];?>" alt="PRODUCT"
                                                width="100%">
                                        </a>

                                        <!-- กล่องรายละเอียด -->
                                        <div class="flex-grow-1">
                                            <a href="#" class="stext-116 cl8 hov-cl1 trans-04">
                                                <?=$value002["product_name"];?>
                                            </a>

                                            <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                                <span>
                                                    <span class="cl4">สี</span> <?=$value002["variant_color"];?>
                                                    <span class="cl12 m-l-4 m-r-6">|</span>
                                                </span>

                                                <span>
                                                    <span class="cl4">ขนาด</span> <?=$value002["variant_size"];?>
                                                </span>
                                            </span>

                                            <span class="stext-116 cl6 p-t-10">
                                                x <?=$value002["qty"];?>
                                            </span>
                                        </div>

                                        <!-- ราคา -->
                                        <div style="text-align:right; min-width:80px; margin-left:auto;">
                                            <span class="stext-116 cl1">
                                                ฿<?=$value002["total_price"];?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php
                                        }
                                    ?>

                                </div>
                                <div class="d-flex p-t-18 p-b-15 p-lr-40 p-lr-15-sm"
                                    style="align-items:center; justify-content: space-between;">
                                    <!-- ข้อความแจ้งเตือนอยู่ซ้าย -->
                                    <div>
                                        <?php
                                            if($value00["receipt_link"] != ""){
                                        ?>
                                        <span class="stext-116 cl8 trans-04">
                                            เปิดดูใบเสร็จรับเงิน
                                            <a href="<?=$value00["receipt_link"]?>" target="_blank">
                                                คลิ๊กที่นี่
                                            </a>
                                        </span>
                                        <?php }?>
                                    </div>

                                    <!-- กล่องรวมการสั่งซื้อ + ปุ่ม อยู่ขวา -->
                                    <div class="text-right">
                                        <div class="cl1 mb-2">
                                            รวมการสั่งซื้อ: <span
                                                class="mtext-112">฿<?=$value00["total_price"];?></span>
                                        </div>

                                    </div>
                                </div>


                            </div>

                            <?php
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>