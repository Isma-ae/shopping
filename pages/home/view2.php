<section id="billboard" class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <h1 class="section-title text-center mt-4" data-aos="fade-up">Welcome to OAR Shopping</h1>
            <!--<div class="col-md-6 text-center" data-aos="fade-up" data-aos-delay="300">
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe voluptas ut dolorum consequuntur,
                    adipisci
                    repellat! Eveniet commodi voluptatem voluptate, eum minima, in suscipit explicabo voluptatibus
                    harum,
                    quibusdam ex repellat eaque!</p>
            </div>-->
        </div>
    </div>
</section>
<?php
    $sql = "SELECT product.*, MIN(stock.price) AS price FROM product
            INNER JOIN stock ON product.product_id LIMIT 4";
    $obj = $DB->QueryObj($sql);
    if(sizeof($obj)>0){
?>
<section id="new-arrival" class="new-arrival product-carousel py-5 position-relative overflow-hidden">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 mb-3">
            <h4 class="text-uppercase">Our New Arrivals</h4>
            <a href="./?page=detail" class="btn-link">View All Products</a>
        </div>
        <div class="swiper product-swiper open-up" data-aos="zoom-out">
            <div class="swiper-wrapper d-flex">
                <?php
                   foreach ($obj as $key => $row) {
                ?>
                <div class="swiper-slide">
                    <div class="product-item image-zoom-effect link-effect">
                        <div class="image-holder ">
                            <a href="./?page=detail&product=<?=md5($row["product_id"]);?>">
                                <img src="file/product/product-image1.png" alt="product" class="img-fluid">
                            </a>
                        </div>
                        <div class="product-content banner-content py-4">
                            <h5 class="text-uppercase fs-5 border-animation-left">
                                <a href="./?page=detail&product=<?=md5($row["product_id"]);?>"
                                    class="item-anchor"><?=$row["product_name"];?></a>
                            </h5>
                            <p class="text-primary">฿<?=$row["price"];?></p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="product-item image-zoom-effect link-effect">
                        <div class="image-holder ">
                            <a href="./?page=detail&product=<?=md5($row["product_id"]);?>">
                                <img src="file/product/product-image1.png" alt="product" class="img-fluid">
                            </a>
                        </div>
                        <div class="product-content banner-content py-4">
                            <h5 class="text-uppercase fs-5 border-animation-left">
                                <a href="./?page=detail&product=<?=md5($row["product_id"]);?>"
                                    class="item-anchor"><?=$row["product_name"];?></a>
                            </h5>
                            <p class="text-primary">฿<?=$row["price"];?></p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="product-item image-zoom-effect link-effect">
                        <div class="image-holder ">
                            <a href="./?page=detail&product=<?=md5($row["product_id"]);?>">
                                <img src="file/product/product-image1.png" alt="product" class="img-fluid">
                            </a>
                        </div>
                        <div class="product-content banner-content py-4">
                            <h5 class="text-uppercase fs-5 border-animation-left">
                                <a href="./?page=detail&product=<?=md5($row["product_id"]);?>"
                                    class="item-anchor"><?=$row["product_name"];?></a>
                            </h5>
                            <p class="text-primary">฿<?=$row["price"];?></p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="product-item image-zoom-effect link-effect">
                        <div class="image-holder ">
                            <a href="./?page=detail&product=<?=md5($row["product_id"]);?>">
                                <img src="file/product/product-image1.png" alt="product" class="img-fluid">
                            </a>
                        </div>
                        <div class="product-content banner-content py-4">
                            <h5 class="text-uppercase fs-5 border-animation-left">
                                <a href="./?page=detail&product=<?=md5($row["product_id"]);?>"
                                    class="item-anchor"><?=$row["product_name"];?></a>
                            </h5>
                            <p class="text-primary">฿<?=$row["price"];?></p>
                        </div>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>
    </div>
</section>
<?php }?>