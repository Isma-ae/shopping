$(document).ready(function () {
    $('#cancel-order').click(function (e) {
        e.preventDefault();
        var order_id = $(this).attr('order-id');
        swal({
            title: "ยืนยันยกเลิก?",
            text: "คุณต้องการยกเลิกคำสั่งซื้อนี้ใช่หรือไม่",
            icon: "warning",
            buttons: ["ไม่ยกเลิก", "ยกเลิก"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
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
                            title: res.title,
                            text: res.msg,
                            icon: res.icon,
                            timer: 1500,
                            buttons: false
                        });

                        location.reload();
                    },
                    error: function () {
                        swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                    }
                });
            }
        });
    });
});