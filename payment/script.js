$(document).ready(function () {
    let timer2 = "7:01";
    let order_no = $('#order_no').val();
    let order_ref1 = $('#ref1').val();
    let order_ref2 = $('#ref2').val();

    let paymentDone = false;

    let interval = setInterval(function () {
        let timer = timer2.split(':');
        let minutes = parseInt(timer[0], 10);
        let seconds = parseInt(timer[1], 10);

        --seconds;
        if (seconds < 0) {
            seconds = 59;
            --minutes;
        }

        seconds = (seconds < 10) ? '0' + seconds : seconds;
        $('#countdown').text(minutes + ':' + seconds);

        // ตรวจสอบการชำระเงินทุก ๆ 5 วินาที
        if (!paymentDone && (seconds % 5 === 0)) {
            $.ajax({
                type: "post",
                url: "../api/check_payment.php",
                data: {
                    fn: "check_ref",
                    order_ref1: order_ref1,
                    order_ref2: order_ref2
                },
                dataType: "json",
                success: function (res) {
                    if (res.data === 't' && !paymentDone) {
                        paymentDone = true; // ✅ กันซ้ำ
                        clearInterval(interval);
                        swal({
                            title: "ชำระเงินสำเร็จ",
                            icon: "success",
                        });
                        send_line(order_no);
                        send_mail(order_no);
                        change_status(order_no, order_ref1);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        // หมดเวลา
        if (minutes <= 0 && seconds <= 0) {
            clearInterval(interval);
            window.location.href = "../?page=shipping_list";
        }

        timer2 = minutes + ':' + seconds;
    }, 1000);


    function change_status(order_id, ref1) {
        $.ajax({
            type: "post",
            url: "../api/check_payment.php",
            data: {
                fn: "change_status",
                order_id: order_id,
                ref1: ref1
            },
            dataType: "json",
            success: function (res) {
                if (res.data === 't') {
                    window.location.href = "../?page=shipping_list";
                } else {
                    alert('ไม่สำเร็จ');
                }
            }
        });
    }

    function send_line(order_no) {
        $.ajax({
            type: "post",
            url: "../api/linenotify.php",
            data: {
                order_no: order_no
            },
            dataType: "json",
            success: function (response) {}
        });
    }

    function send_mail(order_no) {
        $.ajax({
            type: "post",
            url: "../api/send_mail.php",
            data: {
                order_no: order_no
            },
            dataType: "json",
            success: function (response) {}
        });
    }

});