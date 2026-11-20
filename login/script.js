$(document).ready(function () {
    // Toggle show/hide password
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

    // Helpful forgot-password dialog (storefront uses SweetAlert v1)
    $("#forgot-password-link").on("click", function (e) {
        e.preventDefault();
        swal({
            title: "ลืมรหัสผ่าน?",
            text: "หากลงทะเบียนด้วย PSU Passport / Google / LINE สามารถกดปุ่มด้านล่างเพื่อเข้าสู่ระบบได้ทันที\n\nหากเป็นบัญชีสมาชิกทั่วไป กรุณาติดต่อเจ้าหน้าที่สำนักวิทยบริการ (โทร. 073-331-300) หรือส่งข้อความผ่านหน้าติดต่อเรา",
            icon: "info",
            buttons: {
                cancel: "ปิด",
                contact: {
                    text: "ไปหน้าติดต่อเรา",
                    value: "contact"
                }
            }
        }).then(function (val) {
            if (val === "contact") {
                window.location.href = "../?page=contact";
            }
        });
    });

    $("#login-form").on("submit", function (e) {
        e.preventDefault();
        loginFunction();
    });

    function loginFunction() {
        var user_name = $.trim($("#user_name").val());
        var user_password = $("#user_password").val();

        if (!user_name) {
            swal("กรุณากรอกข้อมูล", "กรุณากรอกชื่อบัญชีผู้ใช้หรืออีเมล", "warning").then(function () {
                $("#user_name").focus();
            });
            return;
        }
        if (!user_password) {
            swal("กรุณากรอกข้อมูล", "กรุณากรอกรหัสผ่าน", "warning").then(function () {
                $("#user_password").focus();
            });
            return;
        }

        var $btn = $("#login");
        var origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> <span>กำลังเข้าสู่ระบบ...</span>');

        $.ajax({
            type: "POST",
            url: "action.php",
            data: {
                fn: "login",
                user_name: user_name,
                user_password: user_password
            },
            dataType: "json",
            success: function (res) {
                if (res && res.data === "y") {
                    swal(res.title || "เข้าสู่ระบบสำเร็จ", res.message || "", res.icon || "success").then(function () {
                        window.location.href = "../";
                    });
                } else {
                    swal(
                        (res && res.title) || "เข้าสู่ระบบไม่สำเร็จ",
                        (res && res.message) || "กรุณาตรวจสอบบัญชีผู้ใช้และรหัสผ่าน",
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
