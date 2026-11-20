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

    function renderStatusBadge(row) {
        if (!row.order_id) {
            return '<span class="badge badge-secondary">ไม่พบคำสั่งซื้อ</span>';
        }
        var sid = parseInt(row.status_id, 10);
        if (sid === 1) return '<span class="badge badge-warning">รอชำระเงิน</span>';
        if (sid === 2) return '<span class="badge badge-info">รอจัดส่ง (ชำระแล้ว)</span>';
        if (sid === 3) return '<span class="badge badge-success">จัดส่งแล้ว</span>';
        if (sid === 4) return '<span class="badge badge-danger">ยกเลิก</span>';
        return '<span class="badge badge-secondary">' + escapeHtml(row.order_status || "-") + '</span>';
    }

    var dataTable = $("#evidence-table").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: false,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            emptyTable: "ยังไม่มีหลักฐานการชำระเงินจากลูกค้า",
            zeroRecords: "ไม่พบรายการที่ตรงกับการค้นหา",
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 รายการ"
        },
        ajax: {
            url: "api/evidence.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_evidence";
                d.filter = currentFilter;
            },
            dataSrc: function (json) {
                if (json && json.counts) {
                    $("#evStatTotal").text(json.counts.total || 0);
                    $("#evStatPending").text(json.counts.pending || 0);
                    $("#evStatVerified").text(json.counts.verified || 0);
                    $("#evStatOther").text(json.counts.other || 0);
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
                data: "evidence_slip",
                render: function (slip) {
                    var imgUrl = "../file/evidence/" + encodeURIComponent(slip || "");
                    return '<button type="button" class="ev-thumb-btn js-view-slip" title="คลิกเพื่อดูสลิปขนาดใหญ่">' +
                        '<img src="' + imgUrl + '" alt="Slip" onerror="this.onerror=null;this.style.display=\'none\';this.parentNode.innerHTML=\'<i class=\\\'fas fa-file-image text-muted\\\'></i>\';">' +
                    '</button>';
                }
            },
            {
                data: null,
                render: function (row) {
                    var orderNo = escapeHtml(row.order_no || "-");
                    if (row.order_id) {
                        return '<a href="?page=order-detail&order_id=' + encodeURIComponent(row.order_id) + '" class="ev-order-link">' + orderNo + '</a>' +
                            '<div class="small text-muted">' + formatDateTh(row.create_date) + '</div>';
                    }
                    return '<span class="fw-bold text-muted">' + orderNo + '</span>';
                }
            },
            {
                data: null,
                render: function (row) {
                    var name = escapeHtml(row.buyer_name || "-");
                    var contact = [];
                    if (row.buyer_phone) contact.push(escapeHtml(row.buyer_phone));
                    if (row.buyer_email) contact.push(escapeHtml(row.buyer_email));
                    return '<div class="fw-semibold">' + name + '</div>' +
                        (contact.length ? '<div class="small text-muted">' + contact.join(" · ") + '</div>' : '');
                }
            },
            {
                data: null,
                className: "text-end fw-bold",
                render: function (row) {
                    return row.order_id ? formatBaht(row.total_price) : "-";
                }
            },
            {
                data: null,
                render: function (row) {
                    return renderStatusBadge(row);
                }
            },
            {
                data: null,
                className: "text-end",
                render: function (row) {
                    var isPending = row.order_id && parseInt(row.status_id, 10) === 1;
                    var html = '<div class="ev-actions-wrap">';
                    html += '<button type="button" class="btn btn-sm btn-primary js-view-slip">' +
                        '<i class="fas fa-eye me-1"></i>ดูสลิป</button>';
                    if (isPending) {
                        html += '<button type="button" class="btn btn-sm btn-success js-approve-payment" data-order-id="' + escapeHtml(row.order_id) + '" data-order-no="' + escapeHtml(row.order_no) + '" title="ยืนยันการชำระเงิน">' +
                            '<i class="fas fa-check"></i></button>';
                    }
                    html += '<button type="button" class="btn btn-sm btn-outline-danger js-delete-ev" data-id="' + escapeHtml(row.evidence_id) + '" title="ลบหลักฐาน">' +
                        '<i class="fas fa-trash-alt"></i></button>';
                    html += '</div>';
                    return html;
                }
            }
        ]
    });

    function openSlipModal(row) {
        if (!row) return;
        var slipUrl = "../file/evidence/" + encodeURIComponent(row.evidence_slip || "");
        var $img = $("#modalSlipImg");
        var $missing = $("#modalSlipMissing");

        $missing.addClass("d-none");
        $img.removeClass("d-none").off("error").on("error", function () {
            $img.addClass("d-none");
            $missing.removeClass("d-none");
            $("#modalSlipFilename").text(row.evidence_slip || "");
        }).attr("src", slipUrl);

        $("#modalSlipOpenLink").attr("href", slipUrl);
        $("#modalOrderNo").text(row.order_no || "-");
        $("#modalBuyerName").text(row.buyer_name || "-");

        var contacts = [];
        if (row.buyer_phone) contacts.push(row.buyer_phone);
        if (row.buyer_email) contacts.push(row.buyer_email);
        $("#modalBuyerContact").text(contacts.length ? contacts.join(" / ") : "-");
        $("#modalOrderDate").text(formatDateTh(row.create_date));
        $("#modalTotalPrice").text(row.order_id ? formatBaht(row.total_price) : "-");
        $("#modalOrderStatus").html(renderStatusBadge(row));

        if (row.order_id && parseInt(row.status_id, 10) === 1) {
            $("#modalApproveBtn")
                .removeClass("d-none")
                .attr("data-order-id", row.order_id)
                .attr("data-order-no", row.order_no || "");
        } else {
            $("#modalApproveBtn").addClass("d-none").attr("data-order-id", "");
        }

        if (row.order_id) {
            $("#modalOrderDetailLink")
                .removeClass("d-none")
                .attr("href", "?page=order-detail&order_id=" + encodeURIComponent(row.order_id));
        } else {
            $("#modalOrderDetailLink").addClass("d-none");
        }

        var modalEl = document.getElementById("slipModal");
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function approveOrderPayment(orderId, orderNo, closeModal) {
        Swal.fire({
            title: "ยืนยันการชำระเงิน?",
            text: "เปลี่ยนสถานะคำสั่งซื้อ " + (orderNo || "") + " เป็น \"รอจัดส่ง\" ใช่หรือไม่",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#198754",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ยืนยันชำระเงิน",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "api/evidence.php",
                type: "POST",
                data: {
                    fn: "approve_payment",
                    order_id: orderId
                },
                dataType: "json",
                success: function (res) {
                    Swal.fire(res.title || "สำเร็จ", res.message || "", res.icon || "success");
                    if (res.status === "success") {
                        if (closeModal) {
                            var modalEl = document.getElementById("slipModal");
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        dataTable.ajax.reload(null, false);
                    }
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    }

    $(".js-ev-filter").on("click", function (e) {
        e.preventDefault();
        $(".js-ev-filter").removeClass("active");
        $(this).addClass("active");
        currentFilter = $(this).data("filter");
        dataTable.ajax.reload();
    });

    $("#evidence-table tbody").on("click", ".js-view-slip", function (e) {
        e.preventDefault();
        var row = dataTable.row($(this).closest("tr")).data();
        openSlipModal(row);
    });

    $("#evidence-table tbody").on("click", ".js-approve-payment", function (e) {
        e.preventDefault();
        approveOrderPayment($(this).attr("data-order-id"), $(this).attr("data-order-no"), false);
    });

    $("#modalApproveBtn").on("click", function (e) {
        e.preventDefault();
        approveOrderPayment($(this).attr("data-order-id"), $(this).attr("data-order-no"), true);
    });

    $("#evidence-table tbody").on("click", ".js-delete-ev", function (e) {
        e.preventDefault();
        var evId = $(this).attr("data-id");

        Swal.fire({
            title: "ยืนยันการลบหลักฐาน?",
            text: "รายการหลักฐานการชำระเงินนี้จะถูกลบออกจากระบบ",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ลบหลักฐาน",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "api/evidence.php",
                type: "POST",
                data: {
                    fn: "delete_evidence",
                    evidence_id: evId
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
