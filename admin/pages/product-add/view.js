$(document).ready(function () {
    var selectedMap = {}; // { [idStr]: rowObj }
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
        return table.row($tr).data();
    }

    function getSelectedList() {
        return Object.keys(selectedMap).map(function (k) {
            return selectedMap[k];
        });
    }

    function updateSelectionUI() {
        var list = getSelectedList();
        var count = list.length;
        var sumStock = 0;
        list.forEach(function (r) {
            sumStock += parseInt(r.number_stock, 10) || 0;
        });

        $("#stat-selected").text(formatInt(count));
        $("#stat-selected-stock").text(formatInt(sumStock) + " ชิ้น");

        if (count > 0) {
            $("#btn-sel-count").text(formatInt(count)).removeClass("d-none");
            $("#btnClearSelection").removeClass("d-none");
        } else {
            $("#btn-sel-count").text("0").addClass("d-none");
            $("#btnClearSelection").addClass("d-none");
        }

        // Sync header select-all checkbox for current page
        var $pageChecks = $("#shirtTable tbody .variant-check");
        var totalOnPage = $pageChecks.length;
        var checkedOnPage = $pageChecks.filter(":checked").length;
        $("#check-all-page").prop("checked", totalOnPage > 0 && checkedOnPage === totalOnPage);
    }

    function populateColorFilter(rows) {
        var colorSet = {};
        (rows || []).forEach(function (r) {
            var c = (r.color_shirt || "").trim();
            if (c !== "") colorSet[c] = true;
        });
        var colors = Object.keys(colorSet).sort();
        var $sel = $("#filter-color");
        var prev = currentColorFilter;
        $sel.empty().append('<option value="">ทุกสี</option>');
        colors.forEach(function (c) {
            $sel.append($("<option></option>").val(c).text(c));
        });
        if (prev && colorSet[prev]) {
            $sel.val(prev);
        } else {
            currentColorFilter = "";
            $sel.val("");
        }
    }

    // Color filter for DataTables
    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
        if (settings.nTable.id !== "shirtTable") return true;
        if (!currentColorFilter) return true;
        var row = table.row(dataIndex).data();
        if (!row) return true;
        return ((row.color_shirt || "").trim() === currentColorFilter);
    });

    tinymce.init({
        selector: "textarea#product_detail",
        height: 260,
        promotion: false,
        statusbar: false
    });

    // Bootstrap 5 modal + TinyMCE dialog focus fix
    $(document).on("focusin", function (e) {
        if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
            e.stopImmediatePropagation();
        }
    });

    var table = $("#shirtTable").DataTable({
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
            infoEmpty: "ไม่มีรายการรอจัดกลุ่ม",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
            zeroRecords: "ไม่พบรายการสินค้าที่ตรงกับเงื่อนไข",
            loadingRecords: "กำลังดึงข้อมูลจากคลังสินค้า...",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        },
        ajax: {
            url: "api/api_proxy.php",
            type: "POST",
            dataSrc: function (json) {
                if (json && json.status === "success" && Array.isArray(json.data)) {
                    $("#api-error-banner").addClass("d-none").removeClass("d-flex");
                    $("#stat-api-total").text(formatInt(json.data.length));
                    populateColorFilter(json.data);

                    // Drop selected items that no longer exist in API list
                    var validIds = {};
                    json.data.forEach(function (r) {
                        validIds[String(r.id)] = r;
                    });
                    Object.keys(selectedMap).forEach(function (k) {
                        if (!validIds[k]) {
                            delete selectedMap[k];
                        } else {
                            selectedMap[k] = validIds[k];
                        }
                    });
                    updateSelectionUI();
                    return json.data;
                }

                var msg = (json && json.message) ? json.message : "ไม่สามารถดึงข้อมูลจากระบบคลังสินค้าได้";
                $("#api-error-text").text(msg);
                $("#api-error-banner").removeClass("d-none").addClass("d-flex");
                $("#stat-api-total").text("0");
                return [];
            },
            error: function () {
                $("#api-error-text").text("เกิดข้อผิดพลาดในการเชื่อมต่อ API คลังสินค้า กรุณาลองใหม่อีกครั้ง");
                $("#api-error-banner").removeClass("d-none").addClass("d-flex");
            }
        },
        columns: [
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    var idStr = String(row.id);
                    var checkedAttr = selectedMap[idStr] ? " checked" : "";
                    return '<input class="form-check-input variant-check" type="checkbox" value="' + escapeHtml(idStr) + '"' + checkedAttr + '>';
                }
            },
            {
                data: "name_product",
                render: function (val, type, row) {
                    var name = (row.name_product || "").trim();
                    var detail = (row.detail || "").trim();
                    var idStr = String(row.id || "");
                    if (type === "sort" || type === "filter") {
                        return name + " " + detail + " " + idStr;
                    }

                    var pic = (row.picture || "").trim();
                    var thumbHtml = "";
                    if (pic) {
                        thumbHtml = '<button type="button" class="pa-thumb-btn js-preview-api-img" title="คลิกเพื่อดูรูปขนาดใหญ่">' +
                            '<img src="' + escapeHtml(pic) + '" alt="' + escapeHtml(name) + '" loading="lazy">' +
                            '</button>';
                    } else {
                        thumbHtml = '<div class="pa-thumb-empty"><i class="fas fa-image"></i></div>';
                    }

                    var detailHtml = detail
                        ? '<div class="pa-detail-sub" title="' + escapeHtml(detail) + '">' + escapeHtml(detail) + '</div>'
                        : "";

                    return '<div class="pa-item-cell">' +
                        thumbHtml +
                        '<div class="min-w-0">' +
                        '<div class="pa-name">' + escapeHtml(name || "-") + '</div>' +
                        detailHtml +
                        '<span class="pa-code-chip">ID: ' + escapeHtml(idStr) + '</span>' +
                        '</div>' +
                        '</div>';
                }
            },
            {
                data: "style_shirt",
                render: function (val, type) {
                    var txt = (val || "").trim();
                    if (type === "sort" || type === "filter") return txt;
                    return txt ? escapeHtml(txt) : '<span class="text-muted">-</span>';
                }
            },
            {
                data: "color_shirt",
                render: function (val, type) {
                    var txt = (val || "").trim();
                    if (type === "sort" || type === "filter") return txt;
                    return txt ? '<span class="pa-pill">' + escapeHtml(txt) + '</span>' : '<span class="text-muted">-</span>';
                }
            },
            {
                data: "size_shirt",
                render: function (val, type) {
                    var txt = (val || "").trim();
                    if (type === "sort" || type === "filter") return txt;
                    return txt ? '<span class="pa-pill">' + escapeHtml(txt) + '</span>' : '<span class="text-muted">-</span>';
                }
            },
            {
                data: "price",
                className: "text-end",
                render: function (val, type) {
                    var num = Number(val) || 0;
                    if (type === "sort") return num;
                    return '<span class="pa-price-cost">' + formatPrice(num) + '</span>';
                }
            },
            {
                data: "price_sale",
                className: "text-end",
                render: function (val, type) {
                    var num = Number(val) || 0;
                    if (type === "sort") return num;
                    return '<span class="pa-price-sale">' + formatPrice(num) + '</span>';
                }
            },
            {
                data: "number_stock",
                className: "text-center",
                render: function (val, type) {
                    var num = parseInt(val, 10) || 0;
                    if (type === "sort") return num;
                    var cls = "is-ok";
                    if (num <= 0) cls = "is-out";
                    else if (num <= LOW_STOCK_THRESHOLD) cls = "is-low";
                    return '<span class="pa-stock-pill ' + cls + '">' + formatInt(num) + '</span>';
                }
            }
        ],
        createdRow: function (rowEl, data) {
            if (data && selectedMap[String(data.id)]) {
                $(rowEl).addClass("is-selected");
            }
        }
    });

    table.on("draw", function () {
        $("#shirtTable tbody .variant-check").each(function () {
            var idStr = String($(this).val());
            var isSel = !!selectedMap[idStr];
            $(this).prop("checked", isSel);
            $(this).closest("tr").toggleClass("is-selected", isSel);
        });
        updateSelectionUI();
    });

    // Individual checkbox toggle
    $("#shirtTable tbody").on("change", ".variant-check", function () {
        var $cb = $(this);
        var idStr = String($cb.val());
        var rowData = getRowData($cb);
        if ($cb.is(":checked") && rowData) {
            selectedMap[idStr] = rowData;
            $cb.closest("tr").addClass("is-selected");
        } else {
            delete selectedMap[idStr];
            $cb.closest("tr").removeClass("is-selected");
        }
        updateSelectionUI();
    });

    // Select/deselect all visible rows on current page
    $("#check-all-page").on("change", function () {
        var checked = $(this).is(":checked");
        $("#shirtTable tbody .variant-check").each(function () {
            var $cb = $(this);
            var idStr = String($cb.val());
            var rowData = getRowData($cb);
            $cb.prop("checked", checked);
            if (checked && rowData) {
                selectedMap[idStr] = rowData;
                $cb.closest("tr").addClass("is-selected");
            } else {
                delete selectedMap[idStr];
                $cb.closest("tr").removeClass("is-selected");
            }
        });
        updateSelectionUI();
    });

    $("#filter-color").on("change", function () {
        currentColorFilter = $(this).val() || "";
        table.draw();
    });

    $("#btnRefreshApi, #btnRetryApi").on("click", function () {
        table.ajax.reload(null, false);
    });

    $("#btnClearSelection").on("click", function () {
        selectedMap = {};
        $("#shirtTable tbody .variant-check").prop("checked", false);
        $("#shirtTable tbody tr").removeClass("is-selected");
        $("#check-all-page").prop("checked", false);
        updateSelectionUI();
    });

    // Lightbox image preview
    $("#shirtTable tbody").on("click", ".js-preview-api-img", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data || !data.picture) return;
        $("#api-img-title").text(data.name_product || "รูปสินค้า");
        $("#api-img-src").attr("src", data.picture).attr("alt", data.name_product || "");
        $("#apiImgModal").modal("show");
    });

    // Main image upload preview (replaces missing imgf_change)
    function resetImagePreview() {
        $("#img_name").val("");
        $("#img-preview").attr("src", "").addClass("d-none");
        $("#img-placeholder").removeClass("d-none");
        $("#btnRemoveImg").addClass("d-none");
    }

    $("#img_name").on("change", function () {
        var file = this.files && this.files[0];
        if (!file) {
            resetImagePreview();
            return;
        }
        if (!file.type || file.type.indexOf("image/") !== 0) {
            Swal.fire("ชนิดไฟล์ไม่รองรับ", "กรุณาเลือกไฟล์รูปภาพเท่านั้น (JPG, PNG, WEBP, GIF)", "warning");
            resetImagePreview();
            return;
        }
        var reader = new FileReader();
        reader.onload = function (ev) {
            $("#img-preview").attr("src", ev.target.result).removeClass("d-none");
            $("#img-placeholder").addClass("d-none");
            $("#btnRemoveImg").removeClass("d-none");
        };
        reader.readAsDataURL(file);
    });

    $("#btnRemoveImg").on("click", function () {
        resetImagePreview();
    });

    // Open Create Product modal
    $("#btnSaveGroup").on("click", function (e) {
        e.preventDefault();
        var selected = getSelectedList();
        if (selected.length === 0) {
            Swal.fire("ยังไม่ได้เลือกสินค้า", "กรุณาติ๊กเลือกรายการสินค้าจากตารางอย่างน้อย 1 รายการก่อนครับ", "warning");
            return;
        }

        // Build summary chips & total stock
        var sumStock = 0;
        var chipsHtml = "";
        selected.forEach(function (r) {
            var st = parseInt(r.number_stock, 10) || 0;
            sumStock += st;
            var parts = [r.color_shirt, r.size_shirt].filter(Boolean).join(" / ") || r.name_product || ("#" + r.id);
            chipsHtml += '<span class="pa-sel-chip">' +
                escapeHtml(parts) +
                ' <strong>(' + formatInt(st) + ')</strong>' +
                '</span>';
        });

        $("#modal-selected-summary").text("เลือกไว้ " + formatInt(selected.length) + " ตัวเลือก");
        $("#modal-selected-stock-note").text("สต็อกรวม " + formatInt(sumStock) + " ชิ้น");
        $("#modal-selected-chips").html(chipsHtml);

        // Reset form & auto-suggest product_name from first selected item
        resetImagePreview();
        var suggestedName = (selected[0].name_product || "").trim();
        $("#product_name").val(suggestedName);
        $("#category_id").val("");
        $('[name="reserve_id"][value="2"]').prop("checked", true);

        var editor = tinymce.get("product_detail");
        if (editor) {
            editor.setContent("");
        } else {
            $("#product_detail").val("");
        }
        $('[name="product_detail"]').val("");

        $("#productModal").modal("show");
    });

    $("#productModal").on("shown.bs.modal", function () {
        $("#product_name").trigger("focus");
    });

    $("#add-product").on("click", function (e) {
        e.preventDefault();
        var editor = tinymce.get("product_detail");
        var detail = editor ? editor.getContent() : $("#product_detail").val();
        $('[name="product_detail"]').val(detail);
        $("#form-product").trigger("submit");
    });

    $("#form-product").on("submit", function (e) {
        e.preventDefault();

        var selected = getSelectedList();
        if (selected.length === 0) {
            Swal.fire("ยังไม่ได้เลือกสินค้า", "กรุณาเลือกรายการตัวเลือกสินค้าอย่างน้อย 1 รายการ", "warning");
            return;
        }

        var productName = $.trim($("#product_name").val());
        var categoryId = $("#category_id").val();

        if (!productName) {
            Swal.fire("กรุณากรอกชื่อสินค้า", "กรุณากรอกชื่อสินค้าก่อนบันทึก", "warning");
            $("#product_name").focus();
            return;
        }

        if (!categoryId) {
            Swal.fire("กรุณาเลือกประเภทสินค้า", "กรุณาเลือกประเภทสินค้าก่อนบันทึก", "warning");
            $("#category_id").focus();
            return;
        }

        var $btn = $("#add-product");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i>กำลังบันทึก...');

        var formData = new FormData(this);
        formData.append("variants", JSON.stringify(selected));

        $.ajax({
            url: "api/save_group.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
                var isOk = res && (res.data === "y" || res.status === "success");
                if (isOk) {
                    $("#productModal").modal("hide");
                    Swal.fire({
                        title: res.title || "เพิ่มสินค้าสำเร็จ",
                        text: res.message || "",
                        icon: "success",
                        showCancelButton: true,
                        confirmButtonText: "ไปจัดการสต็อก/ตัวเลือก",
                        cancelButtonText: "กลับหน้ารายการสินค้า"
                    }).then(function (result) {
                        if (result.isConfirmed && res.product_id) {
                            window.location.href = "?page=product-stock&product_id=" + encodeURIComponent(res.product_id);
                        } else {
                            window.location.href = "?page=products";
                        }
                    });
                } else {
                    Swal.fire(
                        (res && res.title) || "ไม่สำเร็จ",
                        (res && res.message) || "ไม่สามารถบันทึกสินค้าได้",
                        (res && res.icon) || "error"
                    );
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
            },
            complete: function () {
                $btn.prop("disabled", false).html(origHtml);
            }
        });
    });
});
