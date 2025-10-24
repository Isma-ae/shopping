<style>
.logo-header h2 {
    font-family: "Anton", sans-serif;
    font-weight: 400;
    font-style: normal;
}
</style>
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="./" class="logo">
                <h2 class="text-white">OAR Shopping</h2>
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
                    class="nav-item <?=($PAGE=="products" || $PAGE=="product-add" || $PAGE=="product-stock" || $PAGE=="variant-add")?'active':"";?>">
                    <a href="?page=products">
                        <i class="fas fa-object-group"></i>
                        <p>จัดกลุ่มสินค้า</p>
                    </a>
                </li>
                <li class="nav-item <?=($PAGE=="manage_product" || $PAGE=="product-image")?'active':"";?>">
                    <a href="?page=manage_product">
                        <i class="fas fa-image"></i>
                        <p>จัดการสินค้า</p>
                    </a>
                </li>
                <li class="nav-item <?=($PAGE=="orders" || $PAGE=="order-detail")?'active':"";?>">
                    <a href="?page=orders">
                        <i class="fas fa-th-list"></i>
                        <p>รายการสั่งซื้อ</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>