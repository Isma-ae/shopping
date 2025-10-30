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

<section class="sec-product-detail bg0 p-t-65 p-b-60">
    <div class="container">
        <div class="row">
            <div class="col-md-5 col-lg-6 p-b-30">
                <div class="p-l-25 p-r-30 p-lr-0-lg">
                    <div class="wrap-slick3">
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

                    <span class="mtext-106 cl1 price-data">
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
                            <?php }?>
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
                                        value="1" readonly>

                                    <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
                                        <i class="fs-16 zmdi zmdi-plus"></i>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="flex-w flex-sb-m p-t-18 p-b-15 p-lr-15-sm">
                            <div class="flex-w flex-m m-r-20 m-tb-5">
                                <button type="button" id="add-cart"
                                    class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 m-r-15 trans-04 js-addcart-detail">
                                    เพิ่มสินค้าลงตะกร้า
                                </button>

                                <div id="buy-product"
                                    class="flex-c-m stext-101 cl2 size-118 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-5">
                                    ซื้อสินค้า
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bor10 m-t-50 p-t-43 p-b-40">
            <div class="tab01">
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

                <div class="tab-content p-t-43">
                    <!-- - -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="how-pos2 p-lr-15-md">
                            <p class="stext-102 cl6">
                                <?=$obj[0]["product_detail"];?>
                            </p>
                        </div>
                    </div>

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
                                                foreach ($obj3 as $row3 ) {
                                                    $size = trim($row3['size_detail']);
                                                    if ($size !== '') {
                                                        $sizes[] = $row3['variant_size'] . ' (' . $size . ')';
                                                    } else {
                                                        $sizes[] = $row3['variant_size'];
                                                    }
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
                                    <div class="review-data m-b-20">

                                    </div>
                                    <a href="javascript:void(0)" id="load-more"
                                        class="stext-104 cl4 hov-cl1 trans-04 js-name-b2">
                                        โหลดเพิ่มเติม
                                    </a>
                                    <form class="w-full p-t-60">
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
                                                <input class="dis-none" type="number" name="review_rating">
                                            </span>
                                        </div>

                                        <div class="row p-b-25">
                                            <div class="col-12 p-b-5">
                                                <label class="stext-102 cl3" for="review_detail">รีวิวของคุณ</label>
                                                <textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10"
                                                    id="review_detail" name="review_detail"></textarea>
                                            </div>
                                        </div>

                                        <button type="button" id="insert-review"
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
</section>