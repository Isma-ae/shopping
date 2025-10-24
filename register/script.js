$(document).ready(function () {
    $('#register').click(function (e) {
        e.preventDefault();
        var name = $('#name').val();
        var user_id = $('#user_id').val();
        var user_password = $('#user_password').val();
        var confirm_password = $('#confirm_password').val();
        var user_phone = $('#user_phone').val();
        $.ajax({
            type: "post",
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
                if (res.data == 'y') {
                    swal(res.title, res.message, res.icon).then((value) => {
                        window.location.href = "../login/";
                    });
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });
});