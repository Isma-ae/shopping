$(document).ready(function () {
    var slidesCache = {};

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function stripHtml(html) {
        if (!html) return "";
        return $("<div>").html(html).text().replace(/\s+/g, " ").trim();
    }

    function setEditorContent(val) {
        var content = val || "";
        var editor = tinymce.get("slide_detail");
        if (editor) {
            editor.setContent(content);
        } else {
            $("#slide_detail").val(content);
        }
    }

    function getEditorContent() {
        var editor = tinymce.get("slide_detail");
        return editor ? editor.getContent() : ($("#slide_detail").val() || "");
    }

    function setPreviewImage(src) {
        var $img = $("#img");
        var $placeholder = $("#bannerPlaceholder");
        if (src) {
            $img.off("error").on("error", function () {
                $img.addClass("d-none").attr("src", "");
                $placeholder.removeClass("d-none");
            }).attr("src", src).removeClass("d-none");
            $placeholder.addClass("d-none");
        } else {
            $img.addClass("d-none").attr("src", "");
            $placeholder.removeClass("d-none");
        }
    }

    tinymce.init({
        selector: 'textarea#slide_detail',
        height: 240,
        promotion: false,
        statusbar: false
    });

    function select_slide() {
        $.ajax({
            type: "post",
            url: "api/slide.php",
            data: { fn: "load_slide" },
            dataType: "json",
            success: function (res) {
                var rows = (res && Array.isArray(res.data)) ? res.data : [];
                slidesCache = {};
                $("#bannerCountBadge").text(rows.length + " รายการ");

                if (rows.length === 0) {
                    var emptyHtml =
                        '<div class="col-12">' +
                            '<div class="bn-empty-state">' +
                                '<div class="bn-empty-icon"><i class="fas fa-images"></i></div>' +
                                '<h5 class="fw-bold mb-1">ยังไม่มีแบนเนอร์ในระบบ</h5>' +
                                '<p class="text-muted mb-3">เพิ่มภาพแบนเนอร์หน้าแรกเพื่อแสดงโปรโมชันหรือสินค้าแนะนำ</p>' +
                                '<button type="button" class="btn btn-primary btn-round js-open-add">' +
                                    '<i class="fa fa-plus me-1"></i>เพิ่มแบนเนอร์แรก' +
                                '</button>' +
                            '</div>' +
                        '</div>';
                    $(".slide-data").html(emptyHtml);
                    return;
                }

                var html = "";
                $.each(rows, function (idx, row) {
                    slidesCache[row.slide_id] = row;
                    var imgSrc = row.slide_img
                        ? "../file/banner/" + encodeURIComponent(row.slide_img) + "?t=" + Date.now()
                        : "../images/icons/OARShopping.png";
                    var title = $.trim(row.slide_name || "");
                    var plainDetail = stripHtml(row.slide_detail || "");
                    var link = $.trim(row.slide_link || "");

                    var destLabel = "";
                    if (link !== "") {
                        destLabel = '<span class="bn-link-chip" title="' + escapeHtml(link) + '"><i class="fas fa-link"></i><span>' + escapeHtml(link) + '</span></span>';
                    } else if (plainDetail !== "") {
                        destLabel = '<span class="bn-link-chip"><i class="fas fa-file-alt"></i><span>เปิดหน้ารายละเอียดแบนเนอร์</span></span>';
                    } else {
                        destLabel = '<span class="bn-link-chip"><i class="fas fa-arrow-down"></i><span>เลื่อนไปยังรายการสินค้า</span></span>';
                    }

                    html += '<div class="col-md-6 col-xl-4 data-slide" data-slide-id="' + escapeHtml(row.slide_id) + '">';
                    html += '  <article class="bn-card">';
                    html += '    <div class="bn-thumb-wrap js-zoom-banner" data-img="' + imgSrc + '" data-title="' + escapeHtml(title || "แบนเนอร์ #" + (idx + 1)) + '">';
                    html += '      <span class="bn-order-pill">แบนเนอร์ #' + (idx + 1) + '</span>';
                    html += '      <img src="' + imgSrc + '" alt="' + escapeHtml(title || "Banner") + '" onerror="this.onerror=null;this.src=\'../images/icons/OARShopping.png\';">';
                    html += '      <span class="bn-zoom-pill"><i class="fas fa-search-plus me-1"></i>ดูภาพเต็ม</span>';
                    html += '    </div>';
                    html += '    <div class="bn-card-body">';
                    html += '      <h5 class="bn-card-title' + (title === "" ? ' is-empty' : '') + '">' + escapeHtml(title !== "" ? title : "(ไม่ได้ระบุหัวข้อ)") + '</h5>';
                    if (plainDetail !== "") {
                        html += '      <p class="bn-card-desc">' + escapeHtml(plainDetail) + '</p>';
                    }
                    html +=        destLabel;
                    html += '    </div>';
                    html += '    <div class="bn-card-footer">';
                    html += '      <span class="bn-slide-id">' + escapeHtml(row.slide_id) + '</span>';
                    html += '      <div class="bn-card-actions">';
                    html += '        <button type="button" class="btn btn-sm btn-warning edit-slide" data-slide-id="' + escapeHtml(row.slide_id) + '"><i class="fas fa-edit me-1"></i>แก้ไข</button>';
                    html += '        <button type="button" class="btn btn-sm btn-outline-danger del-slide" data-slide-id="' + escapeHtml(row.slide_id) + '" title="ลบแบนเนอร์"><i class="fas fa-trash-alt"></i></button>';
                    html += '      </div>';
                    html += '    </div>';
                    html += '  </article>';
                    html += '</div>';
                });

                $(".slide-data").html(html);
            },
            error: function () {
                $(".slide-data").html('<div class="col-12 text-center py-5 text-danger">ไม่สามารถโหลดรายการแบนเนอร์ได้</div>');
            }
        });
    }

    function openAddModal() {
        $("#slideModalLabel").text("เพิ่มแบนเนอร์ใหม่");
        $("#fn").val("add_slide");
        $("#slide_id").val("");
        $("#slide_img").val("");
        $("#slide_name").val("");
        $("#slide_link").val("");
        setPreviewImage("");
        setEditorContent("");
        $("#imgReqMark").removeClass("d-none");
        $("#imgEditHint").addClass("d-none");
        $("#add-slide").show();
        $("#edit-slide").hide();
        $("#slideModal").modal("show");
    }

    function openEditModal(data) {
        if (!data) return;
        $("#slideModalLabel").text("แก้ไขแบนเนอร์ (" + data.slide_id + ")");
        $("#fn").val("edit_slide");
        $("#slide_id").val(data.slide_id);
        $("#slide_img").val("");
        $("#slide_name").val(data.slide_name || "");
        $("#slide_link").val(data.slide_link || "");
        setPreviewImage(data.slide_img ? "../file/banner/" + encodeURIComponent(data.slide_img) + "?t=" + Date.now() : "");
        setEditorContent(data.slide_detail || "");
        $("#imgReqMark").addClass("d-none");
        $("#imgEditHint").removeClass("d-none");
        $("#add-slide").hide();
        $("#edit-slide").show();
        $("#slideModal").modal("show");
    }

    // Live image preview when selecting a file
    $("#slide_img").on("change", function () {
        var file = this.files && this.files[0];
        if (!file) {
            if ($("#fn").val() === "edit_slide") {
                var oldData = slidesCache[$("#slide_id").val()];
                setPreviewImage(oldData && oldData.slide_img ? "../file/banner/" + encodeURIComponent(oldData.slide_img) : "");
            } else {
                setPreviewImage("");
            }
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            setPreviewImage(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $("#add").on("click", function (e) {
        e.preventDefault();
        openAddModal();
    });

    $(document).on("click", ".js-open-add", function (e) {
        e.preventDefault();
        openAddModal();
    });

    $(document).on("click", ".edit-slide", function (e) {
        e.preventDefault();
        var id = $(this).attr("data-slide-id");
        openEditModal(slidesCache[id]);
    });

    $(document).on("click", ".js-zoom-banner", function () {
        $("#bannerZoomImg").attr("src", $(this).attr("data-img") || "");
        $("#bannerZoomTitle").text($(this).attr("data-title") || "ตัวอย่างแบนเนอร์");
        var modalEl = document.getElementById("bannerZoomModal");
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    $(".manage-slide").on("click", function (e) {
        e.preventDefault();
        var fn = $("#fn").val();
        var fileInput = document.getElementById("slide_img");

        if (fn === "add_slide" && (!fileInput.files || fileInput.files.length === 0)) {
            Swal.fire("กรุณาเลือกรูปภาพ", "กรุณาเลือกไฟล์ภาพแบนเนอร์ก่อนบันทึก", "warning");
            return;
        }

        $('[name="slide_detail"]').val(getEditorContent());
        $("#form-slide").trigger("submit");
    });

    $("#form-slide").on("submit", function (e) {
        e.preventDefault();
        var $btns = $(".manage-slide");
        var formData = new FormData(this);

        $btns.prop("disabled", true);
        $.ajax({
            url: "api/slide.php",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res && res.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: res.title || "สำเร็จ",
                        text: res.message || ""
                    }).then(function () {
                        $("#slideModal").modal("hide");
                        select_slide();
                    });
                } else {
                    Swal.fire({
                        icon: (res && res.icon) || "error",
                        title: (res && res.title) || "ไม่สำเร็จ",
                        text: (res && res.message) || "ไม่สามารถบันทึกข้อมูลได้"
                    });
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

    $(document).on("click", ".del-slide", function (e) {
        e.preventDefault();
        var slide_id = $(this).attr("data-slide-id");

        Swal.fire({
            title: "ยืนยันการลบแบนเนอร์?",
            text: "รูปภาพแบนเนอร์นี้จะถูกลบออกจากระบบถาวร",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "ใช่, ลบแบนเนอร์",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "api/slide.php",
                method: "POST",
                data: {
                    fn: "delete_slide",
                    slide_id: slide_id
                },
                dataType: "json",
                success: function (res) {
                    if (res && res.status === "success") {
                        Swal.fire(res.title || "สำเร็จ", res.message || "ลบแบนเนอร์เรียบร้อย", "success").then(function () {
                            select_slide();
                        });
                    } else {
                        Swal.fire((res && res.title) || "ไม่สำเร็จ", (res && res.message) || "ลบไม่สำเร็จ", "error");
                    }
                },
                error: function () {
                    Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                }
            });
        });
    });

    select_slide();
});
