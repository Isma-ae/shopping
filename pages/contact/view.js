$(document).ready(function () {
    $('#send-message').click(function (e) {
        e.preventDefault();
        var email = $('[name="email"]').val();
        var msg = $('[name="msg"]').val();
        $.ajax({
            type: "post",
            url: "api/message.php",
            data: {
                email: email,
                msg: msg
            },
            dataType: "json",
            success: function (res) {
                swal(res.title, res.message, res.icon);
                $('[name="email"]').val('');
                $('[name="msg"]').val('');
            }
        });
    });
});