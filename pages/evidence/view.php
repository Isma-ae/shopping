<?php
    if (!isset($_GET["order_no"])) {
        $order_no = "";
    } else {
        $order_no = $_GET["order_no"];
    }
?>

<!-- Content page -->
<section class="bg0 p-t-20 p-b-116">
    <div class="container">
        <div class="p-lr-200">
            <div class="bor10 p-lr-70 p-t-55 p-b-70 p-lr-15-lg">
                <form>
                    <h4 class="mtext-105 cl2 txt-center p-b-30">
                        ส่งหลักฐานการชำระเงินของคุณ
                    </h4>
                    <input type="hidden" name="order_no" value="<?=$order_no;?>">
                    <div class="bor8 m-b-20 how-pos4-parent">
                        <input class="stext-111 cl2 plh3 size-116 p-t-12 p-l-30 p-r-30" type="file" name="evidence_slip"
                            placeholder="Your Email Address">
                    </div>
                    <button type="submit"
                        class="flex-c-m stext-101 cl0 size-121 bg3 bor1 hov-btn3 p-lr-15 trans-04 pointer">
                        ส่ง
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>