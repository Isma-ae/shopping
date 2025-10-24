<?php
    $dsobj = $DB->QueryObj("SELECT * FROM slide WHERE slide_id = '".$_GET["slide_id"]."'");
?>
<section class="bg1 txt-center p-lr-15 p-tb-92">
    <h2 class="ltext-105 cl0 txt-center">
        <?php echo $dsobj[0]["slide_name"];?>
    </h2>
</section>
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
                        <img src="<?php echo 'file/slide/'.$dsobj[0]["slide_img"];?>" alt="IMG">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>