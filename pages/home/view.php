<section class="section-slide">
    <div class="wrap-slick1">
        <div class="slick1">
            <?php
                $ssl="SELECT * FROM slide";
                $osl = $DB->QueryObj($ssl);
                foreach ($osl as $key => $rsl) {
                    if ($rsl["slide_link"] != "") {
                        $link = $rsl["slide_link"];
                    } elseif ($rsl["slide_detail"] != "") {
                        $link = '?page=slide-detail&slide_id='.$rsl["slide_id"].'';
                    } else {
                        $link = "#";
                    }
                    echo '<a href="'.$link.'" class="item-slick1" style="background-image: url(file/banner/'.$rsl["slide_img"].');">
                    </a>';
                }
            ?>
        </div>
    </div>
</section>
<section class="sec-relate-product bg0 p-t-105 p-b-105">
    <div class="container">
        <div class="p-b-45 txt-center">
            <h4 class="ltext-108 cl1 section-title px-5">
                <span class="px-2">สินค้ามาใหม่</span>
            </h4>
        </div>
        <div class="flex-w flex-sb-m p-t-18">
            <span class="flex-w flex-m mtext-112 cl1 p-r-30 m-tb-10">
                สินค้ามาใหม่ล่าสุด
            </span>

            <a href="?page=product" class="stext-101 cl2 hov-cl1 trans-04 m-tb-10">
                สินค้าทั้งหมด

                <i class="fa fa-long-arrow-right m-l-9"></i>
            </a>
        </div>

        <div class="row isotope-grid p-t-50">
            <?php
                $sql = "SELECT
                            MD5(product.product_id) AS product_id,
                            product_name,
                            MIN(variants.variant_sale) AS price,
                            img_name
                        FROM product
                        LEFT JOIN variants 
                            ON product.product_id = variants.product_id
                        LEFT JOIN img
                            ON product.product_id = img.product_id
                            AND img_main = '1'
                        WHERE product_status = 2
                        GROUP BY 
                            product_id, 
                            product_name, 
                            img_name
                        ORDER BY product.product_id DESC
                        LIMIT 8";
                $obj = $DB->QueryObj($sql);
                if(sizeof($obj)>0){
                    foreach ($obj as $key => $row) {
            ?>
            <div class="col-sm-2 col-md-4 col-lg-3 p-b-35 isotope-item">
                <!-- Block2 -->
                <div class="block2">
                    <div class="item-img-block">
                        <div class="block2-pic hov-img10 item-cover">
                            <a href="?page=detail&product=<?=$row["product_id"];?>" class="block2-pic hov-img0 bor10">
                                <img src="file/product/<?=$row["img_name"];?>" alt="IMG-PRODUCT">
                            </a>
                        </div>
                    </div>
                    <div class="block2-txt flex-w flex-t p-t-14">
                        <div class="block2-txt-child1 flex-col-l">
                            <a href="?page=detail&product=<?=$row["product_id"];?>"
                                class="stext-110 cl1 hov-cl1 trans-04 js-name-b2 p-b-6">
                                <b><?=$row["product_name"];?></b>
                            </a>

                            <span class="stext-105 cl3">
                                ฿<?=$row["price"];?>
                            </span>
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
</section>