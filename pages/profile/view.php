<?php
    if (!isset($_SESSION["user_info"])) {
        echo "<script>window.location.href = './login/';</script>";
        exit();
    }

    if (!function_exists("profileEsc")) {
        function profileEsc($val) {
            return htmlspecialchars((string)($val ?? ""), ENT_QUOTES, "UTF-8");
        }
    }

    $sessionUser = $currentUser ?? $_SESSION["user_info"];
    $userIdInt = (int)($sessionUser["id"] ?? 0);

    // Fetch latest user record from DB
    $userRows = $DB->QueryObj("
        SELECT id, user_id, email, name, role, user_phone, user_password, created_at
        FROM users
        WHERE id = {$userIdInt}
        LIMIT 1
    ");
    $userRow = count($userRows) > 0 ? $userRows[0] : $sessionUser;

    $displayName = (string)($userRow["name"] ?? "");
    $displayEmail = (string)($userRow["email"] ?? "");
    $displayUserId = (string)($userRow["user_id"] ?? "");
    $displayPhone = (string)($userRow["user_phone"] ?? "");
    $displayRole = (string)($userRow["role"] ?? "user");
    $hasLocalPassword = !empty($userRow["user_password"]);
    $createdAt = !empty($userRow["created_at"]) ? date("d/m/Y", strtotime((string)$userRow["created_at"])) : "-";

    // Keep session synced with latest DB info
    $_SESSION["user_info"]["name"] = $displayName;
    $_SESSION["user_info"]["user_phone"] = $displayPhone;

    // Fetch order summary counts for sidebar overview
    $orderStats = [1 => 0, 2 => 0, 3 => 0];
    $statRows = $DB->QueryObj("
        SELECT status_id, COUNT(order_id) AS cnt
        FROM orders
        WHERE user_id = '{$userIdInt}'
        GROUP BY status_id
    ");
    foreach ($statRows as $sr) {
        $sid = (int)($sr["status_id"] ?? 0);
        if (isset($orderStats[$sid])) {
            $orderStats[$sid] = (int)$sr["cnt"];
        }
    }

    $roleLabel = $displayRole === "admin" ? "ผู้ดูแลระบบ" : "สมาชิก OAR Shopping";
    $avatarChar = mb_strtoupper(mb_substr(trim($displayName) !== "" ? trim($displayName) : "U", 0, 1, "UTF-8"), "UTF-8");
?>

<!-- Breadcrumb -->
<section class="profile-breadcrumb-bar">
    <div class="container">
        <nav class="profile-breadcrumb" aria-label="breadcrumb">
            <a href="./">หน้าแรก</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="is-current">โปรไฟล์ของฉัน</span>
        </nav>
    </div>
</section>

<!-- Main Profile Page -->
<section class="profile-page-section">
    <div class="container">
        <div class="profile-page-head">
            <div>
                <span class="oar-eyebrow">MY ACCOUNT</span>
                <h1 class="oar-heading">โปรไฟล์ของฉัน</h1>
            </div>
            <a href="?page=shipping_list" class="profile-orders-btn">
                <i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>รายการสั่งซื้อของฉัน
            </a>
        </div>

        <div class="row align-items-start">
            <!-- Left Column: Account Summary -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <aside class="profile-sidebar-card">
                    <div class="profile-hero-banner">
                        <div class="profile-avatar" id="profileAvatarChar"><?= profileEsc($avatarChar); ?></div>
                        <h2 class="profile-hero-name" id="profileHeroName"><?= profileEsc($displayName); ?></h2>
                        <p class="profile-hero-email mb-2"><?= profileEsc($displayEmail); ?></p>
                        <span class="profile-role-badge<?= $displayRole === 'admin' ? ' is-admin' : ''; ?>">
                            <i class="fa <?= $displayRole === 'admin' ? 'fa-shield' : 'fa-user-o'; ?> mr-1" aria-hidden="true"></i>
                            <?= profileEsc($roleLabel); ?>
                        </span>
                    </div>

                    <div class="profile-sidebar-body">
                        <div class="profile-stats-grid">
                            <a href="?page=shipping_list" class="profile-stat-item">
                                <strong><?= $orderStats[1]; ?></strong>
                                <span>รอชำระเงิน</span>
                            </a>
                            <a href="?page=shipping_list" class="profile-stat-item">
                                <strong><?= $orderStats[2]; ?></strong>
                                <span>รอจัดส่ง</span>
                            </a>
                            <a href="?page=shipping_list" class="profile-stat-item">
                                <strong><?= $orderStats[3]; ?></strong>
                                <span>จัดส่งแล้ว</span>
                            </a>
                        </div>

                        <ul class="profile-meta-list">
                            <?php if ($displayUserId !== "") { ?>
                            <li>
                                <span><i class="fa fa-id-badge" aria-hidden="true"></i>รหัสผู้ใช้</span>
                                <strong><?= profileEsc($displayUserId); ?></strong>
                            </li>
                            <?php } ?>
                            <li>
                                <span><i class="fa fa-phone" aria-hidden="true"></i>เบอร์โทรศัพท์</span>
                                <strong id="profileSidebarPhone"><?= $displayPhone !== "" ? profileEsc($displayPhone) : "ยังไม่ระบุ"; ?></strong>
                            </li>
                            <li>
                                <span><i class="fa fa-calendar-o" aria-hidden="true"></i>สมาชิกตั้งแต่</span>
                                <strong><?= profileEsc($createdAt); ?></strong>
                            </li>
                        </ul>

                        <div class="profile-quick-actions">
                            <a href="?page=cart" class="profile-quick-link">
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                <span>ตะกร้าสินค้า</span>
                                <i class="fa fa-angle-right ml-auto" aria-hidden="true"></i>
                            </a>
                            <a href="?page=shipping_list" class="profile-quick-link">
                                <i class="fa fa-list-alt" aria-hidden="true"></i>
                                <span>ประวัติการสั่งซื้อ</span>
                                <i class="fa fa-angle-right ml-auto" aria-hidden="true"></i>
                            </a>
                            <?php if ($displayRole === "admin") { ?>
                            <a href="./admin/" class="profile-quick-link is-admin-link">
                                <i class="fa fa-cog" aria-hidden="true"></i>
                                <span>จัดการระบบ (ผู้ดูแล)</span>
                                <i class="fa fa-angle-right ml-auto" aria-hidden="true"></i>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Right Column: Profile Info + Address Book -->
            <div class="col-lg-8">
                <!-- Card 1: Personal Info -->
                <div class="profile-card mb-4">
                    <div class="profile-card-head">
                        <div class="profile-card-title">
                            <span class="profile-card-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                            <div>
                                <h3>ข้อมูลส่วนตัว</h3>
                                <p class="mb-0">ข้อมูลนี้จะถูกนำไปกรอกอัตโนมัติในขั้นตอนการสั่งซื้อและจัดส่งสินค้า</p>
                            </div>
                        </div>
                    </div>

                    <form id="form_profile" class="profile-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="profile-label" for="profile_name">
                                    ชื่อ-นามสกุล <span class="profile-req">*</span>
                                </label>
                                <input type="text" class="profile-input" id="profile_name" name="name"
                                    value="<?= profileEsc($displayName); ?>" placeholder="กรอกชื่อ-นามสกุลของคุณ">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="profile-label" for="profile_phone">หมายเลขโทรศัพท์</label>
                                <input type="text" class="profile-input" id="profile_phone" name="user_phone"
                                    value="<?= profileEsc($displayPhone); ?>" placeholder="เช่น 08x-xxx-xxxx">
                            </div>

                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="profile-label" for="profile_email">อีเมล</label>
                                <input type="email" class="profile-input profile-input--readonly" id="profile_email"
                                    value="<?= profileEsc($displayEmail); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="profile-label" for="profile_uid">รหัสผู้ใช้ / บัญชี</label>
                                <input type="text" class="profile-input profile-input--readonly" id="profile_uid"
                                    value="<?= $displayUserId !== "" ? profileEsc($displayUserId) : "-"; ?>" readonly>
                            </div>
                        </div>

                        <div class="profile-form-footer">
                            <button type="submit" class="profile-btn-primary" id="btn_save_profile">
                                <i class="fa fa-check mr-2" aria-hidden="true"></i>
                                <span>บันทึกข้อมูลส่วนตัว</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Shipping Address Book (synced with Checkout) -->
                <div class="profile-card<?= $hasLocalPassword ? ' mb-4' : ''; ?>" id="address-section">
                    <div class="profile-card-head">
                        <div class="profile-card-title">
                            <span class="profile-card-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                            <div>
                                <h3>
                                    ที่อยู่จัดส่งของฉัน
                                    <span class="profile-addr-count d-none" id="profileAddrCount">0</span>
                                </h3>
                                <p class="mb-0">ที่อยู่ที่บันทึกไว้จะแสดงให้เลือกอัตโนมัติในหน้าชำระเงิน (ส่งภายนอกวิทยาเขต)</p>
                            </div>
                        </div>

                        <button type="button" class="profile-btn-add-addr add-address">
                            <i class="fa fa-plus mr-2" aria-hidden="true"></i>เพิ่มที่อยู่ใหม่
                        </button>
                    </div>

                    <div class="profile-card-body">
                        <div id="profileAddressList">
                            <div class="profile-loading">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                                <span>กำลังโหลดรายการที่อยู่...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Change Password (only for local registered accounts) -->
                <?php if ($hasLocalPassword) { ?>
                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-card-title">
                            <span class="profile-card-icon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                            <div>
                                <h3>เปลี่ยนรหัสผ่าน</h3>
                                <p class="mb-0">แนะนำให้ตั้งรหัสผ่านที่มีความยาวอย่างน้อย 6 ตัวอักษร</p>
                            </div>
                        </div>
                    </div>

                    <form id="form_password" class="profile-card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="profile-label" for="old_password">
                                    รหัสผ่านปัจจุบัน <span class="profile-req">*</span>
                                </label>
                                <input type="password" class="profile-input" id="old_password" name="old_password"
                                    placeholder="กรอกรหัสผ่านปัจจุบัน" autocomplete="current-password">
                            </div>

                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="profile-label" for="new_password">
                                    รหัสผ่านใหม่ <span class="profile-req">*</span>
                                </label>
                                <input type="password" class="profile-input" id="new_password" name="new_password"
                                    placeholder="อย่างน้อย 6 ตัวอักษร" autocomplete="new-password">
                            </div>

                            <div class="col-md-6">
                                <label class="profile-label" for="confirm_password">
                                    ยืนยันรหัสผ่านใหม่ <span class="profile-req">*</span>
                                </label>
                                <input type="password" class="profile-input" id="confirm_password" name="confirm_password"
                                    placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="profile-form-footer">
                            <button type="submit" class="profile-btn-outline" id="btn_change_password">
                                <i class="fa fa-key mr-2" aria-hidden="true"></i>
                                <span>อัปเดตรหัสผ่าน</span>
                            </button>
                        </div>
                    </form>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<!-- Address Modal (shared with api/address.php -> shows on Checkout) -->
<div class="modal fade profile-modal" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header profile-modal-header">
                <h5 class="modal-title" id="addModalLabel">เพิ่มที่อยู่จัดส่งใหม่</h5>
                <button type="button" class="close profile-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body profile-modal-body">
                <form class="w-full" id="form_address" data-mode="add">
                    <div class="row">
                        <input type="hidden" id="address_id" name="address_id" value="">

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="name">
                                ชื่อ-นามสกุลผู้รับ <span class="profile-req">*</span>
                            </label>
                            <input class="profile-input" id="name" type="text" name="name" placeholder="กรอกชื่อผู้รับพัสดุ">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="phone">
                                หมายเลขโทรศัพท์ <span class="profile-req">*</span>
                            </label>
                            <input class="profile-input" id="phone" type="text" name="phone" placeholder="เช่น 08x-xxx-xxxx">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="province_id">
                                จังหวัด <span class="profile-req">*</span>
                            </label>
                            <select class="js-example-basic-single" name="province_id" id="province_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="district_id">
                                เขต / อำเภอ <span class="profile-req">*</span>
                            </label>
                            <select class="js-example-basic-single" name="district_id" id="district_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="subdistrict_id">
                                แขวง / ตำบล <span class="profile-req">*</span>
                            </label>
                            <select class="js-example-basic-single" name="subdistrict_id" id="subdistrict_id"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="profile-label" for="zip_code">
                                รหัสไปรษณีย์ <span class="profile-req">*</span>
                            </label>
                            <input class="profile-input profile-input--readonly" id="zip_code" type="text" name="zip_code"
                                readonly placeholder="แสดงอัตโนมัติเมื่อเลือกตำบล">
                        </div>

                        <div class="col-12">
                            <label class="profile-label" for="address_at">
                                บ้านเลขที่, ซอย, หมู่, ถนน <span class="profile-req">*</span>
                            </label>
                            <textarea class="profile-textarea" id="address_at" name="address_at" rows="3"
                                placeholder="กรอกรายละเอียดที่อยู่จัดส่ง"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer profile-modal-footer">
                <button type="button" class="profile-modal-cancel" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="profile-modal-save" id="btn_submit_address">บันทึกที่อยู่</button>
            </div>
        </div>
    </div>
</div>
