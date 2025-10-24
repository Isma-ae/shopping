$(document).ready(function () {
    $('.my-password').click(function (e) {
        e.preventDefault();
        // หาตัว input ที่อยู่ใน div เดียวกัน
        var $input = $(this).closest('.my-pass-div').find('.my-pass-input');

        // ตรวจสอบว่าตอนนี้เป็น type อะไร
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text'); // แสดงรหัสผ่าน
            $(this).attr('src', '../images/icons/eye.png'); // เปลี่ยนไอคอนเป็น "เปิดตา"
        } else {
            $input.attr('type', 'password'); // ซ่อนรหัสผ่าน
            $(this).attr('src', '../images/icons/eye-crossed.png'); // เปลี่ยนไอคอนเป็น "ปิดตา"
        }
    });

    $('#login').click(function (e) {
        e.preventDefault();
        loginFunction();
    });

    $('#user_name, #user_password').on('keypress', function (e) {
        if (e.which === 13) { // ถ้ากด Enter
            e.preventDefault();
            loginFunction();
        }
    });

    function loginFunction() {
        var user_name = $('#user_name').val();
        var user_password = $('#user_password').val();

        $.ajax({
            type: "post",
            url: "action.php",
            data: {
                fn: "login",
                user_name: user_name,
                user_password: user_password
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'y') {
                    swal(res.title, res.message, res.icon).then(() => {
                        location.reload();
                    });
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    }

});