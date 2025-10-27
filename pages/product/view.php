<div class="container">
    <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
        <a href="./" class="stext-109 cl8 hov-cl1 trans-04">
            หน้าแรก
            <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
        </a>

        <span class="stext-109 cl4">
            สินค้า
        </span>
    </div>
</div>

<section class="sec-relate-product bg0 p-t-105 p-b-105">
    <div class="container">

        <div class="row isotope-grid">
            <?php
                    $search = '';
                    if (!empty($_GET['search'])) {
                        $search_term = $DB->escape($_GET['search']); // ถ้ามีฟังก์ชัน escape
                        $search = " AND product_name LIKE '%{$search_term}%'";
                    }
                    
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
                            WHERE product_status = 2 $search
                            GROUP BY 
                                product_id, 
                                product_name, 
                                img_name
                            ORDER BY product.product_id DESC";
                    $obj = $DB->QueryObj($sql);
                    if(sizeof($obj)>0){
                        foreach ($obj as $key => $row) {
                ?>
            <div class="col-sm-2 col-md-4 col-lg-3 p-b-35 isotope-item">
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