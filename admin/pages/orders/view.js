$(document).ready(function () {


    select_count();

    function select_count() {
        $.ajax({
            type: "post",
            url: "api/orders.php",
            data: {
                fn: "select_count"
            },
            dataType: "json",
            success: function (res) {
                if (res.count1[0].count_status > 0) {
                    $('.count1').html('(' + res.count1[0].count_status + ')');
                }
                if (res.count2[0].count_status > 0) {
                    $('.count2').html('(' + res.count2[0].count_status + ')');
                }
                if (res.count3[0].count_status > 0) {
                    $('.count3').html('(' + res.count3[0].count_status + ')');
                }
                if (condition) {
                    $('.count4').html('(' + res.count4[0].count_status + ')');
                }
            }
        });
    }

    // ดึงค่าเริ่มต้นของ status_id
    let status_id = $('.choose-status.active').attr('status-id');

    // คลิกเปลี่ยนสถานะ
    $('.choose-status').click(function (e) {
        e.preventDefault();

        // เปลี่ยน active ให้ลิงก์ที่คลิก
        $('.choose-status').removeClass('active');
        $(this).addClass('active');

        // อัปเดตค่า status_id ใหม่
        status_id = $(this).attr('status-id');

        // โหลดข้อมูลใหม่ใน DataTable
        dataTable.ajax.reload();
    });

    // สร้าง DataTable
    var dataTable = $("#add-row").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/orders.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_order";
                d.status_id = status_id; // ✅ ส่งค่าตัวแปรจริง
            }
        },
        "columns": [{
                "data": function (row, type, val, meta) {
                    return meta.row + 1;
                }
            },
            {
                "data": "order_no"
            },
            {
                "data": "name"
            },
            {
                "data": "total_price"
            },
            {
                "data": function (row) {
                    if (row.status_id == '1') return '<span style="color:orange;">รอชำระเงิน</span>';
                    else if (row.status_id == '2') return '<span style="color:orange;">ชำระเงินแล้ว รอจัดส่ง</span>';
                    else if (row.status_id == '3') return 'จัดส่งแล้ว';
                    else return 'ยกเลิก';
                }
            },
            {
                "data": function (row) {
                    if (row.shipping_type == '1') {
                        return 'รับหน้าร้าน<br>ผู้รับ: ' + row.shipping_name;
                    } else if (row.shipping_type == '2') {
                        return 'ส่งภายในวิทยาเขต<br>ผู้รับ: ' + row.shipping_name + ' | โทร:' + row.shipping_phone + '<br>หน่วยงาน' + row.shipping_department;
                    } else {
                        return 'ส่งภายนอกวิทยาเขต<br>ที่อยู่: ' + row.address_at + ' ตำบล' + row.subdistrict_name_in_thai + ' อำเภอ' + row.district_name_in_thai + ' จังหวัด' + row.province_name_in_thai + ' ' + row.zip_code + '<br>จัดส่งโดย: ' + row.transported_name + ' | หมายเลขพัสดุ' + row.parcel_number;
                    }
                }
            },
            {
                "data": function (row) {
                    return '<a href="?page=order-detail&order_id=' + row.order_id + '" class="btn btn-outline-primary btn-sm"><i class="fas fa-table"></i></a>';
                },
                "bSortable": false
            }
        ]
    });

});