<?php
    $sql = "SELECT 
                product.*,
                MIN(variants.variant_sale) AS min_price, 
                MAX(variants.variant_sale) AS max_price
            FROM product
            INNER JOIN variants 
                ON product.product_id = variants.product_id
            WHERE MD5(product.product_id) = '".$_GET["product"]."'";
    $obj = $DB->QueryObj($sql);
    if ($obj[0]["min_price"] == $obj[0]["max_price"]) {
        $product_price = '฿'.$obj[0]["min_price"];
    } else {
        $product_price = '฿'.$obj[0]["min_price"].' - ฿'.$obj[0]["max_price"];
    }
    
?>
<input type="hidden" id="product_id" value="<?=$_GET["product"];?>">
<div class="container">
    <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
        <a href="./" class="stext-109 cl8 hov-cl1 trans-04">
            หน้าแรก
            <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
        </a>

        <a href="#" class="stext-109 cl8 hov-cl1 trans-04">
            รายละเอียดสินค้า
            <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
        </a>

        <span class="stext-109 cl4">
            <?=$obj[0]["product_name"];?>
        </span>
    </div>
</div>


<!-- Product Detail -->
<section class="sec-product-detail bg0 p-t-65 p-b-60">
    <div class="container">
        <div class="row">
            <div class="col-md-5 col-lg-6 p-b-30">
                <div class="p-l-25 p-r-30 p-lr-0-lg">
                    <div class="wrap-slick3">
                        <!-- ภาพหลัก -->
                        <div class="slick3-main">
                            <?php
                                $sql4 = "SELECT *
                                        FROM img
                                        WHERE MD5(product_id) = '".$_GET["product"]."'
                                        ORDER BY img_main";
                                $obj4 = $DB->QueryObj($sql4);
                                foreach ($obj4 as $row4) {
                            ?>
                            <div class="bor10"><img src="file/product/<?=$row4["img_name"];?>" alt="IMG"
                                    data-large="file/product/<?=$row4["img_name"];?>"></div>
                            <?php }?>
                        </div>

                        <!-- thumbnail ด้านล่าง -->
                        <div class="slick3-nav">
                            <?php
                                foreach ($obj4 as $key5 => $row5) {
                            ?>
                            <div><img src="file/product/<?=$row5["img_name"];?>" alt="IMG"></div>
                            <?php }?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-7 col-lg-6 p-b-30">
                <div class="p-r-50 p-t-5 p-lr-0-lg">
                    <h4 class="mtext-105 cl2 js-name-detail p-b-14 product_name">
                        <?=$obj[0]["product_name"];?>
                    </h4>

                    <span class="mtext-106 cl2 price-data">
                        <?=$product_price;?>
                    </span>
                    <div class="p-t-33">
                        <div class="p-b-10">
                            <?php
                                $sql2 = "SELECT variant_color, img_name
                                        FROM variants
                                        LEFT JOIN img
                                                ON variants.img_id = img.img_id
                                        WHERE MD5(variants.product_id) = '".$_GET["product"]."'
                                        GROUP BY variant_color";
                                $obj2 = $DB->QueryObj($sql2);
                                if (sizeof($obj2) > 1) {
                            ?>
                            <div class="size-203 respon6 p-b-10">
                                <strong>สี</strong>
                            </div>

                            <div class="selectgroup selectgroup-pills">
                                <?php
                                foreach ($obj2 as $key => $value2) {
                                    if ($key == 0) {
                                        $checked = ' checked=""';
                                    } else {
                                        $checked = '';
                                    }
                                ?>
                                <label class="selectgroup-item">
                                    <input type="radio" name="variant_color" value="<?=$value2["variant_color"]?>"
                                        class="selectgroup-input" color-img="file/product/<?=$value2["img_name"];?>"
                                        <?=$checked;?> />
                                    <span class="selectgroup-button"><?=$value2["variant_color"]?></span>
                                </label>
                                <?php }?>
                            </div>
                            <?php } else {
                                echo '<input type="hidden" name="variant_color" value="'.$obj2[0]["variant_color"].'">';
                            }?>
                        </div>
                        <?php
                                $sql3 = "SELECT variant_size, size_detail, size_order
                                        FROM variants
                                        WHERE MD5(variants.product_id) = '".$_GET["product"]."'
                                        GROUP BY variant_size
                                        ORDER BY size_order";
                                $obj3 = $DB->QueryObj($sql3);  
                            ?>
                        <div class="p-b-10 size-data">
                        </div>

                        <div class="p-b-10">
                            <div class="size-204 flex-w flex-m respon6-next">
                                <div class="wrap-num-product flex-w m-r-20 m-tb-10">
                                    <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
                                        <i class="fs-16 zmdi zmdi-minus"></i>
                                    </div>

                                    <input class="mtext-104 cl3 txt-center num-product" type="number" name="cart_qty"
                                        value="1">

                                    <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
                                        <i class="fs-16 zmdi zmdi-plus"></i>
                                    </div>
                                </div>

                                <button type="button" id="add-cart"
                                    class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-addcart-detail">
                                    เพิ่มลงตะกร้า
                                </button>

                            </div>
                        </div>
                    </div>

                    <!--  -->
                    <!-- <div class="flex-w flex-m p-l-100 p-t-40 respon7">
                        <div class="flex-m bor9 p-r-10 m-r-11">
                            <a href="#"
                                class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 js-addwish-detail tooltip100"
                                data-tooltip="Add to Wishlist">
                                <i class="zmdi zmdi-favorite"></i>
                            </a>
                        </div>

                        <a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
                            data-tooltip="Facebook">
                            <i class="fa fa-facebook"></i>
                        </a>

                        <a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
                            data-tooltip="Twitter">
                            <i class="fa fa-twitter"></i>
                        </a>

                        <a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
                            data-tooltip="Google Plus">
                            <i class="fa fa-google-plus"></i>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>

        <div class="bor10 m-t-50 p-t-43 p-b-40">
            <!-- Tab01 -->
            <div class="tab01">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item p-b-10">
                        <a class="nav-link active" data-toggle="tab" href="#description" role="tab">รายละเอียด</a>
                    </li>

                    <li class="nav-item p-b-10">
                        <a class="nav-link" data-toggle="tab" href="#information" role="tab">ข้อมูลเพิ่มเติม</a>
                    </li>

                    <li class="nav-item p-b-10">
                        <a class="nav-link" data-toggle="tab" href="#reviews" role="tab">รีวิว</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-t-43">
                    <!-- - -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="how-pos2 p-lr-15-md">
                            <p class="stext-102 cl6">
                                <?=$obj[0]["product_detail"];?>
                            </p>
                        </div>
                    </div>

                    <!-- - -->
                    <div class="tab-pane fade" id="information" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
                                <ul class="p-lr-28 p-lr-15-sm">

                                    <li class="flex-w flex-t p-b-7">
                                        <span class="stext-102 cl3 size-205">
                                            สี
                                        </span>

                                        <span class="stext-102 cl6 size-206">
                                            <?php
                                                $colors = [];
                                                foreach ($obj2 as $row2) {
                                                    $colors[] = $row2['variant_color'];
                                                }
                                                echo implode(', ', $colors);
                                            ?>
                                        </span>
                                    </li>

                                    <li class="flex-w flex-t p-b-7">
                                        <span class="stext-102 cl3 size-205">
                                            ขนาด
                                        </span>

                                        <span class="stext-102 cl6 size-206">
                                            <?php
                                                $sizes = [];
                                                foreach ($obj3 as $row3) {
                                                    $sizes[] = $row3['variant_size'].' ('.$row3['size_detail'].')';
                                                }
                                                echo implode(', ', $sizes);
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- - -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
                                <div class="p-b-30 m-lr-15-sm">
                                    <!-- Review -->
                                    <!-- <div class="flex-w flex-t p-b-68">
                                        <div class="wrap-pic-s size-109 bor0 of-hidden m-r-18 m-t-6">
                                            <img src="images/avatar-01.jpg" alt="AVATAR">
                                        </div>

                                        <div class="size-207">
                                            <div class="flex-w flex-sb-m p-b-17">
                                                <span class="mtext-107 cl2 p-r-20">
                                                    Ariana Grande
                                                </span>

                                                <span class="fs-18 cl11">
                                                    <i class="zmdi zmdi-star"></i>
                                                    <i class="zmdi zmdi-star"></i>
                                                    <i class="zmdi zmdi-star"></i>
                                                    <i class="zmdi zmdi-star"></i>
                                                    <i class="zmdi zmdi-star-half"></i>
                                                </span>
                                            </div>

                                            <p class="stext-102 cl6">
                                                Quod autem in homine praestantissimum atque optimum est, id
                                                deseruit. Apud ceteros autem philosophos
                                            </p>
                                        </div>
                                    </div> -->

                                    <!-- Add review -->
                                    <form class="w-full">
                                        <h5 class="mtext-108 cl2 p-b-7">
                                            เพิ่มรีวิว
                                        </h5>

                                        <div class="flex-w flex-m p-t-50 p-b-23">
                                            <span class="stext-102 cl3 m-r-16">
                                                คะแนนของคุณ
                                            </span>

                                            <span class="wrap-rating fs-18 cl11 pointer">
                                                <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                                <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                                <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                                <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                                <i class="item-rating pointer zmdi zmdi-star-outline"></i>
                                                <input class="dis-none" type="number" name="rating">
                                            </span>
                                        </div>

                                        <div class="row p-b-25">
                                            <div class="col-12 p-b-5">
                                                <label class="stext-102 cl3" for="review">รีวิวของคุณ</label>
                                                <textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10"
                                                    id="review" name="review"></textarea>
                                            </div>
                                        </div>

                                        <button
                                            class="flex-c-m stext-101 cl0 size-112 bg7 bor11 hov-btn3 p-lr-15 trans-04 m-b-10">
                                            ส่ง
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--<div class="bg6 flex-c-m flex-w size-302 m-t-73 p-tb-15">
        <span class="stext-107 cl6 p-lr-25">
            SKU: JAK-01
        </span>

        <span class="stext-107 cl6 p-lr-25">
            Categories: Jacket, Men
        </span>
    </div>-->
</section>