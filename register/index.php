<?php
include_once("../config/all.php");
if (isset($_SESSION["user_info"])) {
    header("Location: ../");
    exit();
}

$lineProviderCfg = file_exists("../sso-authen/config/providers/line.php")
    ? require("../sso-authen/config/providers/line.php")
    : [];
$showLineLogin = !empty($lineProviderCfg['show_button']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <title>สมัครสมาชิก | OAR Shopping</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/icons/favicon.png" />
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/util.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../css/main.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../style.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('../login/style.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetVer('style.css'); ?>">

    <script src="../vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="../vendor/sweetalert/sweetalert.min.js"></script>
    <script src="<?= assetVer('script.js'); ?>"></script>
</head>

<body class="animsition oar-login-body">
    <!-- Clean Top Navigation Bar -->
    <header class="oar-login-topbar">
        <div class="container">
            <div class="oar-login-topbar-inner">
                <a href="../" class="oar-login-logo" aria-label="OAR Shopping หน้าแรก">
                    <img src="../images/icons/S__8732697.png" alt="OAR Shopping">
                </a>
                <a href="../" class="oar-login-back-btn">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    <span>กลับหน้าร้านค้า</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Auth Section -->
    <main class="oar-login-main">
        <div class="container">
            <div class="oar-login-card">
                <div class="row no-gutters align-items-stretch">
                    <!-- Left Brand Panel -->
                    <div class="col-lg-5 oar-login-brand">
                        <div class="oar-login-brand-orb oar-login-brand-orb--1" aria-hidden="true"></div>
                        <div class="oar-login-brand-orb oar-login-brand-orb--2" aria-hidden="true"></div>

                        <div class="oar-login-brand-content">
                            <span class="oar-login-brand-pill">
                                <i class="fa fa-university" aria-hidden="true"></i>
                                สำนักวิทยบริการ ม.อ.ปัตตานี
                            </span>

                            <div class="oar-login-visual-wrap">
                                <img src="../images/icons/logo_login.png" alt="OAR Shopping" class="oar-login-visual">
                            </div>

                            <h2 class="oar-login-brand-title">เริ่มต้นช้อปกับ OAR Shopping</h2>
                            <p class="oar-login-brand-desc">
                                สมัครสมาชิกเพื่อสั่งซื้อสินค้าที่ระลึกและของใช้ประจำสำนักวิทยบริการ
                                บันทึกที่อยู่จัดส่ง และติดตามคำสั่งซื้อได้ทุกขั้นตอน
                            </p>

                            <ul class="oar-login-perks">
                                <li>
                                    <span class="oar-perk-icon"><i class="fa fa-truck" aria-hidden="true"></i></span>
                                    <span>รับหน้าร้านฟรี &amp; ส่งฟรีภายในวิทยาเขต</span>
                                </li>
                                <li>
                                    <span class="oar-perk-icon"><i class="fa fa-qrcode" aria-hidden="true"></i></span>
                                    <span>ชำระเงินสะดวกด้วย Thai QR PromptPay</span>
                                </li>
                                <li>
                                    <span class="oar-perk-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                                    <span>ติดตามสถานะคำสั่งซื้อและใบเสร็จออนไลน์</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right Register Form Panel -->
                    <div class="col-lg-7 oar-login-form-col">
                        <div class="oar-login-form-inner oar-register-form-inner">
                            <div class="oar-login-head">
                                <span class="oar-eyebrow">CREATE ACCOUNT</span>
                                <h1 class="oar-login-title">สมัครสมาชิก</h1>
                                <p class="oar-login-subtitle mb-0">
                                    กรอกข้อมูลด้านล่างเพื่อสร้างบัญชีใหม่ หรือเลือกเข้าสู่ระบบด่วนด้วย PSU Passport, Google และ LINE
                                </p>
                            </div>

                            <form id="register-form" autocomplete="on" novalidate>
                                <div class="oar-field">
                                    <label class="oar-label" for="name">
                                        ชื่อ-นามสกุล <span class="oar-req">*</span>
                                    </label>
                                    <div class="oar-input-group">
                                        <i class="fa fa-user-o oar-input-icon" aria-hidden="true"></i>
                                        <input class="oar-input" type="text" id="name" name="name"
                                            placeholder="กรอกชื่อ-นามสกุลของคุณ" autocomplete="name" maxlength="255">
                                    </div>
                                </div>

                                <div class="row oar-form-row">
                                    <div class="col-md-7">
                                        <div class="oar-field">
                                            <label class="oar-label" for="user_id">
                                                อีเมล <span class="oar-req">*</span>
                                            </label>
                                            <div class="oar-input-group">
                                                <i class="fa fa-envelope-o oar-input-icon" aria-hidden="true"></i>
                                                <input class="oar-input" type="email" id="user_id" name="user_id"
                                                    placeholder="example@email.com" autocomplete="email" maxlength="255">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="oar-field">
                                            <label class="oar-label" for="user_phone">
                                                เบอร์โทรศัพท์ <span class="oar-optional">(ไม่บังคับ)</span>
                                            </label>
                                            <div class="oar-input-group">
                                                <i class="fa fa-phone oar-input-icon" aria-hidden="true"></i>
                                                <input class="oar-input" type="tel" id="user_phone" name="user_phone"
                                                    placeholder="08X-XXX-XXXX" autocomplete="tel" maxlength="20">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row oar-form-row">
                                    <div class="col-md-6">
                                        <div class="oar-field my-pass-div">
                                            <label class="oar-label" for="user_password">
                                                รหัสผ่าน <span class="oar-req">*</span>
                                            </label>
                                            <div class="oar-input-group">
                                                <i class="fa fa-lock oar-input-icon" aria-hidden="true"></i>
                                                <input class="oar-input my-pass-input oar-input--has-toggle" type="password"
                                                    id="user_password" name="user_password"
                                                    placeholder="อย่างน้อย 8 ตัวอักษร" autocomplete="new-password">
                                                <button type="button" class="oar-pass-toggle my-password"
                                                    aria-label="แสดงหรือซ่อนรหัสผ่าน" title="แสดง/ซ่อนรหัสผ่าน">
                                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oar-field my-pass-div">
                                            <label class="oar-label" for="confirm_password">
                                                ยืนยันรหัสผ่าน <span class="oar-req">*</span>
                                            </label>
                                            <div class="oar-input-group">
                                                <i class="fa fa-lock oar-input-icon" aria-hidden="true"></i>
                                                <input class="oar-input my-pass-input oar-input--has-toggle" type="password"
                                                    id="confirm_password" name="confirm_password"
                                                    placeholder="กรอกรหัสผ่านอีกครั้ง" autocomplete="new-password">
                                                <button type="button" class="oar-pass-toggle my-password"
                                                    aria-label="แสดงหรือซ่อนยืนยันรหัสผ่าน" title="แสดง/ซ่อนรหัสผ่าน">
                                                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="oar-pass-hint">
                                    <i class="fa fa-shield" aria-hidden="true"></i>
                                    <span>รหัสผ่านควรมีความยาวอย่างน้อย 8 ตัวอักษร เพื่อความปลอดภัยของบัญชีคุณ</span>
                                </p>

                                <button type="submit" class="oar-submit-btn" id="register">
                                    <span class="oar-submit-label">สมัครสมาชิก</span>
                                    <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                </button>
                            </form>

                            <div class="oar-divider">
                                <span>หรือเข้าสู่ระบบด่วนโดยไม่ต้องกรอกฟอร์ม</span>
                            </div>

                            <div class="row oar-social-grid">
                                <div class="col-sm-6 mb-2">
                                    <a href="../sso-authen/public/login.php" class="oar-oauth-btn oar-oauth-btn--psu">
                                        <img src="../images/icons/logo-mini.png" alt="PSU Passport" class="oar-oauth-img">
                                        <span>PSU Passport</span>
                                    </a>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <a href="../sso-authen/public/login_google.php" class="oar-oauth-btn oar-oauth-btn--google">
                                        <img src="../images/icons/search.png" alt="Google" class="oar-oauth-img oar-oauth-img--sm">
                                        <span>Google</span>
                                    </a>
                                </div>
                                <?php if ($showLineLogin) { ?>
                                <div class="col-12 mt-1">
                                    <a href="../sso-authen/public/login_line.php" class="oar-oauth-btn oar-oauth-btn--line">
                                        <svg class="oar-social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor"
                                                d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                                        </svg>
                                        <span>เข้าสู่ระบบด้วย LINE</span>
                                    </a>
                                </div>
                                <?php } ?>
                            </div>

                            <div class="oar-login-footer">
                                <p class="oar-register-note mb-2">
                                    มีบัญชีสมาชิกอยู่แล้ว?
                                    <a href="../login/" class="oar-register-link">เข้าสู่ระบบที่นี่</a>
                                </p>
                                <p class="oar-legal-note mb-0">
                                    การสมัครสมาชิกถือว่าท่านยอมรับ
                                    <a href="../privacy.php">นโยบายคุ้มครองข้อมูลส่วนบุคคล (PDPA)</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once("../master/footer.php"); ?>

    <script src="../vendor/animsition/js/animsition.min.js"></script>
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= assetVer('../js/main.js'); ?>"></script>
</body>

</html>
