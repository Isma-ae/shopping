$(document).ready(function () {
    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function getRowData(table, $el) {
        var $tr = $el.closest("tr");
        if ($tr.hasClass("child")) {
            $tr = $tr.prev();
        }
        return table.row($tr).data();
    }

    var dtLang = {
        emptyTable: "ยังไม่มีข้อมูล",
        zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
        search: "ค้นหา:",
        lengthMenu: "แสดง _MENU_ รายการ",
        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
        infoEmpty: "แสดง 0 รายการ"
    };

    // ------------------------------------------------------------------
    // 1. Product Categories
    // ------------------------------------------------------------------
    var dataTable = $("#category-table").DataTable({
        paging: true,
        lengthChange: false,
        pageLength: 10,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: dtLang,
        ajax: {
            url: "api/category.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_category";
            },
            dataSrc: function (json) {
                var rows = (json && Array.isArray(json.data)) ? json.data : [];
                $("#statCategoryCount").text(rows.length);
                return rows;
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
                data: "category_name",
                render: function (data) {
                    return '<span class="fw-semibold text-dark">' + escapeHtml(data) + '</span>';
                }
            },
            {
                data: "product_count",
                className: "text-center",
                render: function (cnt) {
                    var n = parseInt(cnt || 0, 10);
                    return '<span class="cat-usage-pill' + (n === 0 ? ' is-zero' : '') + '">' + n + ' สินค้า</span>';
                }
            },
            {
                data: null,
                className: "text-end",
                bSortable: false,
                render: function (row) {
                    var inUse = parseInt(row.product_count || 0, 10) > 0;
                    var delTitle = inUse ? "มีสินค้ากำลังใช้ประเภทนี้อยู่" : "ลบประเภทสินค้า";
                    return '<div class="cat-actions-wrap">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm edit_category" title="แก้ไข"><i class="fas fa-edit"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm delete_category' + (inUse ? ' cat-btn-locked' : '') + '" title="' + delTitle + '"><i class="fas fa-trash"></i></button>' +
                    '</div>';
                }
            }
        ]
    });

    $("#categoryModal").on("shown.bs.modal", function () {
        $("#category_name").trigger("focus");
    });

    $("#addCategory").on("click", function (e) {
        e.preventDefault();
        $(".category-label").text("เพิ่มประเภทสินค้า");
        $("#fn").val("add_category");
        $("#category_id").val("");
        $("#category_name").val("");
        $("#add-category").show();
        $("#edit-category").hide();
        $("#categoryModal").modal("show");
    });

    $("#category-table tbody").on("click", ".edit_category", function () {
        var data = getRowData(dataTable, $(this));
        if (!data) return;
        $(".category-label").text("แก้ไขประเภทสินค้า");
        $("#fn").val("edit_category");
        $("#category_id").val(data.category_id);
        $("#category_name").val(data.category_name);
        $("#add-category").hide();
        $("#edit-category").show();
        $("#categoryModal").modal("show");
    });

    $("#add-category, #edit-category").on("click", function (e) {
        e.preventDefault();
        $("#form-category").trigger("submit");
    });

    $("#form-category").on("submit", function (e) {
        e.preventDefault();
        var name = $.trim($("#category_name").val());
        if (name === "") {
            Swal.fire("กรุณากรอกข้อมูล", "กรุณาระบุชื่อประเภทสินค้า", "warning");
            $("#category_name").trigger("focus");
            return;
        }

        var $btns = $("#add-category, #edit-category");
        $btns.prop("disabled", true);
        $.ajax({
            type: "POST",
            url: "api/category.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire(res.title, res.message, res.icon).then(function () {
                        dataTable.ajax.reload(null, false);
                        $("#categoryModal").modal("hide");
                    });
                } else {
                    Swal.fire(res.title || "ไม่สำเร็จ", res.message || "", res.icon || "error");
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btns.prop("disabled", false);
            }
        });
    });

    $("#category-table tbody").on("click", ".delete_category", function () {
        var data = getRowData(dataTable, $(this));
        if (!data) return;

        var usedCnt = parseInt(data.product_count || 0, 10);
        if (usedCnt > 0) {
            Swal.fire(
                "ไม่สามารถลบได้",
                "มีสินค้า " + usedCnt + " รายการกำลังใช้ประเภท \"" + data.category_name + "\" อยู่ กรุณาย้ายประเภทสินค้าก่อนลบ",
                "warning"
            );
            return;
        }

        Swal.fire({
            title: "ยืนยันการลบประเภทสินค้า?",
            text: "คุณต้องการลบประเภท \"" + data.category_name + "\" ใช่หรือไม่",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ใช่, ลบออก",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: "POST",
                url: "api/category.php",
                dataType: "json",
                data: {
                    fn: "delete_category",
                    category_id: data.category_id
                },
                success: function (res) {
                    Swal.fire(res.title || "สำเร็จ", res.message || "", res.icon || "success").then(function () {
                        dataTable.ajax.reload(null, false);
                    });
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });

    // ------------------------------------------------------------------
    // 2. Shipping Carriers
    // ------------------------------------------------------------------
    var dataTable2 = $("#shipping-table").DataTable({
        paging: true,
        lengthChange: false,
        pageLength: 10,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: dtLang,
        ajax: {
            url: "api/category.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_shipping";
            },
            dataSrc: function (json) {
                var rows = (json && Array.isArray(json.data)) ? json.data : [];
                $("#statShippingCount").text(rows.length);
                return rows;
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
                data: "transported_name",
                render: function (data) {
                    return '<span class="fw-semibold text-dark">' + escapeHtml(data) + '</span>';
                }
            },
            {
                data: "order_count",
                className: "text-center",
                render: function (cnt) {
                    var n = parseInt(cnt || 0, 10);
                    return '<span class="cat-usage-pill' + (n === 0 ? ' is-zero' : '') + '">' + n + ' รายการ</span>';
                }
            },
            {
                data: null,
                className: "text-end",
                bSortable: false,
                render: function (row) {
                    var inUse = parseInt(row.order_count || 0, 10) > 0;
                    var delTitle = inUse ? "มีคำสั่งซื้ออ้างอิงบริษัทขนส่งนี้อยู่" : "ลบบริษัทขนส่ง";
                    return '<div class="cat-actions-wrap">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm edit_shipping" title="แก้ไข"><i class="fas fa-edit"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm delete_shipping' + (inUse ? ' cat-btn-locked' : '') + '" title="' + delTitle + '"><i class="fas fa-trash"></i></button>' +
                    '</div>';
                }
            }
        ]
    });

    $("#shippingModal").on("shown.bs.modal", function () {
        $("#transported_name").trigger("focus");
    });

    $("#addShipping").on("click", function (e) {
        e.preventDefault();
        $(".shipping-label").text("เพิ่มบริษัทขนส่ง");
        $("#fn_shipping").val("add_shipping");
        $("#transported_id").val("");
        $("#transported_name").val("");
        $("#add-shipping").show();
        $("#edit-shipping").hide();
        $("#shippingModal").modal("show");
    });

    $("#shipping-table tbody").on("click", ".edit_shipping", function () {
        var data = getRowData(dataTable2, $(this));
        if (!data) return;
        $(".shipping-label").text("แก้ไขบริษัทขนส่ง");
        $("#fn_shipping").val("edit_shipping");
        $("#transported_id").val(data.transported_id);
        $("#transported_name").val(data.transported_name);
        $("#add-shipping").hide();
        $("#edit-shipping").show();
        $("#shippingModal").modal("show");
    });

    $("#add-shipping, #edit-shipping").on("click", function (e) {
        e.preventDefault();
        $("#form-shipping").trigger("submit");
    });

    $("#form-shipping").on("submit", function (e) {
        e.preventDefault();
        var name = $.trim($("#transported_name").val());
        if (name === "") {
            Swal.fire("กรุณากรอกข้อมูล", "กรุณาระบุชื่อบริษัทขนส่ง", "warning");
            $("#transported_name").trigger("focus");
            return;
        }

        var $btns = $("#add-shipping, #edit-shipping");
        $btns.prop("disabled", true);
        $.ajax({
            type: "POST",
            url: "api/category.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire(res.title, res.message, res.icon).then(function () {
                        dataTable2.ajax.reload(null, false);
                        $("#shippingModal").modal("hide");
                    });
                } else {
                    Swal.fire(res.title || "ไม่สำเร็จ", res.message || "", res.icon || "error");
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btns.prop("disabled", false);
            }
        });
    });

    $("#shipping-table tbody").on("click", ".delete_shipping", function () {
        var data = getRowData(dataTable2, $(this));
        if (!data) return;

        var usedCnt = parseInt(data.order_count || 0, 10);
        if (usedCnt > 0) {
            Swal.fire(
                "ไม่สามารถลบได้",
                "มีคำสั่งซื้อ " + usedCnt + " รายการอ้างอิงบริษัทขนส่ง \"" + data.transported_name + "\" อยู่ จึงไม่สามารถลบได้",
                "warning"
            );
            return;
        }

        Swal.fire({
            title: "ยืนยันการลบบริษัทขนส่ง?",
            text: "คุณต้องการลบบริษัทขนส่ง \"" + data.transported_name + "\" ใช่หรือไม่",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ใช่, ลบออก",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: "POST",
                url: "api/category.php",
                dataType: "json",
                data: {
                    fn: "delete_shipping",
                    transported_id: data.transported_id
                },
                success: function (res) {
                    Swal.fire(res.title || "สำเร็จ", res.message || "", res.icon || "success").then(function () {
                        dataTable2.ajax.reload(null, false);
                    });
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });
});
