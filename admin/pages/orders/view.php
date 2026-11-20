<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">รายการสั่งซื้อ</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="./">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="?page=orders">รายการสั่งซื้อ</a>
            </li>
        </ul>
    </div>

    <!-- Clickable Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0 ord-stat-card is-active" data-status-id="1">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รอชำระเงิน</p>
                                <h4 class="card-title" id="statCount1">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0 ord-stat-card" data-status-id="2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ชำระแล้ว · รอจัดส่ง</p>
                                <h4 class="card-title" id="statCount2">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0 ord-stat-card" data-status-id="3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">จัดส่งแล้ว</p>
                                <h4 class="card-title" id="statCount3">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0 ord-stat-card" data-status-id="4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ยกเลิก</p>
                                <h4 class="card-title" id="statCount4">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <ul class="nav nav-pills nav-secondary ord-status-tabs" id="line-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active choose-status" id="line-status1" status-id="1" href="#">
                                    รอชำระเงิน <span class="ord-tab-badge count1">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link choose-status" id="line-status2" status-id="2" href="#">
                                    รอจัดส่ง <span class="ord-tab-badge count2">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link choose-status" id="line-status3" status-id="3" href="#">
                                    จัดส่งแล้ว <span class="ord-tab-badge count3">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link choose-status" id="line-status4" status-id="4" href="#">
                                    ยกเลิก <span class="ord-tab-badge count4">0</span>
                                </a>
                            </li>
                        </ul>

                        <div class="ord-ship-filter-wrap">
                            <label for="shippingTypeFilter" class="ord-filter-label mb-0">
                                <i class="fas fa-filter me-1"></i>วิธีรับสินค้า:
                            </label>
                            <select id="shippingTypeFilter" class="form-select form-select-sm ord-filter-select">
                                <option value="0">ทั้งหมด</option>
                                <option value="1">รับหน้าร้าน</option>
                                <option value="2">ส่งภายในวิทยาเขต</option>
                                <option value="3">ส่งภายนอกวิทยาเขต</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 44px;">#</th>
                                    <th style="width: 195px;">หมายเลขคำสั่งซื้อ</th>
                                    <th style="width: 185px;">ผู้สั่งซื้อ</th>
                                    <th style="width: 120px;" class="text-end">ยอดสั่งซื้อ</th>
                                    <th style="width: 155px;">สถานะ</th>
                                    <th>การจัดส่ง</th>
                                    <th style="width: 155px;" class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
