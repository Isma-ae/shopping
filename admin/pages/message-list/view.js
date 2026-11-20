$(document).ready(function () {
    var currentFilter = "all";

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatDateTh(dateStr) {
        if (!dateStr) return "-";
        var d = new Date(dateStr.replace(" ", "T"));
        if (isNaN(d.getTime())) return escapeHtml(dateStr);
        var pad = function (n) { return n < 10 ? "0" + n : n; };
        return pad(d.getDate()) + "/" + pad(d.getMonth() + 1) + "/" + (d.getFullYear() + 543) +
            " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
    }

    var dataTable = $("#message-table").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: false,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            emptyTable: "ยังไม่มีข้อความติดต่อจากลูกค้า",
            zeroRecords: "ไม่พบข้อความที่ตรงกับการค้นหา",
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 รายการ"
        },
        ajax: {
            url: "api/message_list.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_message";
                d.filter = currentFilter;
            },
            dataSrc: function (json) {
                if (json && json.counts) {
                    $("#msgStatTotal").text(json.counts.total || 0);
                    $("#msgStatUnread").text(json.counts.unread || 0);
                    $("#msgStatUnreplied").text(json.counts.unreplied || 0);
                    $("#msgStatReplied").text(json.counts.replied || 0);
                }
                return json && Array.isArray(json.data) ? json.data : [];
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: "email",
                render: function (data) {
                    return '<div class="msg-email-cell"><i class="fas fa-envelope text-muted me-1"></i>' + escapeHtml(data) + '</div>';
                }
            },
            {
                data: "msg",
                render: function (data) {
                    return '<div class="msg-preview-text">' + escapeHtml(data) + '</div>';
                }
            },
            {
                data: "send_date",
                render: function (data) {
                    return '<span class="msg-date-cell">' + formatDateTh(data) + '</span>';
                }
            },
            {
                data: null,
                render: function (row) {
                    var hasReply = row.message_reply && $.trim(row.message_reply) !== "";
                    var isUnread = String(row.read) === "0" || row.read === 0;
                    if (hasReply) {
                        return '<span class="badge badge-success">ตอบกลับแล้ว</span>';
                    }
                    if (isUnread) {
                        return '<span class="badge badge-danger">ยังไม่อ่าน</span>';
                    }
                    return '<span class="badge badge-warning">รอตอบกลับ</span>';
                }
            },
            {
                data: null,
                className: "text-end",
                render: function (row) {
                    var id = escapeHtml(row.message_id);
                    return '<div class="msg-actions-wrap">' +
                        '<button type="button" class="btn btn-sm btn-primary js-open-msg" data-id="' + id + '">' +
                            '<i class="fas fa-reply me-1"></i>อ่าน/ตอบ' +
                        '</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger js-delete-msg" data-id="' + id + '" title="ลบข้อความ">' +
                            '<i class="fas fa-trash-alt"></i>' +
                        '</button>' +
                    '</div>';
                }
            }
        ],
        createdRow: function (row, data) {
            if (String(data.read) === "0" || data.read === 0) {
                $(row).addClass("unread-row");
            }
        }
    });

    function openMessage(messageId) {
        $.ajax({
            url: "api/message_list.php",
            type: "POST",
            data: {
                fn: "change_read",
                message_id: messageId
            },
            dataType: "json",
            complete: function () {
                window.location.href = "?page=message&message_id=" + encodeURIComponent(messageId);
            }
        });
    }

    $(".js-msg-filter").on("click", function (e) {
        e.preventDefault();
        $(".js-msg-filter").removeClass("active");
        $(this).addClass("active");
        currentFilter = $(this).data("filter");
        dataTable.ajax.reload();
    });

    $("#message-table tbody").on("click", ".js-open-msg", function (e) {
        e.stopPropagation();
        openMessage($(this).data("id"));
    });

    $("#message-table tbody").on("click", "tr", function (e) {
        if ($(e.target).closest("button, a").length) return;
        var data = dataTable.row(this).data();
        if (!data) return;
        openMessage(data.message_id);
    });

    $("#message-table tbody").on("click", ".js-delete-msg", function (e) {
        e.stopPropagation();
        var messageId = $(this).attr("data-id");

        Swal.fire({
            title: "ยืนยันการลบข้อความ?",
            text: "ข้อความนี้จะถูกลบออกจากระบบถาวร",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ลบข้อความ",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "api/message_list.php",
                type: "POST",
                data: {
                    fn: "delete_message",
                    message_id: messageId
                },
                dataType: "json",
                success: function (res) {
                    Swal.fire(res.title || "สำเร็จ", res.message || "", res.icon || "success");
                    dataTable.ajax.reload(null, false);
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });
});
