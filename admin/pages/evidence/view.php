<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">หลักฐานการชำระเงิน</h3>
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
                <a href="?page=evidence">หลักฐานการชำระเงิน</a>
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
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">หลักฐานทั้งหมด</p>
                                <h4 class="card-title" id="evStatTotal">0</h4>
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
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รอตรวจสอบชำระ</p>
                                <h4 class="card-title" id="evStatPending">0</h4>
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
                                <p class="card-category">ชำระเงินแล้ว</p>
                                <h4 class="card-title" id="evStatVerified">0</h4>
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
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ยกเลิก / ไม่พบออเดอร์</p>
                                <h4 class="card-title" id="evStatOther">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evidence Table Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h4 class="card-title mb-0">รายการสลิปที่ลูกค้าแจ้งชำระเงิน</h4>
                        <ul class="nav nav-pills nav-secondary ev-filter-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active js-ev-filter" data-filter="all" href="#">ทั้งหมด</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-ev-filter" data-filter="pending" href="#">รอตรวจสอบ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-ev-filter" data-filter="verified" href="#">ชำระแล้ว</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-ev-filter" data-filter="other" href="#">ยกเลิก / ไม่พบออเดอร์</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="evidence-table" class="display table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 48px;">#</th>
                                    <th style="width: 90px;">สลิป</th>
                                    <th style="width: 190px;">หมายเลขคำสั่งซื้อ</th>
                                    <th>ผู้สั่งซื้อ</th>
                                    <th style="width: 130px;" class="text-end">ยอดสั่งซื้อ</th>
                                    <th style="width: 150px;">สถานะคำสั่งซื้อ</th>
                                    <th style="width: 210px;" class="text-end">จัดการ</th>
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

<!-- Slip Preview & Payment Verification Modal -->
<div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="slipModalLabel">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>ตรวจสอบหลักฐานการชำระเงิน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4 align-items-start">
                    <div class="col-md-6 text-center">
                        <div class="ev-modal-slip-box">
                            <img id="modalSlipImg" src="" alt="หลักฐานการโอนเงิน" class="img-fluid rounded">
                            <div id="modalSlipMissing" class="ev-slip-missing d-none">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <div>ไม่พบไฟล์รูปสลิปบนเซิร์ฟเวอร์นี้</div>
                                <small id="modalSlipFilename" class="text-muted"></small>
                            </div>
                        </div>
                        <a id="modalSlipOpenLink" href="#" target="_blank" rel="noopener noreferrer"
                            class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-external-link-alt me-1"></i>เปิดดูภาพขนาดเต็ม
                        </a>
                    </div>
                    <div class="col-md-6">
                        <div class="ev-detail-list">
                            <div class="ev-detail-row">
                                <span>หมายเลขคำสั่งซื้อ</span>
                                <strong id="modalOrderNo">-</strong>
                            </div>
                            <div class="ev-detail-row">
                                <span>ผู้สั่งซื้อ</span>
                                <strong id="modalBuyerName">-</strong>
                            </div>
                            <div class="ev-detail-row">
                                <span>เบอร์โทร / อีเมล</span>
                                <strong id="modalBuyerContact">-</strong>
                            </div>
                            <div class="ev-detail-row">
                                <span>วันที่สั่งซื้อ</span>
                                <strong id="modalOrderDate">-</strong>
                            </div>
                            <div class="ev-detail-row">
                                <span>ยอดชำระทั้งหมด</span>
                                <strong class="text-primary fs-5" id="modalTotalPrice">-</strong>
                            </div>
                            <div class="ev-detail-row">
                                <span>สถานะปัจจุบัน</span>
                                <span id="modalOrderStatus">-</span>
                            </div>
                        </div>

                        <div class="mt-4 d-grid gap-2">
                            <button type="button" class="btn btn-success d-none" id="modalApproveBtn" data-order-id="">
                                <i class="fas fa-check-circle me-1"></i>ยืนยันการชำระเงิน (เปลี่ยนเป็นรอจัดส่ง)
                            </button>
                            <a href="#" class="btn btn-outline-secondary d-none" id="modalOrderDetailLink">
                                <i class="fas fa-list-alt me-1"></i>ดูรายละเอียดคำสั่งซื้อ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
