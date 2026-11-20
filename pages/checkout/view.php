<?php
    $checkoutUser = $currentUser ?? ($_SESSION['user_info'] ?? []);
    $checkoutRole = (string)($checkoutUser['role'] ?? '');
    $checkoutName = htmlspecialchars((string)($checkoutUser['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $checkoutPhone = htmlspecialchars((string)($checkoutUser['user_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!-- Breadcrumb -->
<section class="checkout-breadcrumb-bar">
    <div class="container">
        <nav class="checkout-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <a href="?page=cart">ตะกร้าสินค้า</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">ยืนยันคำสั่งซื้อ</span>
        </nav>
    </div>
</section>

<!-- Main Checkout Page -->
<section class="checkout-page-section">
    <div class="container">
        <div class="checkout-page-head">
            <div>
                <span class="oar-eyebrow">CHECKOUT</span>
                <h1 class="oar-heading">ตรวจสอบและยืนยันคำสั่งซื้อ</h1>
            </div>
            <div class="checkout-head-meta d-none" id="checkoutHeadMeta">
                <strong id="checkoutItemCount">0</strong> รายการ (<span id="checkoutQtyCount">0</span> ชิ้น)
            </div>
        </div>

        <form class="checkout-layout-form" onsubmit="return false;">
            <!-- Full Width: Card 1 Order Items -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="checkout-card">
                        <div class="checkout-card-head">
                            <div class="checkout-card-title">
                                <span class="checkout-step-num">1</span>
                                <span>รายการสินค้าที่สั่งซื้อ</span>
                            </div>
                            <a href="?page=cart" class="checkout-edit-cart-link">
                                <i class="fa fa-pencil mr-1" aria-hidden="true"></i>แก้ไขตะกร้า
                            </a>
                        </div>

                        <div class="checkout-item-cols">
                            <span>สินค้า</span>
                            <span class="text-right">ราคาต่อชิ้น</span>
                            <span class="text-center">จำนวน</span>
                            <span class="text-right">รวม</span>
                        </div>

                        <div class="cart_data checkout-item-list">
                            <div class="checkout-loading">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                                <span>กำลังโหลดรายการสินค้า...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-start">
                <!-- Left Column: Shipping & Receipt -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <!-- Card 2: Shipping Method -->
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-head">
                            <div class="checkout-card-title">
                                <span class="checkout-step-num">2</span>
                                <span>วิธีการรับสินค้า</span>
                            </div>
                        </div>

                        <div class="checkout-card-body">
                            <div class="checkout-shipping-options">
                                <label class="checkout-ship-option" for="shipping-type-1">
                                    <input type="radio" name="shipping_type" id="shipping-type-1" value="1" class="checkout-ship-radio" checked />
                                    <div class="checkout-ship-card">
                                        <div class="checkout-ship-icon">
                                            <i class="fa fa-university" aria-hidden="true"></i>
                                        </div>
                                        <div class="checkout-ship-info">
                                            <strong>รับหน้าร้าน</strong>
                                            <span>เคาน์เตอร์ยืมคืน สำนักวิทยบริการ</span>
                                        </div>
                                        <span class="checkout-ship-fee is-free">ฟรี</span>
                                    </div>
                                </label>

                                <label class="checkout-ship-option" for="shipping-type-2">
                                    <input type="radio" name="shipping_type" id="shipping-type-2" value="2" class="checkout-ship-radio" />
                                    <div class="checkout-ship-card">
                                        <div class="checkout-ship-icon">
                                            <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                        </div>
                                        <div class="checkout-ship-info">
                                            <strong>ส่งภายในวิทยาเขต</strong>
                                            <span>คณะ/หน่วยงาน ม.อ.ปัตตานี</span>
                                        </div>
                                        <span class="checkout-ship-fee is-free">ฟรี</span>
                                    </div>
                                </label>

                                <label class="checkout-ship-option" for="shipping-type-3">
                                    <input type="radio" name="shipping_type" id="shipping-type-3" value="3" class="checkout-ship-radio" />
                                    <div class="checkout-ship-card">
                                        <div class="checkout-ship-icon">
                                            <i class="fa fa-truck" aria-hidden="true"></i>
                                        </div>
                                        <div class="checkout-ship-info">
                                            <strong>ส่งภายนอกวิทยาเขต</strong>
                                            <span>จัดส่งพัสดุทั่วประเทศ</span>
                                        </div>
                                        <span class="checkout-ship-fee">เริ่มต้น ฿50</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Shipping Detail: Pickup -->
                            <div class="checkout-ship-panel form-front">
                                <?php if ($checkoutRole === 'admin') { ?>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="checkout-label" for="shipping_name">ชื่อ-นามสกุล <span class="clr">*</span></label>
                                            <input class="checkout-input" id="shipping_name" type="text" name="shipping_name" placeholder="กรอกชื่อผู้รับสินค้า">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="checkout-label" for="shipping_phone">หมายเลขโทรศัพท์</label>
                                            <input class="checkout-input" id="shipping_phone" type="text" name="shipping_phone" placeholder="เช่น 08x-xxx-xxxx">
                                        </div>
                                        <div class="col-12">
                                            <label class="checkout-label" for="shipping_department">หน่วยงาน <span class="clr">*</span></label>
                                            <textarea class="checkout-textarea" id="shipping_department" name="shipping_department" rows="2" placeholder="ระบุคณะหรือหน่วยงาน"></textarea>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="checkout-pickup-banner">
                                        <div class="checkout-pickup-icon">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <strong>สถานที่รับสินค้าหน้าร้าน</strong>
                                            <p class="mb-0">มารับได้ที่เคาน์เตอร์ยืมคืน สำนักวิทยบริการ มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี</p>
                                        </div>
                                    </div>
                                    <input id="shipping_name" type="hidden" name="shipping_name" value="<?= $checkoutName; ?>">
                                    <input id="shipping_phone" type="hidden" name="shipping_phone" value="<?= $checkoutPhone; ?>">
                                    <input id="shipping_department" type="hidden" name="shipping_department" value="">
                                <?php } ?>
                            </div>

                            <!-- Shipping Detail: In-campus -->
                            <div class="checkout-ship-panel form-in">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="checkout-label" for="shipping_name2">ชื่อ-นามสกุล <span class="clr">*</span></label>
                                        <input class="checkout-input" id="shipping_name2" type="text" name="shipping_name2" value="<?= $checkoutName; ?>" placeholder="กรอกชื่อผู้รับสินค้า">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="checkout-label" for="shipping_phone2">หมายเลขโทรศัพท์ <span class="clr">*</span></label>
                                        <input class="checkout-input" id="shipping_phone2" type="text" name="shipping_phone2" value="<?= $checkoutPhone; ?>" placeholder="เช่น 08x-xxx-xxxx">
                                    </div>
                                    <div class="col-12">
                                        <label class="checkout-label" for="shipping_department2">คณะ / หน่วยงาน / อาคาร <span class="clr">*</span></label>
                                        <textarea class="checkout-textarea" id="shipping_department2" name="shipping_department2" rows="2" placeholder="ระบุคณะ หน่วยงาน อาคาร หรือห้องที่ต้องการให้จัดส่งภายในวิทยาเขต"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Detail: Outside Campus Addresses -->
                            <div class="checkout-ship-panel form-out">
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Receipt Option -->
                    <div class="checkout-card">
                        <div class="checkout-card-head">
                            <label class="checkout-receipt-toggle" for="receipt_id">
                                <input class="form-check-input checkout-checkbox" type="checkbox" value="1" id="receipt_id" name="receipt_id">
                                <span class="checkout-step-num">3</span>
                                <span class="checkout-receipt-label">
                                    <strong>ต้องการออกใบเสร็จรับเงิน</strong>
                                    <small>เลือกกรณีต้องการให้จัดส่งหรือออกใบเสร็จในนามบุคคล/หน่วยงาน</small>
                                </span>
                            </label>
                        </div>

                        <div class="checkout-card-body receipt_form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="checkout-label" for="receipt_name">นามออกใบเสร็จ <span class="clr">*</span></label>
                                    <input class="checkout-input" id="receipt_name" type="text" name="receipt_name" placeholder="ชื่อบุคคล หรือ ชื่อหน่วยงาน">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="checkout-label" for="receipt_number">เลขประจำตัวผู้เสียภาษี <span class="clr">*</span></label>
                                    <input class="checkout-input" id="receipt_number" type="text" name="receipt_number" placeholder="เลขประจำตัวผู้เสียภาษี 13 หลัก">
                                </div>
                                <div class="col-12">
                                    <label class="checkout-label" for="receipt_address">ที่อยู่ออกใบเสร็จ <span class="clr">*</span></label>
                                    <input class="checkout-input" id="receipt_address" type="text" name="receipt_address" placeholder="ที่อยู่สำหรับระบุบนใบเสร็จ">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Order Summary -->
                <div class="col-lg-4">
                    <aside class="checkout-summary-card">
                        <h3 class="checkout-summary-title">สรุปยอดชำระเงิน</h3>

                        <div class="checkout-summary-rows">
                            <div class="checkout-summary-row">
                                <span>จำนวนสินค้า</span>
                                <strong id="checkoutSummaryQty">0 รายการ (0 ชิ้น)</strong>
                            </div>
                            <div class="checkout-summary-row">
                                <span>รวมค่าสินค้า</span>
                                <strong class="cart-total">฿0</strong>
                            </div>
                            <div class="checkout-summary-row">
                                <span>ค่าจัดส่ง</span>
                                <strong class="shipping-price">ฟรี</strong>
                            </div>
                            <div class="checkout-shipping-rate-note d-none" id="shippingRateNote">
                                <i class="fa fa-info-circle mr-1" aria-hidden="true"></i>
                                ค่าจัดส่งพัสดุชิ้นแรก ฿50 และชิ้นถัดไปชิ้นละ ฿10
                            </div>
                        </div>

                        <div class="checkout-summary-divider"></div>

                        <div class="checkout-summary-total">
                            <span>ยอดชำระเงินทั้งหมด</span>
                            <strong class="total-price">฿0</strong>
                        </div>

                        <button type="button" class="checkout-btn-submit" id="check-out">
                            <span>ยืนยันสั่งซื้อและชำระเงิน</span>
                            <i class="fa fa-long-arrow-right ml-2" aria-hidden="true"></i>
                        </button>

                        <a href="?page=cart" class="checkout-btn-back">
                            <i class="fa fa-angle-left mr-2" aria-hidden="true"></i>
                            <span>กลับไปหน้าตะกร้าสินค้า</span>
                        </a>

                        <div class="checkout-payment-note">
                            <img src="images/icons/Thai_QR_Logo.png" alt="Thai QR Payment">
                            <span>หลังยืนยันคำสั่งซื้อ ระบบจะสร้าง QR Code สำหรับสแกนชำระเงินทันที</span>
                        </div>
                    </aside>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Address Modal -->
<div class="modal fade checkout-modal" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header checkout-modal-header">
                <h5 class="modal-title" id="addModalLabel">เพิ่มที่อยู่จัดส่งใหม่</h5>
                <button type="button" class="close checkout-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body checkout-modal-body">
                <form class="w-full" id="form_address" data-mode="add">
                    <div class="row">
                        <input type="hidden" id="address_id" name="address_id" value="">
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="name">ชื่อ-นามสกุล <span class="clr">*</span></label>
                            <input class="checkout-input" id="name" type="text" name="name" placeholder="กรอกชื่อผู้รับพัสดุ">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="phone">หมายเลขโทรศัพท์ <span class="clr">*</span></label>
                            <input class="checkout-input" id="phone" type="text" name="phone" placeholder="เช่น 08x-xxx-xxxx">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="province_id">จังหวัด <span class="clr">*</span></label>
                            <select class="js-example-basic-single" name="province_id" id="province_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="district_id">เขต / อำเภอ <span class="clr">*</span></label>
                            <select class="js-example-basic-single" name="district_id" id="district_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="subdistrict_id">แขวง / ตำบล <span class="clr">*</span></label>
                            <select class="js-example-basic-single" name="subdistrict_id" id="subdistrict_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="checkout-label" for="zip_code">รหัสไปรษณีย์ <span class="clr">*</span></label>
                            <input class="checkout-input checkout-input--readonly" id="zip_code" type="text" name="zip_code" readonly placeholder="แสดงอัตโนมัติเมื่อเลือกตำบล">
                        </div>

                        <div class="col-12">
                            <label class="checkout-label" for="address_at">บ้านเลขที่, ซอย, หมู่, ถนน <span class="clr">*</span></label>
                            <textarea class="checkout-textarea" id="address_at" name="address_at" rows="3" placeholder="กรอกรายละเอียดที่อยู่จัดส่ง"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer checkout-modal-footer">
                <button type="button" class="checkout-modal-cancel" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="checkout-modal-save" id="btn_submit_address">บันทึกที่อยู่</button>
            </div>
        </div>
    </div>
</div>
