<div class="container">
    <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
        <a href="index.html" class="stext-109 cl8 hov-cl1 trans-04">
            หน้าหลัก
            <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
        </a>

        <span class="stext-109 cl4">
            เช็คเอาท์
        </span>
    </div>
</div>
<form class="bg0 p-t-75 p-b-85">
    <div class="container">
        <div class="m-l-25 m-r--38 m-lr-0-xl">
            <div class="wrap-table-shopping-cart">
                <table class="table-shopping-cart">
                    <thead>
                        <tr class="table_head">
                            <th class="column-1">สินค้า</th>
                            <th class="column-2"></th>
                            <th class="column-3">ราคา</th>
                            <th class="column-4">จำนวน</th>
                            <th class="column-5">รวม</th>
                        </tr>
                    </thead>
                    <tbody class="cart_data">
                    </tbody>
                </table>
            </div>
            <div class="bor15 p-t-18 p-b-15 p-lr-40 p-lr-15-sm">

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="receipt_id" name="receipt_id">
                    <label class="form-check-label" for="receipt_id">
                        ข้อมูลออกใบเสร็จ (กรณีที่ต้องการให้ส่งใบเสร็จ)
                    </label>
                </div>
                <div class="row receipt_form">
                    <div class="col-6 p-b-5">
                        <label class="stext-102 cl3" for="receipt_name">นามออกใบเสร็จ <span class="clr">*</span></label>
                        <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="receipt_name" type="text"
                            name="receipt_name">
                    </div>

                    <div class="col-6 p-b-5">
                        <label class="stext-102 cl3" for="receipt_number">หมายเลขผู้เสียภาษี<span
                                class="clr">*</span></label>
                        <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="receipt_number" type="text"
                            name="receipt_number">
                    </div>
                    <div class="col-12">
                        <label class="stext-102 cl3" for="receipt_address">ที่อยู่ <span class="clr">*</span></label>
                        <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="receipt_address" type="text"
                            name="receipt_address">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 col-xl-7 m-lr-auto m-b-50">
                <div class="bor10 m-tb-20 m-l-25 m-r--38 m-lr-0-xl p-lr-40 p-t-30 p-b-40 p-lr-15-sm">
                    <div class="flex-w flex-t p-t-15 p-b-30 bor12">
                        <div class="my-size-208 w-full-ssm">
                            <span class="stext-110 cl2">
                                วิธการรับสินค้า:
                            </span>
                        </div>

                        <div class="my-size-209 p-r-18 p-r-0-sm w-full-ssm">
                            <div class="selectgroup selectgroup-pills">
                                <label class="selectgroup-item">
                                    <input type="radio" name="shipping_type" id="shipping-type-1" value="1"
                                        class="selectgroup-input" checked />
                                    <span class="selectgroup-button">รับหน้าร้าน</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="shipping_type" id="shipping-type-2" value="2"
                                        class="selectgroup-input" />
                                    <span class="selectgroup-button">ส่งภายในวิทยาเขต</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="shipping_type" id="shipping-type-3" value="3"
                                        class="selectgroup-input" />
                                    <span class="selectgroup-button">ส่งภายนอกวิทยาเขต</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex-w flex-t p-t-15 p-b-30">

                        <div class="form-front">
                            <?php
                                if (htmlspecialchars($currentUser['role']) == 'admin') {
                            ?>
                            <div class="row">
                                <div class="col-6 p-b-5">
                                    <label class="stext-102 cl3" for="shipping_name">ชื่อ นามสกุล <span
                                            class="clr">*</span></label>
                                    <input class="size-111 bor8 stext-102 cl2 p-lr-20" type="text" name="shipping_name">
                                </div>

                                <div class="col-6 p-b-5">
                                    <label class="stext-102 cl3" for="shipping_phone">หมายเลขโทรศัพท์</label>
                                    <input class="size-111 bor8 stext-102 cl2 p-lr-20" type="text"
                                        name="shipping_phone">
                                </div>
                                <div class="col-12">
                                    <label class="stext-102 cl3" for="shipping_department">หน่วยงาน <span
                                            class="clr">*</span></label>
                                    <textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10"
                                        name="shipping_department"></textarea>
                                </div>
                            </div>
                            <?php
                                } else {
                            ?>
                            <p class="stext-111 cl6 p-t-2 form-front">
                                มารับได้ที่เคาน์เตอร์ยืมคืน สำนักวิทยบริการ ม.อ.ปัตตานี
                            </p>
                            <input id="shipping_name" type="hidden" name="shipping_name"
                                value="<?=htmlspecialchars($currentUser['name']);?>">
                            <input id="shipping_phone" type="hidden" name="shipping_phone"
                                value="<?=htmlspecialchars($currentUser['user_phone']);?>">
                            <input id="shipping_department" type="hidden" name="shipping_department" value="">
                            <?php
                                }
                            ?>
                        </div>
                        <div class="form-in">
                            <div class="row">
                                <div class="col-6 p-b-5">
                                    <label class="stext-102 cl3" for="shipping_name">ชื่อ นามสกุล <span
                                            class="clr">*</span></label>
                                    <input class="size-111 bor8 stext-102 cl2 p-lr-20" type="text" name="shipping_name2"
                                        value="<?=htmlspecialchars($currentUser['name']);?>">
                                </div>

                                <div class="col-6 p-b-5">
                                    <label class="stext-102 cl3" for="shipping_phone">หมายเลขโทรศัพท์ <span
                                            class="clr">*</span></label>
                                    <input class="size-111 bor8 stext-102 cl2 p-lr-20" type="text"
                                        name="shipping_phone2"
                                        value="<?=htmlspecialchars($currentUser['user_phone']);?>">
                                </div>
                                <div class="col-12">
                                    <label class="stext-102 cl3" for="shipping_department">หน่วยงาน <span
                                            class="clr">*</span></label>
                                    <textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10"
                                        name="shipping_department2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="p-l-13 form-out">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-10 col-lg-7 col-xl-5 m-lr-auto m-b-50">
                <div class="bor10 m-tb-20 m-l-25 m-r--38 m-lr-0-xl p-lr-40 p-t-30 p-b-40 p-lr-15-sm">
                    <h4 class="mtext-109 cl2 p-b-30">
                        รวมรายการ
                    </h4>
                    <div class="flex-w flex-t bor12 p-b-13">
                        <div class="my-size">
                            <span class="stext-110 cl2">
                                รวมการสั่งซื้อ:
                            </span>
                        </div>

                        <div class="my-size text-right">
                            <span class="mtext-110 cl2 cart-total">
                            </span>
                        </div>
                    </div>
                    <div class="flex-w flex-t bor12 p-b-13 p-t-13">
                        <div class="my-size">
                            <span class="stext-110 cl2">
                                ค่าจัดส่ง:
                            </span>
                        </div>

                        <div class="my-size text-right">
                            <span class="mtext-110 cl2 shipping-price">
                            </span>
                        </div>
                    </div>

                    <div class="flex-w flex-t p-t-27 p-b-33">
                        <div class="my-size">
                            <span class="mtext-101 cl2">
                                ยอดชำระเงินทั้งหมด
                            </span>
                        </div>

                        <div class="my-size p-t-1 text-right">
                            <span class="my-mtext cl2 total-price">
                            </span>
                        </div>
                    </div>

                    <button type="button"
                        class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-r-15 trans-04 pointer"
                        id="check-out">
                        ยืนยันสั่งซื้อ
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg1">
                <h5 class="modal-title cl0 mtext-112" id="addModalLabel">ที่อยู่ใหม่</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="w-full" id="form_address" data-mode="add">
                    <div class="row p-b-25">
                        <input type="hidden" id="address_id" name="address_id" value="">
                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="name">ชื่อ นามสกุล</label>
                            <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="name" type="text" name="name">
                        </div>

                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="phone">หมายเลขโทรศัพท์</label>
                            <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="phone" type="text" name="phone">
                        </div>
                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="province_id">จังหวัด</label>
                            <select class="js-example-basic-single" name="province_id" id="province_id">
                            </select>
                        </div>
                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="district_id">เขต / อำเภอ</label>
                            <select class="js-example-basic-single" name="district_id" id="district_id">
                            </select>
                        </div>
                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="subdistrict_id">แขวง / ตำบล</label>
                            <select class="js-example-basic-single" name="subdistrict_id" id="subdistrict_id">
                            </select>
                        </div>
                        <div class="col-6 p-b-5">
                            <label class="stext-102 cl3" for="zip_code">รหัสไปรษณีย์</label>
                            <input class="size-111 bor8 stext-102 cl2 p-lr-20" id="zip_code" type="text" name="zip_code"
                                readonly>
                        </div>
                        <div class="col-12 p-b-5">
                            <label class="stext-102 cl3" for="address_at">บ้านเลขที่, ซอย, หมู่, ถนน</label>
                            <textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10" id="address_at"
                                name="address_at"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="flex-c-m stext-101 cl0 size-112 bg5 bor11 hov-btn4 p-lr-15 trans-04 m-b-10"
                    data-dismiss="modal">
                    ยกเลิก
                </button>
                <button type="button" class="flex-c-m stext-101 cl0 size-112 bg1 bor1 hov-btn1 p-lr-15 trans-04 m-b-10"
                    id="btn_submit_address">
                    บันทึก
                </button>
            </div>
        </div>
    </div>
</div>