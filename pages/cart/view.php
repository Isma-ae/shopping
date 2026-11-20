<!-- Breadcrumb -->
<section class="cart-breadcrumb-bar">
    <div class="container">
        <nav class="cart-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <a href="?page=product">สินค้าทั้งหมด</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">ตะกร้าสินค้า</span>
        </nav>
    </div>
</section>

<!-- Main Cart Page -->
<section class="cart-page-section">
    <div class="container">
        <div class="cart-page-head">
            <div>
                <span class="oar-eyebrow">SHOPPING CART</span>
                <h1 class="oar-heading">ตะกร้าสินค้าของคุณ</h1>
            </div>
            <div class="cart-head-count d-none" id="cartHeadCount">
                ทั้งหมด <strong id="cartTotalItems">0</strong> รายการ
            </div>
        </div>

        <form class="cart-layout-form" onsubmit="return false;">
            <div class="row align-items-start">
                <!-- Left Column: Cart Items -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="cart-card">
                        <div class="cart-card-toolbar d-none" id="cartToolbar">
                            <label class="cart-check-all-label" for="checkAll">
                                <input class="form-check-input cart-checkbox" type="checkbox" id="checkAll">
                                <span class="cart-check-all-text">
                                    เลือกทั้งหมด
                                    <small id="cartSelectedHint">(เลือกแล้ว 0 รายการ)</small>
                                </span>
                            </label>

                            <button type="button" class="cart-clear-btn" id="empty-basket">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                <span>ล้างตะกร้า</span>
                            </button>
                        </div>

                        <div class="cart_data cart-list" id="cartItemList">
                            <div class="cart-loading">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                                <span>กำลังโหลดรายการสินค้าในตะกร้า...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Order Summary -->
                <div class="col-lg-4">
                    <aside class="cart-summary-card">
                        <h3 class="cart-summary-title">สรุปคำสั่งซื้อ</h3>

                        <div class="cart-summary-rows">
                            <div class="cart-summary-row">
                                <span>รายการที่เลือก</span>
                                <strong id="cartSummarySelected">0 รายการ (0 ชิ้น)</strong>
                            </div>
                            <div class="cart-summary-row">
                                <span>ยอดรวมสินค้า</span>
                                <strong id="cartSubtotal">฿0</strong>
                            </div>
                            <div class="cart-summary-row cart-summary-row--note">
                                <span>ค่าจัดส่ง</span>
                                <span>คำนวณในขั้นตอนถัดไป</span>
                            </div>
                        </div>

                        <div class="cart-summary-divider"></div>

                        <div class="cart-summary-total">
                            <span>รวมทั้งหมด</span>
                            <strong class="cart-total">฿0</strong>
                        </div>

                        <button type="button" class="cart-btn-checkout" id="btn-checkout">
                            <span>ดำเนินการชำระเงิน</span>
                            <i class="fa fa-long-arrow-right ml-2" aria-hidden="true"></i>
                        </button>

                        <button type="button" class="cart-btn-continue" id="continue-shopping">
                            <i class="fa fa-angle-left mr-2" aria-hidden="true"></i>
                            <span>กลับไปเลือกซื้อสินค้าเพิ่ม</span>
                        </button>

                        <div class="cart-service-notes">
                            <div class="cart-service-note">
                                <i class="fa fa-university" aria-hidden="true"></i>
                                <span>รับหน้าร้านฟรีที่เคาน์เตอร์สำนักวิทยบริการ</span>
                            </div>
                            <div class="cart-service-note">
                                <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                <span>จัดส่งฟรีภายในวิทยาเขต ม.อ.ปัตตานี</span>
                            </div>
                            <div class="cart-service-note">
                                <i class="fa fa-qrcode" aria-hidden="true"></i>
                                <span>รองรับชำระเงินผ่าน Thai QR PromptPay</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </form>
    </div>
</section>
