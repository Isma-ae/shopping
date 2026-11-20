$(document).ready(function () {
    if ($('textarea#message_reply').length) {
        tinymce.init({
            selector: 'textarea#message_reply',
            height: 280,
            promotion: false,
            statusbar: false
        });
    }

    $('#reply').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var message_id = $('#message_id').val();
        var email = $('#email').val();
        var editor = tinymce.get("message_reply");
        var message_reply = editor ? editor.getContent() : $('#message_reply').val();

        if (!$.trim(message_reply.replace(/<[^>]*>/g, ''))) {
            Swal.fire("กรุณากรอกข้อความตอบกลับ", "กรุณาระบุข้อความที่ต้องการตอบกลับลูกค้า", "warning");
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>กำลังส่ง...');

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
                Swal.fire(res.title || "แจ้งเตือน", res.message || "", res.icon || "info");
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>ส่งข้อความตอบกลับ');
            }
        });
    });
});
