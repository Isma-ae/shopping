$(document).ready(function () {
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        date.setHours(date.getHours() + 24);
        return date.toLocaleString('th-TH');
    }
    $('#search-order').click(function (e) {
        e.preventDefault();
        var order_no = $('[name="order_no"]').val();
        $.ajax({
            type: "post",
            url: "api/search-order.php",
            data: {
                order_no: order_no
            },
            dataType: "json",
            success: function (res) {
                let html = '';
                if (res.length === 0) {
                    html = '<p>ยังไม่มีคำสั่งซื้อ</p>';
                } else {
                    res.forEach(order => {
                        let ship_type = '';
                        let address_html = '';

                        if (order.shipping_type == "1") {
                            ship_type = 'รับหน้าร้าน';
                        } else if (order.shipping_type == "2") {
                            ship_type = 'ส่งภายในวิทยาเขต';
                        } else {
                            ship_type = 'ส่งภายนอกวิทยาเขต';
                            address_html = `
                                <p class="stext-116 cl8 trans-04 p-t-10">
                                    <span class="cl6">ที่อยู่:</span> ${order.address_at}
                                    ตำบล${order.subdistrict_name_in_thai}
                                    อำเภอ${order.district_name_in_thai}
                                    จังหวัด${order.province_name_in_thai}
                                    ${order.zip_code}
                                </p>`;
                        }

                        let detail_html = '';
                        order.details.forEach(item => {
                            detail_html += `
                                <li class="flex-w p-b-30 p-t-30 p-lr-40 bor18 m-t-10" style="display:flex; align-items:center;">
                                    <a href="#" class="wrao-pic-w size-214 hov-ovelay1 m-r-20">
                                        <img src="file/product/${item.img_name}" alt="PRODUCT" width="100%">
                                    </a>
                                    <div class="flex-grow-1">
                                        <a href="#" class="stext-116 cl8 hov-cl1 trans-04">${item.product_name}</a>
                                        <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                            <span><span class="cl4">สี</span> ${item.variant_color} <span class="cl12 m-l-4 m-r-6">|</span></span>
                                            <span><span class="cl4">ขนาด</span> ${item.variant_size}</span>
                                        </span>
                                        <span class="stext-116 cl6 p-t-10">x ${item.qty}</span>
                                    </div>
                                    <div style="text-align:right; min-width:80px; margin-left:auto;">
                                        <span class="stext-116 cl1">฿${item.total_price}</span>
                                    </div>
                                </li>`;
                        });
                        let showButtons = (parseInt(order.status_id) === 1);
                        let status_text = '';
                        switch (parseInt(order.status_id)) {
                            case 1:
                                status_text = 'รายการรอชำระ';
                                break;
                            case 2:
                                status_text = 'รอจัดส่ง';
                                break;
                            case 3:
                                status_text = 'จัดส่งแล้ว';
                                break;
                            default:
                                status_text = 'ยกเลิก';
                        }

                        html += `
                        <div class="card m-t-13">
                            <div class="card-header">
                                <div class="flex-w flex-sb-m">
                                    <span class="flex-w flex-m stext-111 cl2 p-r-30 m-tb-10">
                                        <span class="stext-101 cl2">${order.order_no}</span>
                                    </span>
                                    <strong class="stext-101 cl1 m-tb-10">${status_text}</strong>
                                </div>
                            </div>
                            <div class="card-body bor10">
                                <p class="stext-116 cl8 trans-04"><span class="cl6">วิธีการรับสินค้า:</span> ${ship_type}</p>
                                ${address_html}
                                <ul>${detail_html}</ul>
                            </div>
                            <div class="d-flex p-t-18 p-b-15 p-lr-40 p-lr-15-sm" style="align-items:center; justify-content: space-between;">
                                <div>
                                    <span class="stext-116 cl8 trans-04">
                                        ชำระเงินภายใน
                                        <span class="cl1">${formatDate(order.create_date)}</span>
                                        โดยQR พร้อมเพย์
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="cl1 mb-2">
                                        รวมการสั่งซื้อ: <span class="mtext-112">฿${order.total_price}</span>
                                    </div>
                                    ${showButtons ? `
                                    <div class="d-flex justify-content-end gap-2 p-t-20">
                                        <a href="payment/?order_no=${order.order_no}" class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn1 p-lr-15 m-r-10 trans-04">
                                            ชำระเงินตอนนี้
                                        </a>
                                        <a href="#" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04 cancel-order" order-id="${order.order_id}">
                                            ยกเลิกคำสั่งซื้อ
                                        </a>
                                    </div>` : ``}
                                </div>
                            </div>
                        </div>`;
                    });
                }
                $('#order-list').html(html);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            }
        });
    });

    $(document).on('click', '.cancel-order', function (e) {
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
                            icon: res.icon
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