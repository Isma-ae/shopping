<?php
    include_once("../config/all.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>payment</title>
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
    <link rel="stylesheet" type="text/css" href="style.css">

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
    </header>

    <section class="my-bg p-t-50 p-b-116">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="thatqrpayment-box">
                        <div class="thaiqrpayment">
                            <img src="../images/icons/Thai_QR_Logo.png" alt="Thai QR Payment" height="100%">
                        </div>
                        <!--<div class="qr-overlay" id="qr-overlay">QR Code หมดอายุแล้ว</div>-->
                        <div class="qr-box hov-img0">
                            <?php
                            $sql = "SELECT total_price FROM orders WHERE order_no = '".$_GET["order_no"]."'";
                            $obj = $DB->QueryObj($sql);
                            $requestUId = "U-00001";
                            function generateRefSecure($prefix = "R") {
                                return $prefix . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
                            }

                            $ref1 = generateRefSecure("PAY");
                            $ref2 = generateRefSecure("CHK");
                            $ref3 = "OAR00013";
                            $res = $pay->CreateQR30($requestUId, $obj[0]['total_price'], $_GET["order_no"], $_GET["order_no"], $ref3);
                            echo '<input type="hidden" id="order_no" value="'.$_GET["order_no"].'">';
                            echo '<input type="hidden" id="ref1" value="'.$ref1.'">';
                            echo '<input type="hidden" id="ref2" value="'.$ref2.'">';
                            echo '<img width="100%" src="data:image/jpeg;base64, '.$res["data"]["qrImage"].'" alt="QR Code" class="qrcode" id="qr-image">';
                        ?>
                        </div>
                    </div>
                    <div class="countdown-box mb-3 cl2">
                        ชำระเงินภายใน <span id="countdown"></span> นาที
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="info-title mtext-112">
                        ระบบ OAR Shopping
                    </div>
                    <div class="row">
                        <div class="col pe-2">
                            <div class="info-item">
                                <div class="info-label cl2">เลขอ้างอิง 1</div>
                                <div class="info-value cl1"><?=$_GET["order_no"]?> </div>
                            </div>
                        </div>
                        <div class="col ps-2">
                            <div class="info-item">
                                <div class="info-label cl2">เลขอ้างอิง 2</div>
                                <div class="info-value cl1"><?=$_GET["order_no"]?> </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col pe-2">
                            <div class="info-item">
                                <div class="info-label cl2">เลขอ้างอิง 3</div>
                                <div class="info-value cl1">
                                    <?=$ref3?> </div>
                            </div>
                        </div>
                        <div class="col ps-2">
                            <div class="info-item">
                                <div class="info-label cl2">ยอดเงิน</div>
                                <div class="info-value cl1"><?=$obj[0]['total_price']?> </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="info-item">
                                <div class="info-label cl2">
                                    เลขที่ใบสั่งซื้อ </div>
                                <div class="info-value cl1">
                                    <?=$_GET["order_no"]?> </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="info-item">
                                <div class="info-label cl2">บัญชีปลายทาง (Biller ID : 099400058086016 )</div>
                                <div class="info-value cl1">สำนักวิทยบริการ มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="warn-text">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        ชำระเงินผ่านการสแกน QR Code เท่านั้น
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <div class="after-pay-message">
                    หากชำระเงินแล้ว กรุณารอสักครู่ ระบบกำลังตรวจสอบการชำระเงินของท่านภายใน 1 นาที
                </div>
                <div class="after-pay-link">
                    หากไม่ขึ้นว่าชำระเงินแล้ว <a href="../?page=evidence&order_no=<?=$_GET["order_no"]?>"
                        target="_blank">คลิกที่นี่เพื่อส่งหลักฐาน</a>
                </div>
                <div class="flex-c-m flex-w w-full p-t-20">
                    <a href="../?page=checkout"
                        class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04">
                        <i class="fa fa-arrow-left me-1"></i> ย้อนกลับ
                    </a>
                </div>
            </div>
        </div>
    </section>
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

</html>