<div class="page-inner dashboard-page">
    <!-- Header & Period Controls -->
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between pt-2 pb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">แดชบอร์ดภาพรวมระบบ</h3>
            <p class="text-muted mb-0">
                OAR Shopping &middot; <span id="dashboard-period-badge" class="badge bg-light text-dark border">ข้อมูลทั้งหมด</span>
                <span class="ms-2 small text-muted" id="dashboard-last-updated"></span>
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm dashboard-period-group" role="group" aria-label="ช่วงเวลา">
                <button type="button" class="btn btn-primary active" data-period="all">ทั้งหมด</button>
                <button type="button" class="btn btn-outline-primary" data-period="year">ปีนี้</button>
                <button type="button" class="btn btn-outline-primary" data-period="month">เดือนนี้</button>
                <button type="button" class="btn btn-outline-primary" data-period="30d">30 วัน</button>
                <button type="button" class="btn btn-outline-primary" data-period="7d">7 วัน</button>
            </div>
            <button type="button" class="btn btn-sm btn-light border" id="btn-refresh-dashboard" title="รีเฟรชข้อมูล">
                <i class="fas fa-sync-alt me-1"></i> รีเฟรช
            </button>
        </div>
    </div>

    <div id="dashboard-error-banner" class="alert alert-danger d-none" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span id="dashboard-error-text">ไม่สามารถโหลดข้อมูลแดชบอร์ดได้</span>
    </div>

    <!-- Row 1: Primary KPI Cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats card-round h-100 mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ยอดขายสุทธิ (ชำระแล้ว)</p>
                                <h4 class="card-title mb-1" id="kpi-total-revenue">฿ 0</h4>
                                <div class="kpi-subtext text-muted" id="kpi-revenue-breakdown">ค่าสินค้า ฿ 0 &middot; ค่าส่ง ฿ 0</div>
                                <div class="kpi-subtext text-success" id="kpi-est-profit">กำไรขั้นต้นประมาณ ฿ 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats card-round h-100 mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">คำสั่งซื้อสำเร็จ</p>
                                <h4 class="card-title mb-1" id="kpi-order-count">0</h4>
                                <div class="kpi-subtext text-muted" id="kpi-avg-order">เฉลี่ย ฿ 0 / ออเดอร์</div>
                                <div class="kpi-subtext text-muted" id="kpi-buyer-split">สมาชิก 0 &middot; Guest 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats card-round h-100 mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-truck-loading"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รอจัดส่ง (ทั้งหมด)</p>
                                <h4 class="card-title mb-1" id="kpi-pending-shipping">0</h4>
                                <div class="kpi-subtext text-muted" id="kpi-waiting-pay">รอชำระเงิน 0 รายการ</div>
                                <div class="kpi-subtext text-danger" id="kpi-pending-docs">ค้างใบเสร็จ 0 &middot; ค้างเลขพัสดุ 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats card-round h-100 mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สมาชิก &amp; สินค้า</p>
                                <h4 class="card-title mb-1" id="kpi-total-members">0 คน</h4>
                                <div class="kpi-subtext text-muted" id="kpi-products-count">เปิดขาย 0 &middot; รอจำหน่าย 0</div>
                                <div class="kpi-subtext text-muted" id="kpi-items-sold">ขายแล้วในช่วงนี้ 0 ชิ้น</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Operational Action Strip (Always all-time queue) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="?page=orders" class="card card-round action-strip-card h-100 mb-0 text-decoration-none">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">รอจัดส่งทันที</div>
                        <div class="fs-5 fw-bold text-warning" id="alert-pending-shipping">0 รายการ</div>
                    </div>
                    <span class="badge rounded-pill bg-warning-subtle text-warning px-3 py-2">
                        <i class="fas fa-box-open"></i>
                    </span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="#ops-panel" class="card card-round action-strip-card h-100 mb-0 text-decoration-none js-open-tab" data-target-tab="#tab-fulfillment-btn">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">ขอใบเสร็จ / รอเลขพัสดุ</div>
                        <div class="fs-5 fw-bold text-danger" id="alert-docs-pending">0 รายการ</div>
                    </div>
                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="?page=message-list" class="card card-round action-strip-card h-100 mb-0 text-decoration-none">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">ข้อความลูกค้ายังไม่อ่าน</div>
                        <div class="fs-5 fw-bold text-info" id="alert-unread-msg">0 ข้อความ</div>
                    </div>
                    <span class="badge rounded-pill bg-info-subtle text-info px-3 py-2">
                        <i class="fas fa-envelope"></i>
                    </span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="?page=orders" class="card card-round action-strip-card h-100 mb-0 text-decoration-none">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">แนบสลิปเปิดอยู่ / สั่งจอง</div>
                        <div class="fs-5 fw-bold text-primary" id="alert-evidence-reserve">0 / 0</div>
                    </div>
                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                        <i class="fas fa-receipt"></i>
                    </span>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 3: Trend Chart + Category Breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card card-round h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h4 class="card-title mb-0">แนวโน้มยอดขายและคำสั่งซื้อ</h4>
                        <small class="text-muted" id="trend-subtitle">แสดงยอดขายสุทธิ (บาท) เทียบจำนวนออเดอร์ที่ชำระเงินแล้ว</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-box chart-box-lg">
                        <canvas id="salesTrendChart"></canvas>
                        <div id="salesTrendEmpty" class="chart-empty-state d-none">
                            <i class="fas fa-chart-line fa-2x mb-2 text-muted"></i>
                            <div>ยังไม่มีข้อมูลยอดขายในช่วงเวลาที่เลือก</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-round h-100 mb-0">
                <div class="card-header">
                    <h4 class="card-title mb-0">ยอดขายตามหมวดหมู่</h4>
                    <small class="text-muted">เฉพาะรายการที่ชำระแล้ว/จัดส่งแล้ว</small>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="chart-box chart-box-sm">
                        <canvas id="categorySalesChart"></canvas>
                        <div id="categorySalesEmpty" class="chart-empty-state d-none">
                            <i class="fas fa-chart-pie fa-2x mb-2 text-muted"></i>
                            <div>ยังไม่มีข้อมูลหมวดหมู่ในช่วงเวลานี้</div>
                        </div>
                    </div>
                    <div id="categorySalesList" class="mt-3 small"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Status Breakdown + Shipping Methods + Low Stock Alert -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card card-round h-100 mb-0">
                <div class="card-header">
                    <h4 class="card-title mb-0">สัดส่วนสถานะคำสั่งซื้อ</h4>
                    <small class="text-muted">ภาพรวมตามช่วงเวลาที่เลือก</small>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="chart-box chart-box-xs">
                        <canvas id="orderStatusChart"></canvas>
                        <div id="orderStatusEmpty" class="chart-empty-state d-none">
                            <div>ไม่มีคำสั่งซื้อในช่วงเวลาที่เลือก</div>
                        </div>
                    </div>
                    <div id="orderStatusList" class="mt-3 small"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card card-round h-100 mb-0">
                <div class="card-header">
                    <h4 class="card-title mb-0">ช่องทางการรับสินค้า</h4>
                    <small class="text-muted">เปรียบเทียบจำนวนออเดอร์ที่ชำระแล้ว</small>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="chart-box chart-box-xs">
                        <canvas id="shippingTypeChart"></canvas>
                        <div id="shippingTypeEmpty" class="chart-empty-state d-none">
                            <div>ไม่มีข้อมูลการจัดส่งในช่วงเวลานี้</div>
                        </div>
                    </div>
                    <div id="shippingTypeList" class="mt-3 small"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-round h-100 mb-0">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title mb-0">แจ้งเตือนสต๊อกคงเหลือต่ำ</h4>
                        <small class="text-muted">คงเหลือ &le; 10 ชิ้น (หักยอดชำระแล้ว/จัดส่งแล้ว)</small>
                    </div>
                    <a href="?page=products" class="btn btn-xs btn-outline-primary">จัดการสินค้า</a>
                </div>
                <div class="card-body p-0">
                    <div class="dashboard-scroll-list" id="lowStockList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 5: Best Sellers + Operations Tabs -->
    <div class="row g-3" id="ops-panel">
        <div class="col-lg-6">
            <div class="card card-round h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h4 class="card-title mb-0">สินค้าขายดี (Top 8)</h4>
                        <small class="text-muted">จัดอันดับตามจำนวนชิ้นที่ขายได้จริง</small>
                    </div>
                    <div class="btn-group btn-group-xs" role="group" aria-label="โหมดสินค้าขายดี">
                        <button type="button" class="btn btn-secondary active js-bestseller-mode" data-mode="product">ตามสินค้า</button>
                        <button type="button" class="btn btn-outline-secondary js-bestseller-mode" data-mode="variant">ตามสี/ไซส์</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dashboard-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>รายการ</th>
                                    <th class="text-end">จำนวน</th>
                                    <th class="text-end">ยอดขาย</th>
                                </tr>
                            </thead>
                            <tbody id="bestSellersBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-round h-100 mb-0">
                <div class="card-header pb-0 border-bottom-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h4 class="card-title mb-0">ติดตามคำสั่งซื้อและงานค้าง</h4>
                        <a href="?page=orders" class="btn btn-xs btn-outline-primary">ดูทั้งหมด</a>
                    </div>
                    <ul class="nav nav-tabs nav-line nav-color-secondary" id="dashboardOpsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-recent-orders-btn" data-bs-toggle="pill"
                                data-bs-target="#tab-recent-orders" type="button" role="tab">
                                ล่าสุด (10)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-fulfillment-btn" data-bs-toggle="pill"
                                data-bs-target="#tab-fulfillment" type="button" role="tab">
                                ค้างดำเนินการ <span class="badge bg-danger ms-1" id="tab-fulfillment-badge">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-health-btn" data-bs-toggle="pill"
                                data-bs-target="#tab-health" type="button" role="tab">
                                รีวิว &amp; สุขภาพข้อมูล <span class="badge bg-warning text-dark ms-1" id="tab-health-badge">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="dashboardOpsTabContent">
                        <div class="tab-pane fade show active" id="tab-recent-orders" role="tabpanel">
                            <div class="table-responsive dashboard-scroll-table">
                                <table class="table table-hover align-middle mb-0 dashboard-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>เลขใบสั่งซื้อ</th>
                                            <th>ผู้รับ / ผู้สั่ง</th>
                                            <th class="text-end">ยอดสุทธิ</th>
                                            <th>สถานะ</th>
                                            <th class="text-end">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentOrdersBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-fulfillment" role="tabpanel">
                            <div class="table-responsive dashboard-scroll-table">
                                <table class="table table-hover align-middle mb-0 dashboard-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>เลขใบสั่งซื้อ</th>
                                            <th>ผู้รับ</th>
                                            <th>สิ่งที่ต้องทำ</th>
                                            <th class="text-end">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingFulfillmentBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-health" role="tabpanel">
                            <div class="p-3 dashboard-scroll-list" id="reviewsAndHealthBody"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
