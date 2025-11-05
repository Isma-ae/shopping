$(document).ready(function () {
    var dataTable = $("#message-table").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "api/message_list.php",
            "type": "POST",
            "data": function (d) {
                d.fn = "select_message";
            }
        },
        "columns": [{
            "data": function (row, type, val, meta) {
                return row.email + '<br>' + row.msg;

            },
            "bSortable": false
        }],
        "createdRow": function (row, data, dataIndex) {
            if (data.read == 0 || data.read === '0') {
                // แนะนำ: ใส่ class แทนการตั้ง style ตรง ๆ
                $(row).addClass('unread-row');
            }
        }
    });

    tinymce.init({
        selector: 'textarea#product_detail',
        height: 300,
        promotion: false,
        statusbar: false
    });


    $('#message-table tbody').on('click', 'tr', function () {
        var data = dataTable.row(this).data(); // ดึงข้อมูลของแถวนี้
        if (!data) return;
        $.ajax({
            url: "api/message_list.php",
            type: "POST",
            data: {
                fn: "change_read",
                message_id: data.message_id
            },
            dataType: "json",
            success: function (res) {
                if (res.status == 'success') {
                    window.location.href = "?page=message&message_id=" + data.message_id;
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });
});