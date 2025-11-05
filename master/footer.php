<footer class="bg3 p-t-75 p-b-32">
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-lg-4 p-b-50">
                <h4 class="stext-301 cl0 p-b-30">
                    หมวดหมู่
                </h4>

                <ul>
                    <?php
                        $scate = "SELECT * FROM category";
                        $ocate = $DB->QueryObj($scate);
                        foreach ($ocate as $vcate) {
                    ?>
                    <li class="p-b-10">
                        <a href="?page=product" class="stext-107 cl7 hov-cl1 trans-04">
                            <?=$vcate["category_name"]?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="col-sm-6 col-lg-4 p-b-50">
                <h4 class="stext-301 cl0 p-b-30">
                    ช่วยเหลือ
                </h4>

                <ul>
                    <li class="p-b-10">
                        <a href="?page=search-order" class="stext-107 cl7 hov-cl1 trans-04">
                            ติดตามคำสั่งซื้อ
                        </a>
                    </li>

                    <li class="p-b-10">
                        <a href="?page=return" class="stext-107 cl7 hov-cl1 trans-04">
                            การส่งคืน
                        </a>
                    </li>

                    <li class="p-b-10">
                        <a href="?page=shipping" class="stext-107 cl7 hov-cl1 trans-04">
                            การส่งสินค้า
                        </a>
                    </li>

                    <!-- <li class="p-b-10">
                        <a href="#" class="stext-107 cl7 hov-cl1 trans-04">
                            คำถามที่พบบ่อย
                        </a>
                    </li> -->
                </ul>
            </div>

            <div class="col-sm-6 col-lg-4 p-b-50">
                <h4 class="stext-301 cl0 p-b-30">
                    ติดต่อเรา
                </h4>

                <p class="stext-107 cl7 size-201">
                    เลขที่ 181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล อ.เมือง จ.ปัตตานี 94000 โทรศัพท์ +66 73 33 1300 โทรสาร +66 73
                    33 3587
                </p>

                <div class="p-t-27">
                    <a href="https://www.facebook.com/OAR.PSU" target="_blank"
                        class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
                        <i class="fa fa-facebook"></i>
                    </a>

                    <a href="https://www.youtube.com/@OARChannel" target="_blank"
                        class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
                        <i class="fa fa-youtube-play"></i>
                    </a>

                    <a href="https://www.oas.psu.ac.th/" target="_blank" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
                        <i class="fa fa-google"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="p-t-10">
            <div class="flex-c-m flex-w p-b-18">
                <a href="#" class="m-all-1">
                    <img src="images/icons/Thai_QR_Logo.png" alt="ICON-PAY" style="height:23px;">
                </a>
            </div>
            <p class="stext-107 cl6 txt-center">
                Copyright &copy;<script>
                document.write(new Date().getFullYear());
                </script> All rights reserved | Made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a
                    href="https://www.oas.psu.ac.th/" target="_blank">Office Of Academic Resources</a>

            </p>
        </div>
    </div>
</footer>