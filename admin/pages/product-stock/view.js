$(document).ready(function () {
    if (!$("#productTable").length) return;

    var currentStockFilter = "all";
    var currentColorFilter = "";
    var LOW_STOCK_THRESHOLD = 5;

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatPrice(val) {
        var num = Number(val);
        if (!isFinite(num)) return "-";
        var hasDec = Math.abs(num - Math.round(num)) > 0.001;
        return "฿" + num.toLocaleString("th-TH", {
            minimumFractionDigits: hasDec ? 2 : 0,
            maximumFractionDigits: 2
        });
    }

    function formatInt(val) {
        var num = parseInt(val, 10);
        if (isNaN(num)) num = 0;
        return num.toLocaleString("th-TH");
    }

    function getRowData($el) {
        var $tr = $el.closest("tr");
        if ($tr.hasClass("child")) {
            $tr = $tr.prev();
        }
        return dataTable.row($tr).data();
    }

    function getThumbUrl(row) {
        if (row.img_name && String(row.img_name).trim() !== "") {
            return "../file/product/" + encodeURIComponent(row.img_name);
        }
        if (row.variant_img && String(row.variant_img).trim() !== "") {
            return String(row.variant_img).trim();
        }
        return "";
    }

    function updateSummaryAndFilters(rows) {
        var list = Array.isArray(rows) ? rows : [];
        var totalVariants = list.length;
        var sumTotalStock = 0;
        var sumOrdered = 0;
        var sumRemaining = 0;
        var cntInstock = 0;
        var cntLow = 0;
        var cntOut = 0;
        var colorSet = {};

        list.forEach(function (r) {
            var total = parseInt(r.total_stock, 10) || 0;
            var ordered = parseInt(r.total_ordered, 10) || 0;
            var rem = parseInt(r.remaining_stock, 10) || 0;

            sumTotalStock += total;
            sumOrdered += ordered;
            sumRemaining += rem;

            if (rem <= 0) {
                cntOut++;
            } else {
                cntInstock++;
                if (rem <= LOW_STOCK_THRESHOLD) {
                    cntLow++;
                }
            }

            var colorName = (r.variant_color || "").trim();
            if (colorName !== "") {
                colorSet[colorName] = true;
            }
        });

        $("#stat-variants").text(formatInt(totalVariants));
        $("#stat-remaining").text(formatInt(sumRemaining) + " ชิ้น");
        $("#stat-stock-breakdown").text("ตั้งต้น " + formatInt(sumTotalStock) + " · ขายแล้ว " + formatInt(sumOrdered));
        $("#stat-lowstock").text(formatInt(cntLow));
        $("#stat-outstock").text(formatInt(cntOut));

        $("#tab-cnt-all").text(formatInt(totalVariants));
        $("#tab-cnt-instock").text(formatInt(cntInstock));
        $("#tab-cnt-lowstock").text(formatInt(cntLow));
        $("#tab-cnt-outstock").text(formatInt(cntOut));

        // Rebuild color dropdown while preserving current selection
        var colors = Object.keys(colorSet).sort();
        var $sel = $("#filter-color");
        var prevVal = currentColorFilter;
        $sel.empty().append('<option value="">ทุกสี</option>');
        colors.forEach(function (c) {
            $sel.append($("<option></option>").val(c).text(c));
        });
        if (prevVal && colorSet[prevVal]) {
            $sel.val(prevVal);
        } else {
            currentColorFilter = "";
            $sel.val("");
        }
    }

    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
        if (settings.nTable.id !== "productTable") return true;
        var row = dataTable.row(dataIndex).data();
        if (!row) return true;

        var rowColor = (row.variant_color || "").trim();
        if (currentColorFilter !== "" && rowColor !== currentColorFilter) {
            return false;
        }

        var rem = parseInt(row.remaining_stock, 10) || 0;
        if (currentStockFilter === "instock") return rem > 0;
        if (currentStockFilter === "lowstock") return rem > 0 && rem <= LOW_STOCK_THRESHOLD;
        if (currentStockFilter === "outstock") return rem <= 0;
        return true;
    });

    var dataTable = $("#productTable").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        pageLength: 25,
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ยังไม่มีตัวเลือกสินค้า",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
            zeroRecords: "ไม่พบตัวเลือกสินค้าที่ตรงกับเงื่อนไข",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        },
        ajax: {
            url: "api/product-stock.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_stock";
                d.product_id = $("#product_id").val();
            },
            dataSrc: function (json) {
                var rows = (json && Array.isArray(json.data)) ? json.data : [];
                updateSummaryAndFilters(rows);
                return rows;
            }
        },
        columns: [
            {
                data: null,
                className: "text-center text-muted",
                orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: "variant_style",
                render: function (val, type, row) {
                    var styleText = (row.variant_style || "").trim();
                    var apiCode = (row.product_api_id || "").trim();
                    if (type === "sort" || type === "filter") {
                        return styleText + " " + apiCode + " " + (row.variant_id || "");
                    }

                    var thumbUrl = getThumbUrl(row);
                    var thumbHtml = "";
                    if (thumbUrl) {
                        thumbHtml = '<button type="button" class="ps-thumb-btn js-preview-vimg" title="คลิกเพื่อดูรูปขนาดใหญ่">' +
                            '<img src="' + escapeHtml(thumbUrl) + '" alt="' + escapeHtml(styleText || row.variant_color || "สินค้า") + '" loading="lazy">' +
                            '</button>';
                    } else {
                        thumbHtml = '<div class="ps-thumb-empty" title="ยังไม่ได้ผูกภาพ"><i class="fas fa-image"></i></div>';
                    }

                    var titleHtml = '<div class="ps-style-name">' + escapeHtml(styleText || "รูปแบบมาตรฐาน") + '</div>';
                    var codeHtml = apiCode
                        ? '<span class="ps-code-chip">' + escapeHtml(apiCode) + '</span>'
                        : '<span class="ps-code-chip">VID #' + escapeHtml(row.variant_id) + '</span>';

                    return '<div class="ps-item-cell">' +
                        thumbHtml +
                        '<div>' + titleHtml + codeHtml + '</div>' +
                        '</div>';
                }
            },
            {
                data: "variant_color",
                render: function (val, type) {
                    var c = (val || "").trim();
                    if (type === "sort" || type === "filter") return c;
                    if (!c) return '<span class="text-muted">-</span>';
                    return '<span class="ps-color-pill">' + escapeHtml(c) + '</span>';
                }
            },
            {
                data: "variant_size",
                render: function (val, type, row) {
                    var sz = (row.variant_size || "").trim();
                    var detail = (row.size_detail || "").trim();
                    if (type === "sort") {
                        var ord = parseInt(row.size_order, 10);
                        return (isNaN(ord) ? 9 : ord) + "_" + sz;
                    }
                    if (type === "filter") return sz + " " + detail;

                    if (!sz && !detail) return '<span class="text-muted">-</span>';
                    var html = '<div class="ps-size-main">' + escapeHtml(sz || "-") + '</div>';
                    if (detail) {
                        html += '<div class="ps-size-detail">' + escapeHtml(detail) + '</div>';
                    }
                    return html;
                }
            },
            {
                data: "variant_price",
                className: "text-end",
                render: function (val, type) {
                    var num = Number(val) || 0;
                    if (type === "sort") return num;
                    return '<span class="ps-price-cost">' + formatPrice(num) + '</span>';
                }
            },
            {
                data: "variant_sale",
                className: "text-end",
                render: function (val, type, row) {
                    var sale = Number(row.variant_sale) || 0;
                    var cost = Number(row.variant_price) || 0;
                    if (type === "sort") return sale;

                    var html = '<div class="ps-price-sale">' + formatPrice(sale) + '</div>';
                    if (cost > 0 && sale > 0) {
                        var diff = sale - cost;
                        var sign = diff >= 0 ? "+" : "";
                        var cls = diff < 0 ? " is-loss" : "";
                        html += '<div class="ps-profit-sub' + cls + '">' + sign + formatPrice(diff) + '</div>';
                    }
                    return html;
                }
            },
            {
                data: "remaining_stock",
                className: "text-center",
                render: function (val, type, row) {
                    var rem = parseInt(row.remaining_stock, 10) || 0;
                    var total = parseInt(row.total_stock, 10) || 0;
                    var ordered = parseInt(row.total_ordered, 10) || 0;
                    if (type === "sort") return rem;

                    var cls = "is-ok";
                    if (rem <= 0) cls = "is-out";
                    else if (rem <= LOW_STOCK_THRESHOLD) cls = "is-low";

                    var label = rem <= 0 ? "หมด (" + formatInt(rem) + ")" : formatInt(rem) + " ชิ้น";
                    return '<div>' +
                        '<span class="ps-stock-pill ' + cls + '">' + label + '</span>' +
                        '<div class="ps-stock-sub">ขาย ' + formatInt(ordered) + ' / ตั้งต้น ' + formatInt(total) + '</div>' +
                        '</div>';
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    var orderRef = parseInt(row.order_ref_count, 10) || 0;
                    var delClass = orderRef > 0 ? "btn-outline-secondary ps-btn-locked" : "btn-outline-danger";
                    var delTitle = orderRef > 0
                        ? "ไม่สามารถลบได้ (มีคำสั่งซื้อ " + orderRef + " รายการอ้างอิง)"
                        : "ลบตัวเลือกสินค้า";

                    return '<div class="ps-actions-wrap">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm edit_variant" title="แก้ไขตัวเลือกและสต็อก">' +
                        '<i class="fas fa-edit"></i></button>' +
                        '<button type="button" class="btn ' + delClass + ' btn-sm delete_variant" title="' + escapeHtml(delTitle) + '">' +
                        '<i class="fas fa-trash"></i></button>' +
                        '</div>';
                }
            }
        ]
    });

    function setStockFilter(filterVal) {
        currentStockFilter = filterVal || "all";
        $(".ps-stat-card").removeClass("is-active");
        $('.ps-stat-card[data-filter="' + currentStockFilter + '"]').addClass("is-active");
        $("#ps-status-tabs .nav-link").removeClass("active");
        $('#ps-status-tabs .nav-link[data-filter="' + currentStockFilter + '"]').addClass("active");
        dataTable.draw();
    }

    $(".ps-stat-card").on("click", function () {
        setStockFilter($(this).data("filter"));
    });

    $("#ps-status-tabs").on("click", ".nav-link", function () {
        setStockFilter($(this).data("filter"));
    });

    $("#filter-color").on("change", function () {
        currentColorFilter = $(this).val() || "";
        dataTable.draw();
    });

    function updateLiveStockCalc() {
        var total = parseInt($("#variant_stock").val(), 10);
        if (isNaN(total)) total = 0;
        var ordered = parseInt($("#modal_total_ordered").val(), 10) || 0;
        var rem = total - ordered;

        $("#calc-total").text(formatInt(total) + " ชิ้น");
        $("#calc-ordered").text(formatInt(ordered) + " ชิ้น");
        $("#calc-remaining").text(formatInt(rem) + " ชิ้น");

        var $resItem = $("#ps-stock-calc .is-result");
        $resItem.removeClass("is-low is-out");
        if (rem <= 0) $resItem.addClass("is-out");
        else if (rem <= LOW_STOCK_THRESHOLD) $resItem.addClass("is-low");

        $("#calc-warning").toggleClass("d-none", rem >= 0);
    }

    $("#variant_stock").on("input change", updateLiveStockCalc);

    // Lightbox image preview
    $("#productTable tbody").on("click", ".js-preview-vimg", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;
        var url = getThumbUrl(data);
        if (!url) return;

        var titleParts = [data.variant_style, data.variant_color, data.variant_size].filter(Boolean);
        $("#vimg-preview-title").text(titleParts.join(" · ") || "รูปภาพตัวเลือกสินค้า");
        $("#vimg-preview-src").attr("src", url).attr("alt", titleParts.join(" "));
        $("#variantImgModal").modal("show");
    });

    // Open Edit modal
    $("#productTable tbody").on("click", ".edit_variant", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;

        $("#variant_id").val(data.variant_id || "");
        var imgIdVal = data.img_id ? String(data.img_id) : "0";
        $('input[name="img_id"]').prop("checked", false);
        var $imgRadio = $('input[name="img_id"][value="' + imgIdVal + '"]');
        if ($imgRadio.length) {
            $imgRadio.prop("checked", true);
        } else {
            $('input[name="img_id"][value="0"]').prop("checked", true);
        }

        var metaParts = [];
        if (data.product_api_id) metaParts.push("รหัส " + data.product_api_id);
        if (data.variant_color) metaParts.push("สี " + data.variant_color);
        if (data.variant_size) metaParts.push("ขนาด " + data.variant_size);
        $("#modal-variant-meta").text(metaParts.join(" · "));

        $("#variant_style").val(data.variant_style || "");
        $("#variant_color").val(data.variant_color || "");
        $("#variant_size").val(data.variant_size || "");
        $("#size_detail").val(data.size_detail || "");
        $("#variant_price").val(data.variant_price !== null && data.variant_price !== undefined ? data.variant_price : 0);
        $("#variant_sale").val(data.variant_sale !== null && data.variant_sale !== undefined ? data.variant_sale : 0);
        $("#variant_stock").val(data.variant_stock !== null && data.variant_stock !== undefined ? data.variant_stock : 0);
        $("#modal_total_ordered").val(parseInt(data.total_ordered, 10) || 0);
        $("#sync_color_img").prop("checked", true);

        updateLiveStockCalc();
        $("#variantModal").modal("show");
    });

    $("#variantModal").on("hidden.bs.modal", function () {
        $('input[name="img_id"]').prop("checked", false);
    });

    $("#edit-variant").on("click", function (e) {
        e.preventDefault();
        $("#form-variant").trigger("submit");
    });

    $("#form-variant").on("submit", function (e) {
        e.preventDefault();

        var price = Number($("#variant_price").val());
        var sale = Number($("#variant_sale").val());
        var stock = Number($("#variant_stock").val());

        if (!isFinite(price) || price < 0 || !isFinite(sale) || sale < 0 || !isFinite(stock) || stock < 0) {
            Swal.fire("ข้อมูลไม่ถูกต้อง", "กรุณากรอกราคาทุน ราคาขาย และสต็อกเป็นตัวเลขที่ไม่ติดลบ", "warning");
            return;
        }

        var $btn = $("#edit-variant");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i>กำลังบันทึก...');

        var formData = new FormData(this);
        if ($('input[name="img_id"][type="radio"]').length > 0 && $('input[name="img_id"]:checked').length === 0) {
            formData.append("img_id", 0);
        }

        $.ajax({
            url: "api/product-stock.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                var isOk = res && (res.status === "success" || res.icon === "success");
                Swal.fire(
                    (res && res.title) || (isOk ? "สำเร็จ" : "ไม่สำเร็จ"),
                    (res && res.message) || "",
                    (res && res.icon) || (isOk ? "success" : "error")
                ).then(function () {
                    if (isOk) {
                        $("#variantModal").modal("hide");
                        dataTable.ajax.reload(null, false);
                    }
                });
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
            },
            complete: function () {
                $btn.prop("disabled", false).html(origHtml);
            }
        });
    });

    // Delete variant
    $("#productTable tbody").on("click", ".delete_variant", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;

        var label = [data.variant_style, data.variant_color, data.variant_size].filter(Boolean).join(" / ") || ("ตัวเลือก #" + data.variant_id);
        var orderRef = parseInt(data.order_ref_count, 10) || 0;

        if (orderRef > 0) {
            Swal.fire({
                title: "ไม่สามารถลบตัวเลือกได้",
                html: 'ตัวเลือก <strong>' + escapeHtml(label) + '</strong> มีคำสั่งซื้ออ้างอิงอยู่ <strong>' + formatInt(orderRef) + '</strong> รายการ<br><small class="text-muted">แนะนำให้กดแก้ไขแล้วปรับสต็อกตั้งต้นให้เท่ากับยอดที่ขายแล้ว (' + formatInt(data.total_ordered) + ' ชิ้น) เพื่อให้คงเหลือเป็น 0 แทนการลบ</small>',
                icon: "warning",
                confirmButtonText: "เข้าใจแล้ว"
            });
            return;
        }

        Swal.fire({
            title: "ยืนยันการลบตัวเลือกสินค้า?",
            html: 'ต้องการลบ <strong>' + escapeHtml(label) + '</strong> ใช่หรือไม่?<br><small class="text-danger">หากลบแล้วจะไม่สามารถกู้คืนได้</small>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            confirmButtonText: "ใช่ ลบเลย",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: "POST",
                url: "api/product-stock.php",
                dataType: "json",
                data: {
                    fn: "delete_variant",
                    variant_id: data.variant_id
                },
                success: function (res) {
                    var isOk = res && (res.status === "success" || res.icon === "success");
                    Swal.fire(
                        (res && res.title) || (isOk ? "สำเร็จ" : "ไม่สำเร็จ"),
                        (res && res.message) || "",
                        (res && res.icon) || (isOk ? "success" : "error")
                    ).then(function () {
                        if (isOk) {
                            dataTable.ajax.reload(null, false);
                        }
                    });
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
                }
            });
        });
    });
});
