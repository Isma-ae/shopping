<?php
    $sidebarUnreadMsg = 0;
    $sidebarPendingEvidence = 0;
    if (isset($DB)) {
        $msgCntRows = $DB->QueryObj("SELECT COUNT(message_id) AS cnt FROM message WHERE `read` = 0");
        if (!empty($msgCntRows)) {
            $sidebarUnreadMsg = (int)($msgCntRows[0]["cnt"] ?? 0);
        }
        $evCntRows = $DB->QueryObj("
            SELECT COUNT(e.evidence_id) AS cnt
            FROM evidence e
            INNER JOIN orders o ON e.order_no = o.order_no
            WHERE o.status_id = 1
        ");
        if (!empty($evCntRows)) {
            $sidebarPendingEvidence = (int)($evCntRows[0]["cnt"] ?? 0);
        }
    }
?>
<style>
.logo-header .logo {
    display: flex;
    align-items: center;
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 36px;
}

.admin-brand-badge {
    display: flex;
    align-items: center;
    width: 100%;
    background: transparent;
    padding: 0;
    transition: transform 0.2s ease;
}

.logo-header .logo:hover .admin-brand-badge {
    transform: translateY(-1px);
}

.admin-brand-badge img {
    width: 100%;
    height: auto;
    max-height: 44px;
    display: block;
    object-fit: contain;
    object-position: left center;
    filter: drop-shadow(0 0 1.5px rgba(255, 255, 255, 0.95))
            drop-shadow(0 0 6px rgba(255, 255, 255, 0.45));
}

.main-header-logo .logo-header .logo {
    padding-right: 0;
    justify-content: center;
}

.main-header-logo .admin-brand-badge img {
    max-width: 160px;
    max-height: 38px;
    object-position: center;
}
</style>
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="./" class="logo" aria-label="OAR Shopping Admin">
                <span class="admin-brand-badge">
                    <img src="../images/icons/S__8732697.png" alt="OAR Shopping" class="navbar-brand">
                </span>
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item <?=($PAGE=="home")?'active':"";?>">
                    <a href="./">
                        <i class="fas fa-home"></i>
                        <p>แดชบอร์ด</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">ข้อมูล</h4>
                </li>
                <li class="nav-item <?=($PAGE=="banner")?'active':"";?>">
                    <a href="?page=banner">
                        <i class="fas fa-chalkboard"></i>
                        <p>แบนเนอร์</p>
                    </a>
                </li>
                <li class="nav-item <?=($PAGE=="category")?'active':"";?>">
                    <a href="?page=category">
                        <i class="fas fa-th-large"></i>
                        <p>ข้อมูลประเภท</p>
                    </a>
                </li>
                <li
                    class="nav-item <?=($PAGE=="products" || $PAGE=="product-add" || $PAGE=="product-stock" || $PAGE=="variant-add" || $PAGE=="product-image")?'active':"";?>">
                    <a href="?page=products">
                        <i class="fas fa-box-open"></i>
                        <p>จัดการสินค้า</p>
                    </a>
                </li>
                <li class="nav-item <?=($PAGE=="orders" || $PAGE=="order-detail")?'active':"";?>">
                    <a href="?page=orders">
                        <i class="fas fa-th-list"></i>
                        <p>รายการสั่งซื้อ</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">การติดต่อและชำระเงิน</h4>
                </li>
                <li class="nav-item <?=($PAGE=="evidence")?'active':"";?>">
                    <a href="?page=evidence">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <p>หลักฐานการชำระเงิน</p>
                        <?php if ($sidebarPendingEvidence > 0) { ?>
                            <span class="badge badge-warning"><?=$sidebarPendingEvidence;?></span>
                        <?php } ?>
                    </a>
                </li>
                <li class="nav-item <?=($PAGE=="message-list" || $PAGE=="message")?'active':"";?>">
                    <a href="?page=message-list">
                        <i class="fas fa-envelope"></i>
                        <p>ข้อความติดต่อ</p>
                        <?php if ($sidebarUnreadMsg > 0) { ?>
                            <span class="badge badge-danger"><?=$sidebarUnreadMsg;?></span>
                        <?php } ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
