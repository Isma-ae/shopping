$(document).ready(function () {
    // Toggle show/hide password (supports both password & confirm password fields)
    $(".my-password").on("click", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $input = $btn.closest(".my-pass-div").find(".my-pass-input");
        var $icon = $btn.find("i");

        if ($input.attr("type") === "password") {
            $input.attr("type", "text");
            $icon.removeClass("fa-eye-slash").addClass("fa-eye");
        } else {
            $input.attr("type", "password");
            $icon.removeClass("fa-eye").addClass("fa-eye-slash");
        }
    });

    $("#register-form").on("submit", function (e) {
        e.preventDefault();
        registerFunction();
    });

    function registerFunction() {
        var name = $.trim($("#name").val());
        var user_id = $.trim($("#user_id").val());
        var user_phone = $.trim($("#user_phone").val());
        var user_password = $("#user_password").val();
        var confirm_password = $("#confirm_password").val();

        if (!name) {
            swal("กรุณากรอกชื่อ-นามสกุล", "กรุณากรอกชื่อ-นามสกุลของคุณ", "warning").then(function () {
                $("#name").focus();
            });
            return;
        }

        if (!user_id || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user_id)) {
            swal("รูปแบบอีเมลไม่ถูกต้อง", "กรุณากรอกที่อยู่อีเมลที่ถูกต้อง เช่น name@example.com", "warning").then(function () {
                $("#user_id").focus();
            });
            return;
        }

        if (user_phone !== "" && !/^[0-9\-\s\+]{8,20}$/.test(user_phone)) {
            swal("เบอร์โทรศัพท์ไม่ถูกต้อง", "กรุณากรอกหมายเลขโทรศัพท์ให้ถูกต้อง (ตัวเลข 8-20 หลัก)", "warning").then(function () {
                $("#user_phone").focus();
            });
            return;
        }

        if (!user_password || user_password.length < 8) {
            swal("รหัสผ่านสั้นเกินไป", "กรุณากำหนดรหัสผ่านอย่างน้อย 8 ตัวอักษร", "warning").then(function () {
                $("#user_password").focus();
            });
            return;
        }

        if (user_password !== confirm_password) {
            swal("รหัสผ่านไม่ตรงกัน", "กรุณากรอกรหัสผ่านและยืนยันรหัสผ่านให้ตรงกัน", "warning").then(function () {
                $("#confirm_password").focus();
            });
            return;
        }

        var $btn = $("#register");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> <span>กำลังสมัครสมาชิก...</span>');

        $.ajax({
            type: "POST",
            url: "action.php",
            data: {
                fn: "register",
                name: name,
                user_id: user_id,
                user_password: user_password,
                confirm_password: confirm_password,
                user_phone: user_phone
            },
            dataType: "json",
            success: function (res) {
                if (res && res.data === "y") {
                    swal(
                        res.title || "สมัครสมาชิกสำเร็จ",
                        res.message || "กรุณาเข้าสู่ระบบเพื่อเริ่มใช้งาน",
                        res.icon || "success"
                    ).then(function () {
                        window.location.href = "../login/";
                    });
                } else {
                    swal(
                        (res && res.title) || "สมัครสมาชิกไม่สำเร็จ",
                        (res && res.message) || "กรุณาตรวจสอบข้อมูลอีกครั้ง",
                        (res && res.icon) || "error"
                    );
                }
            },
            error: function () {
                swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
            },
            complete: function () {
                $btn.prop("disabled", false).html(origHtml);
            }
        });
    }
});
