<?php
    include_once("../config/all.php");
    if (isset($_SESSION["user_info"])) {
        header("Location: ../");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="../images/icons/favicon.png" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../fonts/iconic/css/material-design-iconic-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../fonts/linearicons-v1.0.0/icon-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/animsition/css/animsition.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../vendor/perfect-scrollbar/perfect-scrollbar.css">
    <!--===============================================================================================-->
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
                        <img src="../images/icons/S__8732697.png" alt="IMG-LOGO">
                    </a>
                </nav>
            </div>
        </div>
        <div class="wrap-header-mobile">
            <div class="logo-mobile">
                <a href="../"><img src="../images/icons/S__8732697.png" alt="IMG-LOGO"></a>
            </div>
        </div>
    </header>

    <section class="bg1 p-t-104 p-b-116">
        <div class="container">
            <div class="flex-w flex-tr">
                <div class="p-t-55 p-r-30">
                    <img src="../images/icons/logo_login.png" style="width:100%">
                </div>

                <div class="size-210 bor10 p-lr-70 p-t-55 p-b-70 p-lr-15-lg w-full-md bg0">
                    <form>
                        <h4 class="mtext-105 cl2 txt-center p-b-30">
                            เข้าสู่ระบบ
                        </h4>

                        <div class="bor8 m-b-20 how-pos4-parent">
                            <input class="stext-111 cl2 plh3 size-116 p-l-62 p-r-30" type="text" id="user_name"
                                placeholder="บัญชีผู้ใช้หรืออีเมล">
                            <img class="how-pos4 pointer-none" src="../images/icons/envelope.png" alt="ICON">
                        </div>

                        <div class="bor8 m-b-20 how-pos4-parent my-pass-div">
                            <input class="stext-111 cl2 plh3 size-116 p-l-62 p-r-30 my-pass-input" type="password"
                                id="user_password" name="email" placeholder="รหัสผ่าน">
                            <img class="how-pos4 my-password" src="../images/icons/eye-crossed.png" alt="ICON">
                        </div>

                        <button type="button"
                            class="flex-c-m stext-101 cl0 size-121 bg3 bor1 hov-btn3 p-lr-15 trans-04 pointer"
                            id="login">
                            เข้าสู่ระบบ
                        </button>
                        <div class="p-t-18">
                            <a href="?page=forgot-password" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                ลืมรหัสผ่าน
                            </a>
                        </div>
                        <div class="p-t-9">
                            <p class="txt-center">หรือ</p>
                        </div>
                        <div class="p-t-18 row">
                            <div class="col-md-6 p-b-5">
                                <a href="../sso-authen/public/login.php"
                                    class="flex-c-m stext-103 cl1 bor13 size-121 bg0 bor2 p-lr-15 trans-04">
                                    <img class="p-lr-5" src="../images/icons/logo-mini.png" alt="ICON"
                                        style="height:80%">
                                    Paspport
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="../sso-authen/public/login_google.php"
                                    class="flex-c-m stext-103 cl2 bor13 size-121 bg0 bor2 p-lr-15 trans-04">
                                    <img class="p-lr-5" src="../images/icons/search.png" alt="ICON">
                                    Google
                                </a>
                            </div>
                        </div>
                        <div class="p-t-18">
                            <p>หรือสมัครสมาชิก
                                <a href="../register/" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                    คลิ๊กที่นี่
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php
        include_once("../master/footer.php");
    ?>

    <script src="../vendor/animsition/js/animsition.min.js"></script>
    <!--===============================================================================================-->
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="../vendor/select2/select2.min.js"></script>
    <script>
    $(".js-select2").each(function() {
        $(this).select2({
            minimumResultsForSearch: 20,
            dropdownParent: $(this).next('.dropDownSelect2')
        });
    })
    </script>
    <!--===============================================================================================-->
    <script src="../vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
    <!--===============================================================================================-->
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