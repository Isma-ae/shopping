<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">แดชบอร์ด</h3>
            <h6 class="op-7 mb-2">OAR Shopping แดชบอร์ดผู้ดูแลระบบ</h6>
        </div>
    </div>
    <div class="row">
        <?php
            $sql = "SELECT COUNT(id) AS count_member FROM users";
            $obj = $DB->QueryObj($sql);
            $sql2 = "SELECT SUM(total_price) AS total_price FROM orders WHERE status_id = 2 OR status_id = 3";
            $obj2 = $DB->QueryObj($sql2);
            $sql3 = "SELECT COUNT(order_id) AS count_order FROM orders WHERE status_id = 2 OR status_id = 3";
            $obj3 = $DB->QueryObj($sql3);

        ?>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ผู้เยี่ยมชม</p>
                                <h4 class="card-title">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">สมาชิก</p>
                                <h4 class="card-title"><?=$obj[0]["count_member"];?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-luggage-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">ยอดขาย</p>
                                <h4 class="card-title">
                                    ฿ <?= number_format($obj2[0]["total_price"], 0); ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="far fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">รายการสั่งซื้อ</p>
                                <h4 class="card-title"><?=$obj3[0]["count_order"];?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>