$(document).ready(function () {
    var status_id = 1;
    var shipping_type = 0;

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatBaht(amount) {
        var num = Number(amount || 0);
        if (isNaN(num)) num = 0;
        var hasFraction = Math.abs(num - Math.round(num)) > 0.001;
        return "฿" + num.toLocaleString("th-TH", {
            minimumFractionDigits: hasFraction ? 2 : 0,
            maximumFractionDigits: 2
        });
    }

    function formatDateTh(dateStr) {
        if (!dateStr) return "-";
        var d = new Date(dateStr.replace(" ", "T"));
        if (isNaN(d.getTime())) return escapeHtml(dateStr);
        var pad = function (n) { return n < 10 ? "0" + n : n; };
        return pad(d.getDate()) + "/" + pad(d.getMonth() + 1) + "/" + (d.getFullYear() + 543) +
            " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
    }

    function select_count() {
        $.ajax({
            type: "post",
            url: "api/orders.php",
            data: { fn: "select_count" },
            dataType: "json",
            success: function (res) {
                var c1 = (res.count1 && res.count1[0]) ? Number(res.count1[0].count_status || 0) : 0;
                var c2 = (res.count2 && res.count2[0]) ? Number(res.count2[0].count_status || 0) : 0;
                var c3 = (res.count3 && res.count3[0]) ? Number(res.count3[0].count_status || 0) : 0;
                var c4 = (res.count4 && res.count4[0]) ? Number(res.count4[0].count_status || 0) : 0;

                $(".count1").text(c1);
                $(".count2").text(c2);
                $(".count3").text(c3);
                $(".count4").text(c4);

                $("#statCount1").text(c1);
                $("#statCount2").text(c2);
                $("#statCount3").text(c3);
                $("#statCount4").text(c4);
            }
        });
    }

    function switchStatus(newStatus) {
        status_id = Number(newStatus) || 1;
        $(".choose-status").removeClass("active");
        $('.choose-status[status-id="' + status_id + '"]').addClass("active");
        $(".ord-stat-card").removeClass("is-active");
        $('.ord-stat-card[data-status-id="' + status_id + '"]').addClass("is-active");
        dataTable.ajax.reload();
    }

    var dataTable = $("#add-row").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: false,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            emptyTable: "ไม่มีรายการสั่งซื้อในสถานะนี้",
            zeroRecords: "ไม่พบคำสั่งซื้อที่ตรงกับการค้นหา",
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 รายการ"
        },
        ajax: {
            url: "api/orders.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_order";
                d.status_id = status_id;
                d.shipping_type = shipping_type;
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
                data: null,
                render: function (row) {
                    var qtyInfo = Number(row.total_qty || 0) > 0
                        ? " · " + row.total_qty + " ชิ้น"
                        : "";
                    return '<a href="?page=order-detail&order_id=' + encodeURIComponent(row.order_id) + '" class="ord-no-link">' +
                            escapeHtml(row.order_no) +
                        '</a>' +
                        '<div class="ord-sub-meta">' + formatDateTh(row.create_date) + qtyInfo + '</div>';
                }
            },
            {
                data: null,
                render: function (row) {
                    var buyer = (Number(row.user_id) !== 0 && $.trim(row.name) !== "")
                        ? row.name
                        : ($.trim(row.shipping_name) !== "" ? row.shipping_name : row.recipient_name);
                    var sub = $.trim(row.user_email) !== "" ? row.user_email : ($.trim(row.shipping_phone) || $.trim(row.recipient_phone));
                    return '<div class="ord-buyer-name">' + escapeHtml(buyer || "-") + '</div>' +
                        (sub ? '<div class="ord-sub-meta">' + escapeHtml(sub) + '</div>' : '');
                }
            },
            {
                data: null,
                className: "text-end",
                render: function (row) {
                    var shipFee = Number(row.shipping_price || 0);
                    return '<div class="ord-amount">' + formatBaht(row.total_price) + '</div>' +
                        '<div class="ord-sub-meta">' + (shipFee > 0 ? 'ค่าส่ง ' + formatBaht(shipFee) : 'ส่งฟรี') + '</div>';
                }
            },
            {
                data: null,
                render: function (row) {
                    var sid = Number(row.status_id);
                    var badge = '<span class="badge badge-secondary">-</span>';
                    if (sid === 1) badge = '<span class="badge badge-warning">รอชำระเงิน</span>';
                    else if (sid === 2) badge = '<span class="badge badge-info">ชำระแล้ว · รอจัดส่ง</span>';
                    else if (sid === 3) badge = '<span class="badge badge-success">จัดส่งแล้ว</span>';
                    else if (sid === 4) badge = '<span class="badge badge-danger">ยกเลิก</span>';

                    var extras = [];
                    if (sid === 1 && Number(row.evidence_count || 0) > 0) {
                        extras.push('<a href="?page=evidence" class="ord-mini-badge ord-mini-badge--slip"><i class="fas fa-receipt me-1"></i>แนบสลิปแล้ว</a>');
                    }
                    if (String(row.receipt_id) === "1") {
                        if ($.trim(row.receipt_link) !== "") {
                            extras.push('<span class="ord-mini-badge ord-mini-badge--receipt-done"><i class="fas fa-check me-1"></i>ออกใบเสร็จแล้ว</span>');
                        } else {
                            extras.push('<span class="ord-mini-badge ord-mini-badge--receipt"><i class="fas fa-file-alt me-1"></i>ขอใบเสร็จ</span>');
                        }
                    }

                    return badge + (extras.length ? '<div class="ord-mini-badges">' + extras.join("") + '</div>' : '');
                }
            },
            {
                data: null,
                render: function (row) {
                    var st = String(row.shipping_type || "1");
                    var html = '<div class="ord-ship-cell">';

                    if (st === "1") {
                        html += '<div class="ord-ship-head"><span class="ord-ship-type ord-ship-type--pickup"><i class="fas fa-store me-1"></i>รับหน้าร้าน</span></div>';
                        if ($.trim(row.shipping_name) !== "") {
                            html += '<div>ผู้รับ: <strong>' + escapeHtml(row.shipping_name) + '</strong></div>';
                        }
                        if ($.trim(row.shipping_department) !== "") {
                            html += '<div>หน่วยงาน: ' + escapeHtml(row.shipping_department) + '</div>';
                        }
                    } else if (st === "2") {
                        html += '<div class="ord-ship-head"><span class="ord-ship-type ord-ship-type--campus"><i class="fas fa-paper-plane me-1"></i>ส่งภายในวิทยาเขต</span></div>';
                        var parts2 = [];
                        if ($.trim(row.shipping_name) !== "") parts2.push('ผู้รับ: <strong>' + escapeHtml(row.shipping_name) + '</strong>');
                        if ($.trim(row.shipping_phone) !== "") parts2.push('โทร: ' + escapeHtml(row.shipping_phone));
                        if (parts2.length) html += '<div>' + parts2.join(' &middot; ') + '</div>';
                        if ($.trim(row.shipping_department) !== "") {
                            html += '<div>หน่วยงาน: ' + escapeHtml(row.shipping_department) + '</div>';
                        }
                    } else {
                        html += '<div class="ord-ship-head"><span class="ord-ship-type ord-ship-type--outside"><i class="fas fa-truck me-1"></i>ส่งภายนอกวิทยาเขต</span></div>';
                        var rName = Number(row.user_id) !== 0 ? $.trim(row.recipient_name) : $.trim(row.shipping_name);
                        var rPhone = Number(row.user_id) !== 0 ? $.trim(row.recipient_phone) : $.trim(row.shipping_phone);
                        var parts3 = [];
                        if (rName !== "") parts3.push('ผู้รับ: <strong>' + escapeHtml(rName) + '</strong>');
                        if (rPhone !== "") parts3.push('โทร: ' + escapeHtml(rPhone));
                        if (parts3.length) html += '<div>' + parts3.join(' &middot; ') + '</div>';

                        if (Number(row.user_id) !== 0) {
                            var addrParts = [];
                            if ($.trim(row.address_at) !== "") addrParts.push(row.address_at);
                            if ($.trim(row.subdistrict_name_in_thai) !== "") addrParts.push("ต." + row.subdistrict_name_in_thai);
                            if ($.trim(row.district_name_in_thai) !== "") addrParts.push("อ." + row.district_name_in_thai);
                            if ($.trim(row.province_name_in_thai) !== "") addrParts.push("จ." + row.province_name_in_thai);
                            if ($.trim(row.zip_code) !== "") addrParts.push(row.zip_code);
                            if (addrParts.length) {
                                html += '<div>ที่อยู่: ' + escapeHtml(addrParts.join(" ")) + '</div>';
                            }
                        } else if ($.trim(row.shipping_department) !== "") {
                            html += '<div>ที่อยู่: ' + escapeHtml(row.shipping_department) + '</div>';
                        }

                        if ($.trim(row.transported_name) !== "" || $.trim(row.parcel_number) !== "") {
                            var trackText = [];
                            if ($.trim(row.transported_name) !== "") trackText.push(escapeHtml(row.transported_name));
                            if ($.trim(row.parcel_number) !== "") trackText.push(escapeHtml(row.parcel_number));
                            html += '<div class="ord-tracking-chip"><i class="fas fa-barcode"></i><span>' + trackText.join(" · ") + '</span></div>';
                        }
                    }

                    html += '</div>';
                    return html;
                }
            },
            {
                data: null,
                className: "text-end",
                bSortable: false,
                render: function (row) {
                    var btns = '<div class="ord-actions-wrap">';
                    btns += '<a href="?page=order-detail&order_id=' + encodeURIComponent(row.order_id) + '" class="btn btn-sm btn-primary" title="ดูรายละเอียดคำสั่งซื้อ">' +
                        '<i class="fas fa-eye me-1"></i>รายละเอียด</a>';
                    if (Number(row.status_id) === 2 && String(row.shipping_type) === "3") {
                        btns += '<a href="print.php?order_id=' + encodeURIComponent(row.order_id) + '" target="_blank" class="btn btn-sm btn-outline-success" title="พิมพ์ใบปะหน้าพัสดุ">' +
                            '<i class="fas fa-print"></i></a>';
                    }
                    if (Number(row.status_id) === 4) {
                        btns += '<button type="button" class="btn btn-sm btn-outline-danger js-delete-order" ' +
                            'data-order-id="' + escapeHtml(row.order_id) + '" ' +
                            'data-order-no="' + escapeHtml(row.order_no) + '" ' +
                            'title="ลบคำสั่งซื้อที่ยกเลิก">' +
                            '<i class="fas fa-trash-alt"></i></button>';
                    }
                    btns += '</div>';
                    return btns;
                }
            }
        ]
    });

    $(".choose-status").on("click", function (e) {
        e.preventDefault();
        switchStatus($(this).attr("status-id"));
    });

    $(".ord-stat-card").on("click", function () {
        switchStatus($(this).attr("data-status-id"));
    });

    $("#shippingTypeFilter").on("change", function () {
        shipping_type = Number($(this).val() || 0);
        dataTable.ajax.reload();
    });

    $("#add-row tbody").on("click", ".js-delete-order", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var orderId = $btn.attr("data-order-id");
        var orderNo = $btn.attr("data-order-no") || "";

        Swal.fire({
            title: "ยืนยันลบคำสั่งซื้อ?",
            text: "คำสั่งซื้อหมายเลข " + orderNo + " และรายการสินค้าที่เกี่ยวข้องจะถูกลบออกจากระบบถาวร",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ลบคำสั่งซื้อ",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $btn.prop("disabled", true);
            $.ajax({
                url: "api/orders.php",
                type: "POST",
                data: {
                    fn: "delete_order",
                    order_id: orderId
                },
                dataType: "json",
                success: function (res) {
                    Swal.fire(
                        (res && res.title) || "สำเร็จ",
                        (res && res.message) || "",
                        (res && res.icon) || "success"
                    );
                    if (res && res.status === "success") {
                        dataTable.ajax.reload(null, false);
                        select_count();
                    } else {
                        $btn.prop("disabled", false);
                    }
                },
                error: function () {
                    $btn.prop("disabled", false);
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });

    select_count();
});
