<?php
if ($PAGE != 'home') {
    $header = ' class="header-v4"';
    $wrap = ' how-shadow1';
} else {
    $header = '';
    $wrap = '';
}

?>
<header<?=$header;?>>
    <!-- Header desktop -->
    <div class="container-menu-desktop">
        <!-- Topbar -->
        <div class="top-bar">
            <div class="content-topbar flex-sb-m h-full container">
                <div class="left-top-bar">
                </div>

                <div class="right-top-bar flex-w h-full">
                    <?php
                        // if (!isset($_SESSION["user_id"])) {
                        if (!isset($_SESSION["user_info"])) {
                    ?>
                    <a href="login/" class="flex-c-m trans-04 p-lr-25">
                        <b class="cl0 hov-cl1">เข้าสู่ระบบ</b>
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
                    <a href="#" class="flex-c-m trans-04 p-lr-25" id="profileDesk">
                        <b class="cl0 hov-cl1"><?=htmlspecialchars($currentUser['name'])?></b>
                    </a>
                    <?php
                        if (htmlspecialchars($currentUser['role']) == 'admin') {
                    ?>
                    <a href="./admin/" class="flex-c-m trans-04 p-lr-25">
                        <b class="cl0 hov-cl1">ผู้ดูแลระบบ</b>
                    </a>
                    <?php
                        }
                    ?>
                    <a href="?page=shipping_list" class="flex-c-m trans-04 p-lr-25">
                        <b class="cl0 hov-cl1">รายการสั่งซื้อ</b>
                    </a>
                    <a href="#" class="flex-c-m trans-04 p-lr-25" id="logout">
                        <b class="cl0 hov-cl1">ออกจากระบบ</b>
                    </a>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="wrap-menu-desktop<?=$wrap;?>">
            <nav class="limiter-menu-desktop container">

                <!-- Logo desktop -->
                <a href="./" class="logo">
                    <img src="images/icons/oar-shopping.png" alt="IMG-LOGO">
                </a>

                <!-- Menu desktop -->
                <div class="menu-desktop">
                    <ul class="main-menu">
                        <li>
                            <a href="./"><b>หน้าแรก</b></a>
                        </li>

                        <li>
                            <a href="?page=product"><b>สินค้า</b></a>
                        </li>
                        <li>
                            <a href="?page=contact"><b>ติดต่อ</b></a>
                        </li>
                    </ul>
                </div>

                <!-- Icon header -->
                <div class="wrap-icon-header flex-w flex-r-m">
                    <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
                        <i class="zmdi zmdi-search"></i>
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
    <div class="wrap-header-mobile">
        <!-- Logo moblie -->
        <div class="logo-mobile">
            <a href="./"><img src="images/icons/oar-shopping.png" alt="IMG-LOGO"></a>
        </div>

        <!-- Icon header -->
        <div class="wrap-icon-header flex-w flex-r-m m-r-15">
            <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
                <i class="zmdi zmdi-search"></i>
            </div>

            <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart"
                data-notify="2">
                <i class="zmdi zmdi-shopping-cart"></i>
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
        <ul class="topbar-mobile">
            <li>
                <div class="left-top-bar">
                    <?php
                        if (isset($_SESSION["user_info"])) {
                    ?>

                    <a href="#" class="flex-c-m p-lr-10 trans-04">
                        <b class="cl0 hov-cl1"><?=htmlspecialchars($currentUser['name'])?></b>
                    </a>
                    <?php
                           
                    }
                    ?>
                </div>
            </li>

            <li>
                <div class="right-top-bar flex-w h-full">
                    <?php
                    // if (!isset($_SESSION["user_id"])) {
                    if (!isset($_SESSION["user_info"])) {
                    ?>
                    <a href="login/" class="flex-c-m p-lr-10 trans-04">
                        <b class="cl0 hov-cl1">เข้าสู่ระบบ</b>
                    </a>
                    <?php
                    } else {
                        if (htmlspecialchars($currentUser['role']) == 'admin') {
                    ?>
                    <a href="./admin/" class="flex-c-m p-lr-10 trans-04">
                        <b class="cl0 hov-cl1">ผู้ดูแลระบบ</b>
                    </a>
                    <?php
                        }
                    ?>
                    <a href="?page=shipping_list" class="flex-c-m p-lr-10 trans-04">
                        <b class="cl0 hov-cl1">รายการสั่งซื้อ</b>
                    </a>
                    <a href="#" class="flex-c-m p-lr-10 trans-04" id="logout">
                        <b class="cl0 hov-cl1">ออกจากระบบ</b>
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

    <!-- Modal Search -->
    <div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
        <div class="container-search-header">
            <button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
                <img src="images/icons/icon-close2.png" alt="CLOSE">
            </button>

            <form class="wrap-search-header flex-w p-l-15">
                <button class="flex-c-m trans-04">
                    <i class="zmdi zmdi-search"></i>
                </button>
                <input class="plh3" type="text" name="search" placeholder="ค้นหา...">
            </form>
        </div>
    </div>
    </header>