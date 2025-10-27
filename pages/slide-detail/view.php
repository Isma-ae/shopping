<?php
    $dsobj = $DB->QueryObj("SELECT * FROM slide WHERE slide_id = '".$_GET["slide_id"]."'");
?>
<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
    <a href="./" class="stext-109 cl8 hov-cl1 trans-04">
        หน้าแรก
        <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
    </a>

    <span class="stext-109 cl4">
        <?php echo $dsobj[0]["slide_name"];?>
    </span>
</div>
<section class="bg0 p-t-75 p-b-120">
    <div class="container">
        <div class="row p-b-148">
            <div class="order-md-2 col-md-7 col-lg-8 p-b-30">
                <div class="p-t-7 p-l-85 p-l-15-lg p-l-0-md">
                    <h3 class="mtext-111 cl2 p-b-16">
                        <?php echo $dsobj[0]["slide_name"];?>
                    </h3>

                    <p class="stext-113 cl6 p-b-26">
                        <?php echo $dsobj[0]["slide_detail"];?>
                    </p>
                </div>
            </div>

            <div class="order-md-1 col-11 col-md-5 col-lg-4 m-lr-auto p-b-30">
                <div class="how-bor2">
                    <div class="hov-img0">
                        <img src="<?php echo 'file/banner/'.$dsobj[0]["slide_img"];?>" alt="IMG">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>