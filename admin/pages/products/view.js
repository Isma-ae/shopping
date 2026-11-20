$(document).ready(function () {
    var currentStatusFilter = "all";
    var currentCategoryFilter = "";
    var LOW_STOCK_THRESHOLD = 5;

    var Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1600,
        timerProgressBar: true
    });

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
        if (!isFinite(num) || num <= 0) return "-";
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

    function updateSummary(rows) {
        var list = Array.isArray(rows) ? rows : [];
        var total = list.length;
        var active = 0;
        var pending = 0;
        var lowstock = 0;

        list.forEach(function (r) {
            var st = parseInt(r.product_status, 10);
            var rem = parseInt(r.remaining_stock, 10) || 0;
            if (st === 2) {
                active++;
            } else {
                pending++;
            }
            if (rem <= LOW_STOCK_THRESHOLD) {
                lowstock++;
            }
        });

        $("#stat-total").text(formatInt(total));
        $("#stat-active").text(formatInt(active));
        $("#stat-pending").text(formatInt(pending));
        $("#stat-lowstock").text(formatInt(lowstock));

        $("#tab-cnt-all").text(formatInt(total));
        $("#tab-cnt-active").text(formatInt(active));
        $("#tab-cnt-pending").text(formatInt(pending));
        $("#tab-cnt-lowstock").text(formatInt(lowstock));
    }

    // Custom filter combining Status/KPI + Category
    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
        if (settings.nTable.id !== "add-row") return true;
        var row = dataTable.row(dataIndex).data();
        if (!row) return true;

        if (currentCategoryFilter !== "" && String(row.category_id) !== String(currentCategoryFilter)) {
            return false;
        }

        var st = parseInt(row.product_status, 10);
        var rem = parseInt(row.remaining_stock, 10) || 0;

        if (currentStatusFilter === "active") return st === 2;
        if (currentStatusFilter === "pending") return st !== 2;
        if (currentStatusFilter === "lowstock") return rem <= LOW_STOCK_THRESHOLD;
        return true;
    });

    var dataTable = $("#add-row").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        pageLength: 10,
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ไม่มีรายการสินค้า",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
            zeroRecords: "ไม่พบสินค้าที่ตรงกับเงื่อนไข",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        },
        ajax: {
            url: "api/products.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_product";
            },
            dataSrc: function (json) {
                var rows = (json && Array.isArray(json.data)) ? json.data : [];
                updateSummary(rows);
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
                data: "product_name",
                render: function (val, type, row) {
                    var safeName = escapeHtml(row.product_name || "");
                    var safeId = escapeHtml(row.product_id || "");
                    if (type === "sort" || type === "filter") {
                        return (row.product_name || "") + " " + (row.product_id || "");
                    }

                    var thumbHtml = "";
                    if (row.img_name) {
                        var safeImg = encodeURIComponent(row.img_name);
                        thumbHtml = '<button type="button" class="prd-thumb-btn js-preview-img" title="คลิกเพื่อดูรูปขนาดใหญ่">' +
                            '<img src="../file/product/' + safeImg + '" alt="' + safeName + '" loading="lazy">' +
                            '</button>';
                    } else {
                        thumbHtml = '<div class="prd-thumb-empty" title="ยังไม่มีรูปภาพ"><i class="fas fa-image"></i></div>';
                    }

                    var vCount = parseInt(row.variant_count, 10) || 0;
                    var metaHtml = '<div class="prd-meta-row">' +
                        '<span class="prd-code-chip">#' + safeId + '</span>' +
                        '<span class="prd-variant-chip">' + formatInt(vCount) + ' ตัวเลือก</span>';

                    if (parseInt(row.reserve_id, 10) === 1) {
                        metaHtml += '<span class="prd-preorder-chip"><i class="fas fa-bookmark me-1"></i>เปิดจอง</span>';
                    }
                    metaHtml += '</div>';

                    return '<div class="prd-cell-wrap">' +
                        thumbHtml +
                        '<div class="prd-info">' +
                        '<div class="prd-name">' + safeName + '</div>' +
                        metaHtml +
                        '</div>' +
                        '</div>';
                }
            },
            {
                data: "category_name",
                render: function (val, type) {
                    var label = val || "ไม่ระบุประเภท";
                    if (type === "sort" || type === "filter") return label;
                    return '<span class="prd-cat-pill">' + escapeHtml(label) + '</span>';
                }
            },
            {
                data: "min_price",
                className: "text-end",
                render: function (val, type, row) {
                    var minP = Number(row.min_price) || 0;
                    var maxP = Number(row.max_price) || 0;
                    if (type === "sort") return minP;
                    if (minP <= 0 && maxP <= 0) {
                        return '<span class="text-muted small">ยังไม่กำหนด</span>';
                    }
                    if (Math.abs(maxP - minP) < 0.01) {
                        return '<span class="prd-price">' + formatPrice(minP) + '</span>';
                    }
                    return '<span class="prd-price">' + formatPrice(minP) + ' - ' + formatPrice(maxP) + '</span>';
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

                    var remLabel = rem <= 0 ? "หมด (" + formatInt(rem) + ")" : formatInt(rem) + " ชิ้น";
                    return '<div>' +
                        '<span class="prd-stock-pill ' + cls + '">' + remLabel + '</span>' +
                        '<div class="prd-stock-sub">ขาย ' + formatInt(ordered) + ' / สต็อก ' + formatInt(total) + '</div>' +
                        '</div>';
                }
            },
            {
                data: "product_status",
                className: "text-center",
                render: function (val, type, row) {
                    var isActive = parseInt(val, 10) === 2;
                    if (type === "sort" || type === "filter") {
                        return isActive ? "วางจำหน่าย" : "รอวางจำหน่าย";
                    }
                    var stateClass = isActive ? "is-active" : "is-pending";
                    var labelText = isActive ? "วางจำหน่าย" : "รอวางจำหน่าย";
                    var checkedAttr = isActive ? " checked" : "";

                    return '<label class="prd-status-toggle ' + stateClass + '" title="คลิกเพื่อสลับสถานะการวางจำหน่าย">' +
                        '<input type="checkbox" class="prd-switch-input product_status" data-product-id="' + escapeHtml(row.product_id || "") + '"' + checkedAttr + '>' +
                        '<span class="prd-switch-track"><span class="prd-switch-thumb"></span></span>' +
                        '<span class="prd-status-label">' + labelText + '</span>' +
                        '</label>';
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    var pid = encodeURIComponent(row.product_id || "");
                    var imgCount = parseInt(row.img_count, 10) || 0;
                    var orderRef = parseInt(row.order_ref_count, 10) || 0;
                    var delClass = orderRef > 0 ? "btn-outline-secondary prd-btn-locked" : "btn-outline-danger";
                    var delTitle = orderRef > 0
                        ? "ไม่สามารถลบได้ (มีคำสั่งซื้อ " + orderRef + " รายการอ้างอิง)"
                        : "ลบสินค้า";

                    return '<div class="prd-actions-wrap">' +
                        '<a href="?page=product-stock&product_id=' + pid + '" class="btn btn-outline-primary btn-sm" title="จัดการสต็อกและตัวเลือกสินค้า">' +
                        '<i class="fas fa-layer-group"></i></a>' +
                        '<a href="?page=product-image&product_id=' + pid + '" class="btn btn-outline-success btn-sm" title="จัดการรูปภาพ (' + imgCount + ' รูป)">' +
                        '<i class="fas fa-images"></i></a>' +
                        '<button type="button" class="btn btn-outline-warning btn-sm edit_product" title="แก้ไขข้อมูลสินค้า">' +
                        '<i class="fas fa-edit"></i></button>' +
                        '<button type="button" class="btn ' + delClass + ' btn-sm delete_product" title="' + escapeHtml(delTitle) + '">' +
                        '<i class="fas fa-trash"></i></button>' +
                        '</div>';
                }
            }
        ]
    });

    function setStatusFilter(filterVal) {
        currentStatusFilter = filterVal || "all";
        $(".prd-stat-card").removeClass("is-active");
        $('.prd-stat-card[data-filter="' + currentStatusFilter + '"]').addClass("is-active");
        $("#prd-status-tabs .nav-link").removeClass("active");
        $('#prd-status-tabs .nav-link[data-filter="' + currentStatusFilter + '"]').addClass("active");
        dataTable.draw();
    }

    $(".prd-stat-card").on("click", function () {
        setStatusFilter($(this).data("filter"));
    });

    $("#prd-status-tabs").on("click", ".nav-link", function () {
        setStatusFilter($(this).data("filter"));
    });

    $("#filter-category").on("change", function () {
        currentCategoryFilter = $(this).val() || "";
        dataTable.draw();
    });

    // TinyMCE init + Bootstrap 5 modal focus fix
    tinymce.init({
        selector: "textarea#product_detail",
        height: 280,
        promotion: false,
        statusbar: false
    });

    $(document).on("focusin", function (e) {
        if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
            e.stopImmediatePropagation();
        }
    });

    // Thumbnail preview lightbox
    $("#add-row tbody").on("click", ".js-preview-img", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data || !data.img_name) return;

        $("#img-preview-title").text(data.product_name || "รูปภาพสินค้า");
        $("#img-preview-src")
            .attr("src", "../file/product/" + encodeURIComponent(data.img_name))
            .attr("alt", data.product_name || "");
        $("#img-preview-manage").attr("href", "?page=product-image&product_id=" + encodeURIComponent(data.product_id));
        $("#productImgModal").modal("show");
    });

    // Open Edit modal
    $("#add-row tbody").on("click", ".edit_product", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;

        var detailHtml = data.product_detail || "";
        $("#fn").val("edit_product");
        $("#product_id").val(data.product_id || "");
        $("#modal-product-code").text("รหัสสินค้า #" + (data.product_id || ""));
        $("#product_name").val(data.product_name || "");
        $("#category_id").val(data.category_id || "0");

        var editor = tinymce.get("product_detail");
        if (editor) {
            editor.setContent(detailHtml);
        } else {
            $("#product_detail").val(detailHtml);
        }
        $('[name="product_detail"]').val(detailHtml);

        var reserveVal = data.reserve_id ? String(data.reserve_id) : "2";
        $('[name="reserve_id"][value="' + reserveVal + '"]').prop("checked", true);

        $("#productModal").modal("show");
    });

    $("#edit-product").on("click", function (e) {
        e.preventDefault();
        var editor = tinymce.get("product_detail");
        var product_detail = editor ? editor.getContent() : $("#product_detail").val();
        $('[name="product_detail"]').val(product_detail);
        $("#form-product").trigger("submit");
    });

    $("#form-product").on("submit", function (e) {
        e.preventDefault();
        var name = $.trim($("#product_name").val());
        var catId = $("#category_id").val();

        if (!name) {
            Swal.fire("กรุณากรอกข้อมูล", "กรุณากรอกชื่อสินค้า", "warning");
            $("#product_name").focus();
            return;
        }
        if (!catId || catId === "0") {
            Swal.fire("กรุณาเลือกประเภทสินค้า", "กรุณาเลือกประเภทสินค้าก่อนบันทึก", "warning");
            $("#category_id").focus();
            return;
        }

        var $btn = $("#edit-product");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i>กำลังบันทึก...');

        var formData = new FormData(this);
        $.ajax({
            url: "api/products.php",
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
                        $("#productModal").modal("hide");
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

    // Quick status toggle switch
    $("#add-row tbody").on("change", ".product_status", function () {
        var $checkbox = $(this);
        var $wrap = $checkbox.closest(".prd-status-toggle");
        var data = getRowData($checkbox);
        var product_id = $checkbox.attr("data-product-id") || (data ? data.product_id : "");
        var isActive = $checkbox.is(":checked");
        var status_id = isActive ? 2 : 1;

        $wrap.addClass("is-loading")
            .toggleClass("is-active", isActive)
            .toggleClass("is-pending", !isActive);
        $wrap.find(".prd-status-label").text(isActive ? "วางจำหน่าย" : "รอวางจำหน่าย");

        $.ajax({
            type: "POST",
            url: "api/products.php",
            data: {
                fn: "change_status",
                product_id: product_id,
                status_id: status_id
            },
            dataType: "json",
            success: function (res) {
                if (res && res.status === "success") {
                    Toast.fire({
                        icon: "success",
                        title: isActive ? 'เปลี่ยนเป็น "วางจำหน่าย" แล้ว' : 'เปลี่ยนเป็น "รอวางจำหน่าย" แล้ว'
                    });
                    dataTable.ajax.reload(null, false);
                } else {
                    $checkbox.prop("checked", !isActive);
                    $wrap.toggleClass("is-active", !isActive).toggleClass("is-pending", isActive);
                    $wrap.find(".prd-status-label").text(!isActive ? "วางจำหน่าย" : "รอวางจำหน่าย");
                    Swal.fire((res && res.title) || "ไม่สำเร็จ", (res && res.message) || "", "error");
                }
            },
            error: function () {
                $checkbox.prop("checked", !isActive);
                $wrap.toggleClass("is-active", !isActive).toggleClass("is-pending", isActive);
                $wrap.find(".prd-status-label").text(!isActive ? "วางจำหน่าย" : "รอวางจำหน่าย");
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $wrap.removeClass("is-loading");
            }
        });
    });

    // Delete product
    $("#add-row tbody").on("click", ".delete_product", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;

        var orderRef = parseInt(data.order_ref_count, 10) || 0;
        if (orderRef > 0) {
            Swal.fire({
                title: "ไม่สามารถลบสินค้าได้",
                html: 'สินค้า <strong>' + escapeHtml(data.product_name) + '</strong> มีคำสั่งซื้ออ้างอิงอยู่ <strong>' + formatInt(orderRef) + '</strong> รายการ<br><small class="text-muted">แนะนำให้ปิดสวิตช์สถานะเป็น "รอวางจำหน่าย" แทนการลบ</small>',
                icon: "warning",
                confirmButtonText: "เข้าใจแล้ว"
            });
            return;
        }

        Swal.fire({
            title: "ยืนยันการลบสินค้า?",
            html: 'ต้องการลบสินค้า <strong>' + escapeHtml(data.product_name) + '</strong> พร้อมตัวเลือกและรูปภาพทั้งหมดใช่หรือไม่?<br><small class="text-danger">หากลบแล้วจะไม่สามารถกู้คืนได้</small>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            confirmButtonText: "ใช่ ลบเลย",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: "POST",
                url: "api/products.php",
                dataType: "json",
                data: {
                    fn: "delete_product",
                    product_id: data.product_id
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
