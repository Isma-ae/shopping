$(document).ready(function () {
    function refreshCaptcha(clearInput) {
        var $btn = $('#refresh-captcha');
        $btn.addClass('is-spinning');
        $('#captcha-img').attr('src', 'api/captcha.php?t=' + Date.now());
        if (clearInput !== false) {
            $('#captcha-input').val('');
        }
        setTimeout(function () {
            $btn.removeClass('is-spinning');
        }, 480);
    }

    function updateCharCount() {
        var len = ($('#contact_msg').val() || '').length;
        $('#contact-char-count').text(len.toLocaleString('th-TH') + ' / 2,000');
    }

    $('#contact_msg').on('input', updateCharCount);
    updateCharCount();

    $('#refresh-captcha').on('click', function (e) {
        e.preventDefault();
        refreshCaptcha(true);
        $('#captcha-input').trigger('focus');
    });

    $('#captcha-input').on('input', function () {
        var cleaned = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        $(this).val(cleaned);
    });

    $('#contact-form').on('submit', function (e) {
        e.preventDefault();
        $('#send-message').trigger('click');
    });

    $('#send-message').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var email = $.trim($('[name="email"]').val());
        var msg = $.trim($('[name="msg"]').val());
        var captcha = $.trim($('#captcha-input').val());
        var website = $('#contact_website').val();

        if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            swal('อีเมลไม่ถูกต้อง', 'กรุณากรอกที่อยู่อีเมลที่ถูกต้อง', 'warning');
            $('[name="email"]').trigger('focus');
            return;
        }

        if (msg === '') {
            swal('กรุณากรอกข้อความ', 'กรุณาระบุข้อความที่ต้องการติดต่อเรา', 'warning');
            $('[name="msg"]').trigger('focus');
            return;
        }

        if (captcha.length < 5) {
            swal('กรุณากรอกรหัสยืนยัน', 'กรุณากรอกตัวอักษร CAPTCHA ให้ครบ 5 หลัก', 'warning');
            $('#captcha-input').trigger('focus');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>กำลังส่ง...</span>');

        $.ajax({
            type: "post",
            url: "api/message.php",
            data: {
                email: email,
                msg: msg,
                captcha: captcha,
                website: website
            },
            dataType: "json",
            success: function (res) {
                swal(res.title, res.message, res.icon);
                if (res.status === 'success') {
                    $('[name="email"]').val('');
                    $('[name="msg"]').val('');
                    updateCharCount();
                    refreshCaptcha(true);
                } else if (res.refresh_captcha) {
                    refreshCaptcha(true);
                    $('#captcha-input').trigger('focus');
                }
            },
            error: function () {
                swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง', 'error');
                refreshCaptcha(true);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane-o mr-2" aria-hidden="true"></i><span>ส่งข้อความ</span>');
            }
        });
    });
});
