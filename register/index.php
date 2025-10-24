<?php
    include_once("../config/all.php");
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/icons/favicon.png" />
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/linearicons-v1.0.0/icon-font.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="../vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" type="text/css" href="../css/util.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">

    <script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../vendor/sweetalert/sweetalert.min.js"></script>
    <script src="script.js"></script>
</head>

<body class="animsition">
    <header class="header-v2">
        <div class="container-menu-desktop trans-03">
            <div class="wrap-menu-desktop how-shadow1">
                <nav class="limiter-menu-desktop container">
                    <a href="../" class="logo">
                        <img src="../images/icons/oar-shopping.png" alt="IMG-LOGO">
                    </a>
                </nav>
            </div>
        </div>
        <div class="wrap-header-mobile">
            <div class="logo-mobile">
                <a href="../"><img src="../images/icons/oar-shopping.png" alt="IMG-LOGO"></a>
            </div>
        </div>
        <div class="menu-mobile">

            <ul class="main-menu-m">
            </ul>
        </div>
    </header>

    <section class="bg1 p-t-104 p-b-116">
        <div class="container">
            <div class="flex-w flex-tr">
                <div class="p-lr-15 p-lr-15-lg p-t-55">
                    <img src="../images/icons/OARShopping.png" style="width:100%">
                </div>

                <div class="size-210 bor10 p-lr-70 p-t-55 p-b-70 p-lr-15-lg w-full-md bg0">
                    <form>
                        <h4 class="mtext-105 cl2 txt-center p-b-30">
                            สมัครสมาชิก
                        </h4>


                        <label for="name">ชื่อ นามสกุล</label>
                        <div class="bor8 m-b-20 how-pos4-parent">
                            <input class="stext-111 cl2 plh3 size-116 p-l-30 p-r-30" type="text" id="name" name="name"
                                placeholder="กรุณากรอกชื่อ นามสกุล...">
                        </div>
                        <label for="user_id">อีเมล</label>
                        <div class="bor8 m-b-20 how-pos4-parent my-pass-div">
                            <input class="stext-111 cl2 plh3 size-116 p-l-30 p-r-30 my-pass-input" type="email"
                                id="user_id" name="user_id" placeholder="กรุณากรอกอีเมล...">
                        </div>
                        <label for="user_password">รหัสผ่าน</label>
                        <div class="bor8 m-b-20 how-pos4-parent my-pass-div">
                            <input class="stext-111 cl2 plh3 size-116 p-l-30 p-r-30 my-pass-input" type="password"
                                id="user_password" name="user_password" placeholder="กรุณากรอกรหัสผ่าน...">
                        </div>
                        <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                        <div class="bor8 m-b-20 how-pos4-parent my-pass-div">
                            <input class="stext-111 cl2 plh3 size-116 p-l-30 p-r-30 my-pass-input" type="password"
                                id="confirm_password" name="confirm_password" placeholder="กรุณายืนยันรหัสผ่าน...">
                        </div>
                        <label for="user_phone">หมายเลขโทรศัพท์</label>
                        <div class="bor8 m-b-20 how-pos4-parent my-pass-div">
                            <input class="stext-111 cl2 plh3 size-116 p-l-30 p-r-30 my-pass-input" type="text"
                                id="user_phone" name="user_phone" placeholder="กรุณากรอกหมายเลขโทรศัพท์...">
                        </div>
                        <button class="flex-c-m stext-101 cl0 size-121 bg3 bor1 hov-btn3 p-lr-15 trans-04 pointer"
                            id="register">
                            สมัครสมาชิก
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php
        include_once("../master/footer.php");
    ?>

    <script src="../vendor/animsition/js/animsition.min.js"></script>
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
    <script src="../vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script>
    $('.js-pscroll').each(function() {
        $(this).css('position', 'relative');
        $(this).css('overflow', 'hidden');
        var ps = new PerfectScrollbar(this, {
            wheelSpeed: 1,
            scrollingThreshold: 1000,
            wheelPropagation: false,
        });

        $(window).on('resize', function() {
            ps.update();
        })
    });
    </script>
    <script src="../js/main.js"></script>
</body>