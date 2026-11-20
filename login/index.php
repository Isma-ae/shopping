<?php
include_once("../config/all.php");
if (isset($_SESSION["user_info"])) {
    header("Location: ../");
}

$lineProviderCfg = file_exists("../sso-authen/config/providers/line.php")
    ? require("../sso-authen/config/providers/line.php")
    : [];
$facebookProviderCfg = file_exists("../sso-authen/config/providers/facebook.php")
    ? require("../sso-authen/config/providers/facebook.php")
    : [];
$showLineLogin = !empty($lineProviderCfg['show_button']);
$showFacebookLogin = !empty($facebookProviderCfg['show_button']);
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
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/util.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/main.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../style.css'); ?>">

    <script src="../vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="../vendor/sweetalert/sweetalert.min.js"></script>
    <script src="<?= assetVer('script.js'); ?>"></script>
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
                        <div class="p-t-18 row oar-social-login-grid">
                            <div class="col-sm-6 p-b-10">
                                <a href="../sso-authen/public/login.php"
                                    class="flex-c-m stext-103 cl1 bor13 size-121 bg0 bor2 p-lr-15 trans-04 oar-social-btn">
                                    <img class="p-lr-5" src="../images/icons/logo-mini.png" alt="ICON"
                                        style="height:80%">
                                    Passport
                                </a>
                            </div>
                            <div class="col-sm-6 p-b-10">
                                <a href="../sso-authen/public/login_google.php"
                                    class="flex-c-m stext-103 cl2 bor13 size-121 bg0 bor2 p-lr-15 trans-04 oar-social-btn">
                                    <img class="p-lr-5" src="../images/icons/search.png" alt="ICON">
                                    Google
                                </a>
                            </div>
                            <!-- <?php if ($showLineLogin) { ?>
                            <div class="col-sm-6 p-b-10">
                                <a href="../sso-authen/public/login_line.php"
                                    class="flex-c-m stext-103 bor13 size-121 p-lr-15 trans-04 oar-social-btn oar-social-btn--line">
                                    <svg class="oar-social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="currentColor"
                                            d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                                    </svg>
                                    LINE
                                </a>
                            </div>
                            <?php } ?> -->
                            <!-- <?php if ($showFacebookLogin) { ?>
                            <div class="col-sm-6 p-b-10">
                                <a href="../sso-authen/public/login_facebook.php"
                                    class="flex-c-m stext-103 bor13 size-121 p-lr-15 trans-04 oar-social-btn oar-social-btn--facebook">
                                    <svg class="oar-social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="currentColor"
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                    Facebook
                                </a>
                            </div>
                            <?php } ?> -->
                        </div>
                        <div class="p-t-18">
                            <p>หรือสมัครสมาชิก
                                <a href="../register/" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                    คลิกที่นี่
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
        $(".js-select2").each(function () {
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
        $('.js-pscroll').each(function () {
            $(this).css('position', 'relative');
            $(this).css('overflow', 'hidden');
            var ps = new PerfectScrollbar(this, {
                wheelSpeed: 1,
                scrollingThreshold: 1000,
                wheelPropagation: false,
            });

            $(window).on('resize', function () {
                ps.update();
            })
        });
    </script>
    <script src="<?= assetVer('../js/main.js'); ?>"></script>
</body>