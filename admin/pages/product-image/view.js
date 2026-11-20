$(document).ready(function () {
    if (!$("#product_id").length) return;

    var productId = $("#product_id").val();
    var galleryImages = [];

    var Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1700,
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

    function getRowData($el) {
        var $tr = $el.closest("tr");
        if ($tr.hasClass("child")) {
            $tr = $tr.prev();
        }
        return dataTable.row($tr).data();
    }

    function updateHeroThumb(images) {
        var mainImg = null;
        for (var i = 0; i < images.length; i++) {
            if (parseInt(images[i].img_main, 10) === 1) {
                mainImg = images[i];
                break;
            }
        }
        if (!mainImg && images.length > 0) {
            mainImg = images[0];
        }

        if (mainImg && mainImg.img_name) {
            $("#hero-main-img")
                .attr("src", "../file/product/" + encodeURIComponent(mainImg.img_name))
                .removeClass("d-none");
            $("#hero-main-empty").addClass("d-none");
        } else {
            $("#hero-main-img").attr("src", "").addClass("d-none");
            $("#hero-main-empty").removeClass("d-none");
        }
    }

    function renderGallery(images) {
        galleryImages = Array.isArray(images) ? images : [];
        $("#gallery-count-badge").text(galleryImages.length + " รูป");
        updateHeroThumb(galleryImages);

        if (galleryImages.length === 0) {
            $("#gallery-grid").empty().addClass("d-none");
            $("#gallery-empty").removeClass("d-none");
            $("#modal-color-img-grid").empty();
            $("#modal-no-img-alert").removeClass("d-none");
            return;
        }

        $("#gallery-grid").removeClass("d-none");
        $("#gallery-empty").addClass("d-none");
        $("#modal-no-img-alert").addClass("d-none");

        var gridHtml = "";
        var pickerHtml = '<label class="pi-pick-option">' +
            '<input type="radio" name="img_id" value="0">' +
            '<div class="pi-pick-card pi-pick-card--none">' +
            '<i class="fas fa-ban"></i><span>ไม่ระบุรูป</span>' +
            '</div></label>';

        galleryImages.forEach(function (v) {
            var isMain = parseInt(v.img_main, 10) === 1;
            var usedCount = parseInt(v.used_color_count, 10) || 0;
            var imgUrl = "../file/product/" + encodeURIComponent(v.img_name || "");
            var imgId = parseInt(v.img_id, 10) || 0;

            gridHtml += '<div class="pi-card' + (isMain ? ' is-main' : '') + '">';
            gridHtml += '<div class="pi-card-media js-open-lightbox" data-src="' + imgUrl + '" data-title="' + (isMain ? 'รูปสินค้าหลัก' : 'รูปภาพสินค้า #' + imgId) + '">';
            gridHtml += '<img src="' + imgUrl + '" alt="รูปสินค้า" loading="lazy">';
            if (isMain) {
                gridHtml += '<span class="pi-main-badge"><i class="fas fa-star"></i> รูปหลัก</span>';
            }
            if (usedCount > 0) {
                gridHtml += '<span class="pi-used-badge">ผูกอยู่ ' + usedCount + ' สี</span>';
            }
            gridHtml += '</div>';

            gridHtml += '<div class="pi-card-footer">';
            if (isMain) {
                gridHtml += '<button type="button" class="btn btn-sm btn-primary pi-btn-main" disabled><i class="fas fa-star me-1"></i>รูปหลัก</button>';
            } else {
                gridHtml += '<button type="button" class="btn btn-sm btn-outline-primary pi-btn-main js-set-main" data-img-id="' + imgId + '"><i class="far fa-star me-1"></i>ตั้งเป็นหลัก</button>';
            }
            gridHtml += '<button type="button" class="btn btn-sm btn-outline-danger delete_img" data-img-id="' + imgId + '" data-used="' + usedCount + '" data-main="' + (isMain ? '1' : '0') + '" title="ลบรูปภาพ"><i class="fas fa-trash-alt"></i></button>';
            gridHtml += '</div>';
            gridHtml += '</div>';

            pickerHtml += '<label class="pi-pick-option">';
            pickerHtml += '<input type="radio" name="img_id" value="' + imgId + '">';
            pickerHtml += '<div class="pi-pick-card">';
            pickerHtml += '<img src="' + imgUrl + '" alt="" loading="lazy">';
            pickerHtml += '<span class="pi-pick-check"><i class="fas fa-check"></i></span>';
            pickerHtml += '</div></label>';
        });

        $("#gallery-grid").html(gridHtml);
        $("#modal-color-img-grid").html(pickerHtml);
    }

    function select_image() {
        $.ajax({
            type: "POST",
            url: "api/product-image.php",
            data: {
                fn: "select_image",
                product_id: productId
            },
            dataType: "json",
            success: function (res) {
                renderGallery((res && res.data) ? res.data : []);
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถโหลดคลังรูปภาพได้", "error");
            }
        });
    }

    function uploadFiles(fileList) {
        if (!fileList || fileList.length === 0) return;

        var formData = new FormData();
        formData.append("fn", "upload_image");
        formData.append("product_id", productId);

        var validCount = 0;
        for (var i = 0; i < fileList.length; i++) {
            var f = fileList[i];
            if (f.type && f.type.indexOf("image/") === 0) {
                formData.append("img_name[]", f);
                validCount++;
            }
        }

        if (validCount === 0) {
            Swal.fire("ชนิดไฟล์ไม่รองรับ", "กรุณาเลือกไฟล์รูปภาพ (JPG, PNG, WEBP, GIF) เท่านั้น", "warning");
            $("#img_name").val("");
            return;
        }

        $("#dropzone-idle").addClass("d-none");
        $("#dropzone-loading").removeClass("d-none");

        $.ajax({
            url: "api/product-image.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
                if (res && (res.data === "y" || res.status === "success")) {
                    Toast.fire({
                        icon: "success",
                        title: res.message || "อัปโหลดรูปภาพเรียบร้อยแล้ว"
                    });
                    select_image();
                    dataTable.ajax.reload(null, false);
                } else {
                    Swal.fire(
                        (res && res.title) || "อัปโหลดไม่สำเร็จ",
                        (res && res.message) || "ไม่สามารถอัปโหลดรูปภาพได้",
                        (res && res.icon) || "error"
                    );
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถอัปโหลดรูปภาพได้ กรุณาลองใหม่อีกครั้ง", "error");
            },
            complete: function () {
                $("#img_name").val("");
                $("#dropzone-loading").addClass("d-none");
                $("#dropzone-idle").removeClass("d-none");
            }
        });
    }

    // Trigger file chooser
    $("#btnTriggerUpload, #upload-dropzone").on("click", function (e) {
        if ($(e.target).is("#img_name")) return;
        $("#img_name").trigger("click");
    });

    $("#img_name").on("change", function () {
        uploadFiles(this.files);
    });

    // Drag & Drop handlers
    $("#upload-dropzone")
        .on("dragover dragenter", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass("is-dragover");
        })
        .on("dragleave dragend drop", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass("is-dragover");
        })
        .on("drop", function (e) {
            var dt = e.originalEvent && e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                uploadFiles(dt.files);
            }
        });

    // 1-click set as main image
    $(document).on("click", ".js-set-main", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var imgId = $btn.data("img-id");
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            type: "POST",
            url: "api/product-image.php",
            data: {
                fn: "change_main",
                img_id: imgId,
                product_id: productId
            },
            dataType: "json",
            success: function (res) {
                if (res && (res.status === "success" || res.icon === "success")) {
                    Toast.fire({
                        icon: "success",
                        title: "เปลี่ยนรูปสินค้าหลักเรียบร้อยแล้ว"
                    });
                    select_image();
                } else {
                    Swal.fire((res && res.title) || "ไม่สำเร็จ", (res && res.message) || "", "error");
                    select_image();
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                select_image();
            }
        });
    });

    // Delete image
    $(document).on("click", ".delete_img", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var imgId = $(this).data("img-id");
        var usedColors = parseInt($(this).data("used"), 10) || 0;
        var isMain = String($(this).data("main")) === "1";

        var noteLines = [];
        if (isMain) {
            noteLines.push("รูปนี้เป็น <strong>รูปสินค้าหลัก</strong> หากลบระบบจะตั้งรูปถัดไปเป็นรูปหลักแทนอัตโนมัติ");
        }
        if (usedColors > 0) {
            noteLines.push("รูปนี้กำลังผูกอยู่กับสีสินค้า <strong>" + usedColors + " สี</strong> หากลบระบบจะปลดการผูกออก");
        }
        var extraHtml = noteLines.length > 0
            ? '<div class="small text-muted mt-2">' + noteLines.join("<br>") + '</div>'
            : '<div class="small text-danger mt-1">หากลบแล้วจะไม่สามารถกู้คืนได้</div>';

        Swal.fire({
            title: "ยืนยันการลบรูปภาพ?",
            html: "ต้องการลบรูปสินค้านี้ใช่หรือไม่?" + extraHtml,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            confirmButtonText: "ใช่ ลบเลย",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: "POST",
                url: "api/product-image.php",
                dataType: "json",
                data: {
                    fn: "delete_image",
                    img_id: imgId
                },
                success: function (res) {
                    var isOk = res && (res.status === "success" || res.icon === "success");
                    if (isOk) {
                        Toast.fire({
                            icon: "success",
                            title: res.message || "ลบรูปสินค้าเรียบร้อยแล้ว"
                        });
                        select_image();
                        dataTable.ajax.reload(null, false);
                    } else {
                        Swal.fire((res && res.title) || "ไม่สำเร็จ", (res && res.message) || "", "error");
                    }
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });

    // Lightbox modal
    $(document).on("click", ".js-open-lightbox", function (e) {
        e.preventDefault();
        var src = $(this).data("src");
        var title = $(this).data("title") || "รูปภาพสินค้า";
        if (!src) return;
        $("#pi-lightbox-title").text(title);
        $("#pi-lightbox-src").attr("src", src).attr("alt", title);
        $("#piLightboxModal").modal("show");
    });

    // Color-to-Image DataTable
    var dataTable = $("#productTable").DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        pageLength: 10,
        language: {
            search: "ค้นหาสี:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ สี",
            infoEmpty: "ยังไม่มีตัวเลือกสีของสินค้านี้",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ สี)",
            zeroRecords: "ไม่พบสีที่ค้นหา",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        },
        ajax: {
            url: "api/product-image.php",
            type: "POST",
            data: function (d) {
                d.fn = "select_stock";
                d.product_id = productId;
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
                data: "img_name",
                className: "text-center",
                orderable: false,
                render: function (val, type, row) {
                    var colorLabel = (row.variant_color || "").trim() || "สีมาตรฐาน";
                    if (row.img_name) {
                        var url = "../file/product/" + encodeURIComponent(row.img_name);
                        return '<button type="button" class="pi-color-thumb-btn js-open-lightbox" data-src="' + url + '" data-title="สี ' + escapeHtml(colorLabel) + '" title="คลิกเพื่อดูรูปขนาดใหญ่">' +
                            '<img src="' + url + '" alt="' + escapeHtml(colorLabel) + '" loading="lazy">' +
                            '</button>';
                    }
                    return '<div class="pi-color-thumb-empty" title="ยังไม่ได้ผูกรูปประจำสี"><i class="far fa-image"></i></div>';
                }
            },
            {
                data: "variant_color",
                render: function (val, type, row) {
                    var label = (val || "").trim() || "ไม่ระบุสี";
                    if (type === "sort" || type === "filter") return label;
                    var cnt = parseInt(row.variant_count, 10) || 0;
                    return '<div class="pi-color-name">' + escapeHtml(label) + '</div>' +
                        '<small class="text-muted">' + cnt + ' ตัวเลือก</small>';
                }
            },
            {
                data: "sizes_list",
                render: function (val, type) {
                    var raw = (val || "").trim();
                    if (type === "sort" || type === "filter") return raw;
                    if (!raw) return '<span class="text-muted small">-</span>';
                    return raw.split(",").map(function (s) {
                        return '<span class="pi-size-chip">' + escapeHtml(s.trim()) + '</span>';
                    }).join("");
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    var hasImg = !!row.img_name;
                    var btnCls = hasImg ? "btn-outline-warning" : "btn-outline-primary";
                    var btnText = hasImg ? "เปลี่ยนรูป" : "เลือกรูป";
                    return '<button type="button" class="btn ' + btnCls + ' btn-sm btn-round px-3 edit_variant">' +
                        '<i class="fas fa-images me-1"></i>' + btnText +
                        '</button>';
                }
            }
        ]
    });

    // Open color image binding modal
    $("#productTable tbody").on("click", ".edit_variant", function (e) {
        e.preventDefault();
        var data = getRowData($(this));
        if (!data) return;

        var colorVal = data.variant_color || "";
        var imgIdVal = data.img_id ? String(data.img_id) : "0";

        $("#variant_color").val(colorVal);
        $("#modal-color-subtitle").text("สี: " + (colorVal || "ไม่ระบุสี") + " (จะมีผลกับทุกขนาดในสีนี้)");

        $('input[name="img_id"]').prop("checked", false);
        var $target = $('input[name="img_id"][value="' + imgIdVal + '"]');
        if ($target.length) {
            $target.prop("checked", true);
        } else {
            $('input[name="img_id"][value="0"]').prop("checked", true);
        }

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

        var $btn = $("#edit-variant");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i>กำลังบันทึก...');

        var formData = new FormData(this);
        if ($('input[name="img_id"]:checked').length === 0) {
            formData.append("img_id", 0);
        }

        $.ajax({
            url: "api/product-image.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                var isOk = res && (res.status === "success" || res.icon === "success");
                if (isOk) {
                    $("#variantModal").modal("hide");
                    Toast.fire({
                        icon: "success",
                        title: res.message || "บันทึกรูปประจำสีเรียบร้อยแล้ว"
                    });
                    select_image();
                    dataTable.ajax.reload(null, false);
                } else {
                    Swal.fire((res && res.title) || "ไม่สำเร็จ", (res && res.message) || "", "error");
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

    // Initial gallery load
    select_image();
});
