<?php
if ($PAGE != 'home') {
    $header = ' class="header-v4"';
    $wrap = ' how-shadow1';
} else {
    $header = '';
    $wrap = '';
}

?>
<header<?= $header; ?>>
    <!-- Header desktop -->
    <div class="container-menu-desktop">
        <!-- Topbar -->
        <div class="top-bar oar-topbar">
            <div class="content-topbar flex-sb-m h-full container">
                <div class="left-top-bar oar-topbar-left">
                    <span class="oar-topbar-brand">
                        <i class="fa fa-university" aria-hidden="true"></i>
                        สำนักวิทยบริการ ม.อ.ปัตตานี
                    </span>
                    <span class="oar-topbar-sep" aria-hidden="true">&middot;</span>
                    <span class="oar-topbar-note">รับหน้าร้านฟรี &amp; ส่งฟรีในวิทยาเขต</span>
                </div>

                <div class="right-top-bar oar-topbar-right flex-w h-full">
                    <?php
                    // if (!isset($_SESSION["user_id"])) {
                    if (!isset($_SESSION["user_info"])) {
                        ?>
                        <a href="?page=search-order" class="oar-topbar-link">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <span>ค้นหาจากเลขที่ใบสั่งซื้อ</span>
                        </a>
                        <a href="login/" class="oar-topbar-cta">
                            <i class="fa fa-sign-in" aria-hidden="true"></i>
                            <span>เข้าสู่ระบบ</span>
                        </a>
                        <?php
                    } else {
                        /*  ข้อมูลผู้ใช้
                                echo htmlspecialchars($currentUser['id']); 
                                echo htmlspecialchars($currentUser['name']);
                                echo htmlspecialchars($currentUser['email']); 
                                echo htmlspecialchars($currentUser['role']);
                                echo htmlspecialchars($currentUser['user_phone']);

                        */
                        ?>
                        <a href="?page=profile" class="oar-topbar-link oar-topbar-user" id="profileDesk">
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($currentUser['name']) ?></span>
                        </a>
                        <?php
                        if (htmlspecialchars($currentUser['role']) == 'admin') {
                            ?>
                            <a href="./admin/" class="oar-topbar-badge">
                                <i class="fa fa-cog" aria-hidden="true"></i>
                                <span>ผู้ดูแลระบบ</span>
                            </a>
                            <?php
                        }
                        ?>
                        <a href="?page=shipping_list" class="oar-topbar-link">
                            <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                            <span>รายการสั่งซื้อ</span>
                        </a>
                        <a href="#" class="oar-topbar-link oar-topbar-logout" id="logout">
                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                            <span>ออกจากระบบ</span>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="wrap-menu-desktop<?= $wrap; ?>">
            <nav class="limiter-menu-desktop container">

                <!-- Logo desktop -->
                <a href="./" class="logo">
                    <img src="images/icons/S__8732697.png" alt="IMG-LOGO">
                </a>

                <!-- Menu desktop -->
                <div class="menu-desktop">
                    <ul class="main-menu">
                        <li>
                            <a href="./"
                                style="text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, -1px  1px 0 #fff, 1px  1px 0 #fff;"><b>หน้าแรก</b></a>
                        </li>

                        <li>
                            <a href="?page=product"
                                style="text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, -1px  1px 0 #fff, 1px  1px 0 #fff;"><b>สินค้า</b></a>
                        </li>
                        <li>
                            <a href="?page=contact"
                                style="text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, -1px  1px 0 #fff, 1px  1px 0 #fff;"><b>ติดต่อ</b></a>
                        </li>
                    </ul>
                </div>

                <!-- Icon header -->
                <div class="wrap-icon-header flex-w flex-r-m">
                    <div class="oar-inline-search">
                        <form class="oar-inline-search-form" method="get" action="./">
                            <input type="hidden" name="page" value="product">
                            <input type="text" name="search" class="oar-inline-search-input" placeholder="ค้นหาสินค้า..." autocomplete="off">
                            <button type="button" class="oar-inline-search-cancel js-oar-search-close" aria-label="ยกเลิกการค้นหา" title="ยกเลิก">
                                <i class="zmdi zmdi-close" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="oar-inline-search-trigger js-oar-search-toggle" aria-label="ค้นหาสินค้า" title="ค้นหาสินค้า">
                                <i class="zmdi zmdi-search" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>

                    <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart"
                        data-notify="0">
                        <a href="?page=cart"><i class="zmdi zmdi-shopping-cart"></i></a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <!-- Header Mobile -->
    <div class="wrap-menu-mobile">
        <div class="wrap-header-mobile">
            <!-- Logo moblie -->
            <div class="logo-mobile">
                <a href="./"><img src="images/icons/S__8732697.png" alt="IMG-LOGO"></a>
            </div>

            <!-- Icon header -->
            <div class="wrap-icon-header flex-w flex-r-m m-r-15">
                <div class="oar-inline-search oar-inline-search--mobile">
                    <form class="oar-inline-search-form" method="get" action="./">
                        <input type="hidden" name="page" value="product">
                        <input type="text" name="search" class="oar-inline-search-input" placeholder="ค้นหาสินค้า..." autocomplete="off">
                        <button type="button" class="oar-inline-search-cancel js-oar-search-close" aria-label="ยกเลิกการค้นหา" title="ยกเลิก">
                            <i class="zmdi zmdi-close" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="oar-inline-search-trigger js-oar-search-toggle" aria-label="ค้นหาสินค้า" title="ค้นหาสินค้า">
                            <i class="zmdi zmdi-search" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>

                <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart"
                    data-notify="2">
                    <a href="?page=cart"><i class="zmdi zmdi-shopping-cart"></i></a>
                </div>
            </div>

            <!-- Button show menu -->
            <div class="btn-show-menu-mobile hamburger hamburger--squeeze">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </div>
        </div>


        <!-- Menu Mobile -->
        <div class="menu-mobile">
            <ul class="topbar-mobile oar-topbar-mobile">
                <?php if (isset($_SESSION["user_info"])) { ?>
                    <li class="oar-topbar-mobile-user">
                        <div class="left-top-bar">
                            <a href="?page=profile" class="oar-topbar-m-user">
                                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                                <span><?= htmlspecialchars($currentUser['name']) ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>

                <li>
                    <div class="right-top-bar oar-topbar-m-actions flex-w h-full">
                        <?php
                        // if (!isset($_SESSION["user_id"])) {
                        if (!isset($_SESSION["user_info"])) {
                            ?>
                            <a href="?page=search-order" class="oar-topbar-m-link">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <span>ค้นหาจากเลขที่ใบสั่งซื้อ</span>
                            </a>
                            <a href="login/" class="oar-topbar-m-cta">
                                <i class="fa fa-sign-in" aria-hidden="true"></i>
                                <span>เข้าสู่ระบบ</span>
                            </a>
                            <?php
                        } else {
                            if (htmlspecialchars($currentUser['role']) == 'admin') {
                                ?>
                                <a href="./admin/" class="oar-topbar-m-badge">
                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                    <span>ผู้ดูแลระบบ</span>
                                </a>
                                <?php
                            }
                            ?>
                            <a href="?page=shipping_list" class="oar-topbar-m-link">
                                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                                <span>รายการสั่งซื้อ</span>
                            </a>
                            <a href="#" class="oar-topbar-m-link oar-topbar-m-logout" id="logout">
                                <i class="fa fa-sign-out" aria-hidden="true"></i>
                                <span>ออกจากระบบ</span>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </li>
            </ul>

            <ul class="main-menu-m">
                <li>
                    <a href="./">หน้าแรก</a>
                </li>

                <li>
                    <a href="?page=product">สินค้า</a>
                </li>

                <li>
                    <a href="?page=contact">ติดต่อ</a>
                </li>
            </ul>
        </div>
    </div>
</header>
