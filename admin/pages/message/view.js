$(document).ready(function () {
    tinymce.init({
        selector: 'textarea#message_reply',
        height: 300,
        promotion: false,
        statusbar: false
    });

    $('#reply').click(function (e) {
        e.preventDefault();
        var message_id = $('#message_id').val();
        var email = $('#email').val();
        var message_reply = tinymce.get("message_reply").getContent();
        $.ajax({
            type: "post",
            url: "api/message.php",
            data: {
                message_id: message_id,
                email: email,
                message_reply: message_reply
            },
            dataType: "json",
            success: function (res) {
                Swal.fire(res.title, res.message, res.icon);
            }
        });
    });
});