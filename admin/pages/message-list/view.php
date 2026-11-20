<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">ข้อความที่ลูกค้าติดต่อ</h3>
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
                <a href="?page=message-list">ข้อความติดต่อ</a>
            </li>
        </ul>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ข้อความทั้งหมด</p>
                                <h4 class="card-title" id="msgStatTotal">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ยังไม่อ่าน</p>
                                <h4 class="card-title" id="msgStatUnread">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รอตอบกลับ</p>
                                <h4 class="card-title" id="msgStatUnreplied">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stats card-round mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ตอบกลับแล้ว</p>
                                <h4 class="card-title" id="msgStatReplied">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h4 class="card-title mb-0">รายการข้อความจากลูกค้า</h4>
                        <ul class="nav nav-pills nav-secondary msg-filter-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active js-msg-filter" data-filter="all" href="#">ทั้งหมด</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-msg-filter" data-filter="unread" href="#">ยังไม่อ่าน</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-msg-filter" data-filter="unreplied" href="#">รอตอบกลับ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-msg-filter" data-filter="replied" href="#">ตอบกลับแล้ว</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="message-table" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 48px;">#</th>
                                    <th style="width: 220px;">อีเมลผู้ติดต่อ</th>
                                    <th>ข้อความ</th>
                                    <th style="width: 155px;">วันที่ส่ง</th>
                                    <th style="width: 140px;">สถานะ</th>
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
