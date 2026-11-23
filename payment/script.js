$(document).ready(function () {
    var $orderNoEl = $('#order_no');
    var $ref1El = $('#ref1');
    var $ref2El = $('#ref2');

    // หากไม่ได้อยู่ในหน้าสร้าง QR (เช่น ชำระแล้ว / ยกเลิก / error) ไม่ต้องเริ่มนับเวลาหรือ polling
    if (!$orderNoEl.length || !$ref1El.length || !$ref2El.length) {
        return;
    }

    var orderNo = $.trim($orderNoEl.val());
    var orderRef1 = $.trim($ref1El.val());
    var orderRef2 = $.trim($ref2El.val());
    var redirectUrl = $.trim($('#redirect_url').val()) || '../?page=shipping_list';

    if (!orderNo || !orderRef1 || !orderRef2) {
        return;
    }

    var remainingSeconds = 7 * 60; // 7 นาที
    var paymentDone = false;
    var isChecking = false;

    function formatTimer(totalSec) {
        var mins = Math.floor(totalSec / 60);
        var secs = totalSec % 60;
        return (mins < 10 ? '0' + mins : String(mins)) + ':' + (secs < 10 ? '0' + secs : String(secs));
    }

    $('#countdown').text(formatTimer(remainingSeconds));

    $('#btn-reload-qr').on('click', function () {
        window.location.reload();
    });

    function checkPayment() {
        if (paymentDone || isChecking) return;
        isChecking = true;

        $.ajax({
            type: 'POST',
            url: '../api/check_payment.php',
            data: {
                fn: 'check_ref',
                order_ref1: orderRef1,
                order_ref2: orderRef2
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.data === 't' && !paymentDone) {
                    paymentDone = true;
                    clearInterval(interval);
                    completePaymentFlow();
                }
            },
            error: function (xhr, status, error) {
                console.error('Payment check error:', error);
            },
            complete: function () {
                isChecking = false;
            }
        });
    }

    function completePaymentFlow() {
        // 1. อัปเดตสถานะในฐานข้อมูลก่อน (ตรวจสอบ ref1 + ref2 ฝั่งเซิร์ฟเวอร์)
        $.ajax({
            type: 'POST',
            url: '../api/check_payment.php',
            data: {
                fn: 'change_status',
                order_id: orderNo,
                ref1: orderRef1,
                ref2: orderRef2
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.data === 't') {
                    // 2. ส่งแจ้งเตือน LINE + Email และรอให้เสร็จ (หรือ timeout สูงสุด) ก่อน redirect ไม่ให้ถูกยกเลิกกลางทาง
                    var lineReq = $.ajax({
                        type: 'POST',
                        url: '../api/linenotify.php',
                        data: { order_no: orderNo },
                        timeout: 5000
                    });
                    var mailReq = $.ajax({
                        type: 'POST',
                        url: '../api/send_mail.php',
                        data: { order_no: orderNo },
                        timeout: 5000
                    });

                    var notifyDone = $.Deferred();
                    var settled = 0;
                    function onNotifyFinished() {
                        settled++;
                        if (settled >= 2 && notifyDone.state() === 'pending') {
                            notifyDone.resolve();
                        }
                    }
                    lineReq.always(onNotifyFinished);
                    mailReq.always(onNotifyFinished);
                    setTimeout(function () {
                        if (notifyDone.state() === 'pending') {
                            notifyDone.resolve();
                        }
                    }, 3000);

                    swal({
                        title: 'ชำระเงินสำเร็จ',
                        text: 'ระบบได้รับการชำระเงินสำหรับใบสั่งซื้อ ' + orderNo + ' เรียบร้อยแล้ว',
                        icon: 'success',
                        button: 'ดูรายการสั่งซื้อ'
                    }).then(function () {
                        notifyDone.always(function () {
                            window.location.href = redirectUrl;
                        });
                    });
                } else {
                    swal(
                        'ไม่สามารถอัปเดตสถานะได้',
                        (res && res.message) || 'กรุณาแนบหลักฐานการโอนเงินเพื่อให้เจ้าหน้าที่ตรวจสอบ',
                        'warning'
                    );
                }
            },
            error: function () {
                swal(
                    'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                    'หากท่านโอนเงินแล้ว กรุณาคลิกส่งหลักฐานการโอนเงินเพื่อให้เจ้าหน้าที่ตรวจสอบ',
                    'error'
                );
            }
        });
    }

    var interval = setInterval(function () {
        if (paymentDone) {
            clearInterval(interval);
            return;
        }

        remainingSeconds--;
        if (remainingSeconds <= 0) {
            remainingSeconds = 0;
            $('#countdown').text('00:00');
            clearInterval(interval);
            $('#qr-overlay').css('display', 'flex');
            $('#countdown-box').addClass('is-expired').html(
                '<i class="fa fa-exclamation-circle mr-1" aria-hidden="true"></i>QR Code หมดอายุแล้ว กรุณากดสร้างใหม่'
            );
            return;
        }

        $('#countdown').text(formatTimer(remainingSeconds));

        // ตรวจสอบการชำระเงินทุก ๆ 5 วินาที
        if (remainingSeconds % 5 === 0) {
            checkPayment();
        }
    }, 1000);
});
