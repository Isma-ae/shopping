$(document).ready(function () {
    // Delegated cancel-order handler (fixes duplicate #cancel-order ID bug when multiple pending orders exist)
    $(document).on('click', '.js-cancel-order', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var order_id = $btn.attr('data-order-id');

        swal({
            title: "ยืนยันยกเลิกคำสั่งซื้อ?",
            text: "คุณต้องการยกเลิกคำสั่งซื้อนี้ใช่หรือไม่",
            icon: "warning",
            buttons: ["ไม่ยกเลิก", "ยืนยันยกเลิก"],
            dangerMode: true,
        }).then(function (willCancel) {
            if (!willCancel) return;

            $btn.prop('disabled', true);
            $.ajax({
                type: "post",
                url: "api/checkout.php",
                data: {
                    fn: "cancel_order",
                    order_id: order_id
                },
                dataType: "json",
                success: function (res) {
                    swal({
                        title: res.title || "ยกเลิกสำเร็จ",
                        text: res.msg || "รายการสั่งซื้อของคุณถูกยกเลิกแล้ว",
                        icon: res.icon || "success",
                        timer: 1400,
                        buttons: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function () {
                    $btn.prop('disabled', false);
                    swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });

    // Copy order number or parcel tracking number
    $(document).on('click', '.js-copy-text', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var text = $btn.attr('data-copy') || '';
        if (!text) return;

        function markCopied() {
            var $icon = $btn.find('i');
            $icon.removeClass('fa-clone').addClass('fa-check');
            setTimeout(function () {
                $icon.removeClass('fa-check').addClass('fa-clone');
            }, 1300);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markCopied);
        } else {
            var $tmp = $('<input>').val(text).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            markCopied();
        }
    });
});
